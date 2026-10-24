import test from 'node:test';
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { readFileSync } from 'node:fs';
import { attachClientErrorDiagnostics, closeWithBadRequest, safeHttpParserErrorCode } from '../worker/http-diagnostics.mjs';

const worker = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');

test('parser diagnostics allow only a bounded HTTP parser code, never error text', () => {
  assert.equal(safeHttpParserErrorCode({ code: 'HPE_INVALID_HEADER_TOKEN', message: 'secret header bytes' }), 'HPE_INVALID_HEADER_TOKEN');
  assert.equal(safeHttpParserErrorCode({ code: 'UNEXPECTED', message: 'secret header bytes' }), 'HTTP_PARSE_ERROR');
  assert.equal(safeHttpParserErrorCode({ message: 'secret header bytes' }), 'HTTP_PARSE_ERROR');
});

test('clientError special cases match Node 20.19.0 response bytes', () => {
  const cases = [
    ['HPE_INVALID_METHOD', 'HTTP/1.1 400 Bad Request\r\nConnection: close\r\n\r\n'],
    ['HPE_HEADER_OVERFLOW', 'HTTP/1.1 431 Request Header Fields Too Large\r\nConnection: close\r\n\r\n'],
    ['HPE_CHUNK_EXTENSIONS_OVERFLOW', 'HTTP/1.1 413 Payload Too Large\r\nConnection: close\r\n\r\n'],
    ['ERR_HTTP_REQUEST_TIMEOUT', 'HTTP/1.1 408 Request Timeout\r\nConnection: close\r\n\r\n']
  ];

  for (const [code, expectedResponse] of cases) {
    let output = null;
    closeWithBadRequest({ code }, { writable: true, end: (response) => { output = response; } });
    assert.equal(output, expectedResponse, `unexpected response for ${code}`);
  }
});

test('ECONNRESET, non-writable sockets, and already-sent responses are destroyed without another response', () => {
  for (const { error, socket } of [
    { error: { code: 'ECONNRESET' }, socket: { writable: true } },
    { error: { code: 'HPE_INVALID_METHOD' }, socket: { writable: false } },
    { error: { code: 'HPE_INVALID_METHOD' }, socket: { writable: true, _httpMessage: { _headerSent: true } } }
  ]) {
    let responseCount = 0;
    let destroyCount = 0;
    socket.end = () => { responseCount += 1; };
    socket.destroy = () => { destroyCount += 1; };
    assert.equal(closeWithBadRequest(error, socket), false);
    assert.equal(responseCount, 0);
    assert.equal(destroyCount, 1);
  }
});

test('socket write failures are contained and the socket is destroyed', () => {
  let destroyCount = 0;
  assert.doesNotThrow(() => closeWithBadRequest(
    { code: 'HPE_INVALID_METHOD' },
    { writable: true, end: () => { throw new Error('socket write failure'); }, destroy: () => { destroyCount += 1; } }
  ));
  assert.equal(destroyCount, 1);
});

test('an attached response whose headers are not yet sent can receive the parser response', () => {
  let output = null;
  closeWithBadRequest(
    { code: 'HPE_INVALID_METHOD' },
    { writable: true, _httpMessage: { _headerSent: false }, end: (response) => { output = response; } }
  );
  assert.equal(output, 'HTTP/1.1 400 Bad Request\r\nConnection: close\r\n\r\n');
});

test('Node HTTP server clientError event is logged safely and receives one HTTP 400', () => {
  const logEntries = [];
  const server = createServer((_request, response) => response.end('unexpected request handler'));
  attachClientErrorDiagnostics(server, { warn: (fields) => logEntries.push(fields) });
  let responseCount = 0;
  let destroyCount = 0;
  let rawResponse = null;
  const socket = {
    writable: true,
    destroyed: false,
    end: (response) => { responseCount += 1; rawResponse = response; },
    destroy: () => { destroyCount += 1; }
  };

  server.emit('clientError', { code: 'HPE_INVALID_METHOD', message: 'must never be logged' }, socket);

  assert.equal(rawResponse, 'HTTP/1.1 400 Bad Request\r\nConnection: close\r\n\r\n');
  assert.equal(responseCount, 1);
  assert.equal(destroyCount, 0);
  assert.equal(logEntries.length, 1);
  assert.deepEqual(Object.keys(logEntries[0]).sort(), ['error_code', 'event']);
  assert.equal(logEntries[0].event, 'http_client_error');
  assert.equal(logEntries[0].error_code, 'HPE_INVALID_METHOD');
});

test('worker wires diagnostics to the Express-created server and logs health outcome', () => {
  assert.match(worker, /event: 'health_handler_started', method: 'GET', path: '\/health'/);
  assert.match(worker, /event: 'health_handler_finished', status: res\.statusCode, outcome/);
  assert.match(worker, /const server = app\.listen\(PORT, '127\.0\.0\.1'/);
  assert.match(worker, /attachClientErrorDiagnostics\(server, logger\)/);
});

/* Retain a small guard that rejected requests are never logged with their raw
 * bytes or error messages. The helper's real-server test above checks runtime
 * behavior; this asserts the production wiring selects only the safe code. */
test('worker parser diagnostic wiring does not reference request bytes or parser messages', () => {
  assert.match(worker, /attachClientErrorDiagnostics\(server, logger\)/);
  assert.doesNotMatch(worker, /rawPacket|socket\.parser\.incoming/);
});
