const HTTP_RESPONSES = {
  400: 'HTTP/1.1 400 Bad Request\r\nConnection: close\r\n\r\n',
  408: 'HTTP/1.1 408 Request Timeout\r\nConnection: close\r\n\r\n',
  413: 'HTTP/1.1 413 Payload Too Large\r\nConnection: close\r\n\r\n',
  431: 'HTTP/1.1 431 Request Header Fields Too Large\r\nConnection: close\r\n\r\n'
};

export function safeHttpParserErrorCode(error) {
  const code = typeof error?.code === 'string' ? error.code : '';
  return /^HPE_[A-Z0-9_]{1,64}$/.test(code) ? code : 'HTTP_PARSE_ERROR';
}

function destroySocket(socket) {
  try {
    if (socket && !socket.destroyed) {
      socket.destroy();
    }
  } catch {
    // The socket is already unusable; never let diagnostics affect the worker.
  }
}

export function closeWithBadRequest(error, socket) {
  if (!socket) {
    return false;
  }

  // Node 20.19.0's own clientError fallback checks this private flag. There is
  // no public socket API exposing whether the attached response wrote headers;
  // keep this version-specific check to avoid corrupting an in-flight reply.
  if (error?.code === 'ECONNRESET' || !socket.writable || socket._httpMessage?._headerSent) {
    destroySocket(socket);
    return false;
  }

  const statusByErrorCode = {
    HPE_HEADER_OVERFLOW: 431,
    HPE_CHUNK_EXTENSIONS_OVERFLOW: 413,
    ERR_HTTP_REQUEST_TIMEOUT: 408
  };
  const status = Object.hasOwn(statusByErrorCode, error?.code) ? statusByErrorCode[error.code] : 400;
  try {
    socket.end(HTTP_RESPONSES[status]);
    return true;
  } catch {
    destroySocket(socket);
    return false;
  }
}

export function attachClientErrorDiagnostics(server, logger) {
  server.on('clientError', (error, socket) => {
    try {
      logger.warn({
        event: 'http_client_error',
        error_code: safeHttpParserErrorCode(error)
      }, 'HTTP request rejected before Express');
    } catch {
      // A logging failure must not prevent closing the rejected connection.
    }
    closeWithBadRequest(error, socket);
  });
  return server;
}
