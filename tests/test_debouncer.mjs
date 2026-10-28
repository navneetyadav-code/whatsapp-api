import { MessageDebouncer } from '../worker/chat-debouncer.mjs';

async function runTest() {
  console.log('=== Running MessageDebouncer Unit Tests ===\n');

  const debouncer = new MessageDebouncer({ debounceMs: 400, maxWaitMs: 1500 });
  let flushedCount = 0;
  let flushedPayload = null;

  // Test 1: Rapid texts within debounce window
  console.log('[Test 1] Simulating 3 rapid fragmented texts from same sender...');
  const userId = 1;
  const remoteJid = '919507286092@s.whatsapp.net';

  debouncer.enqueue(userId, remoteJid, { text: 'when will my order come', id: 1 }, (res) => {
    flushedCount++;
    flushedPayload = res;
  });

  setTimeout(() => {
    debouncer.enqueue(userId, remoteJid, { text: '?' }, (res) => {
      flushedCount++;
      flushedPayload = res;
    });
  }, 100);

  setTimeout(() => {
    debouncer.enqueue(userId, remoteJid, { text: 'need it urgent' }, (res) => {
      flushedCount++;
      flushedPayload = res;
    });
  }, 200);

  // Wait for debounce flush (at ~600ms)
  await new Promise(r => setTimeout(r, 700));

  console.log(`Flushed Callback Count: ${flushedCount} (Expected: 1)`);
  console.log(`Item Count: ${flushedPayload?.itemCount} (Expected: 2, because '?' merged with previous)`);
  console.log(`Consolidated Text:\n"${flushedPayload?.consolidatedText}"`);

  if (flushedCount === 1 && flushedPayload?.consolidatedText.includes('when will my order come ?')) {
    console.log('✔ Test 1 Passed: Rapid texts consolidated cleanly!\n');
  } else {
    console.error('❌ Test 1 Failed!');
    process.exit(1);
  }

  // Test 2: Two different users simultaneously
  console.log('[Test 2] Simulating two different users chatting simultaneously...');
  let user1Flush = null;
  let user2Flush = null;

  debouncer.enqueue(1, 'userA@s.whatsapp.net', { text: 'Hello from User A' }, (res) => {
    user1Flush = res;
  });

  debouncer.enqueue(1, 'userB@s.whatsapp.net', { text: 'Hello from User B' }, (res) => {
    user2Flush = res;
  });

  await new Promise(r => setTimeout(r, 600));

  if (user1Flush?.consolidatedText === 'Hello from User A' && user2Flush?.consolidatedText === 'Hello from User B') {
    console.log('✔ Test 2 Passed: Independent users isolated without crosstalk!\n');
  } else {
    console.error('❌ Test 2 Failed!');
    process.exit(1);
  }

  console.log('🎉 All MessageDebouncer tests passed successfully!');
}

runTest().catch(err => {
  console.error(err);
  process.exit(1);
});
