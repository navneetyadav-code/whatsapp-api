import { ChatResolutionEngine } from '../worker/chat-resolution.mjs';

async function runTest() {
  console.log('=== Running ChatResolutionEngine Unit Tests ===\n');

  const priorTurns = [
    { senderType: 'contact', body: 'What are Assistgroup website packages?' },
    { senderType: 'bot', body: 'We offer Starter ($99) and Enterprise ($299).' }
  ];

  // Test 1: Standard gratitude / resolution
  console.log('[Test 1] Testing "Thank you so much, got it!"...');
  const res1 = ChatResolutionEngine.detectResolution('Thank you so much, got it!', priorTurns);
  console.log('Resolved:', res1.isResolved, '| Remark:', res1.remark);
  if (res1.isResolved && res1.remark.includes('Assistgroup website packages')) {
    console.log('✔ Test 1 Passed: Query resolution detected & remark generated.\n');
  } else {
    console.error('❌ Test 1 Failed!');
    process.exit(1);
  }

  // Test 2: Hinglish resolution
  console.log('[Test 2] Testing Hinglish "dhanyawad bhai samajh gaya"...');
  const res2 = ChatResolutionEngine.detectResolution('dhanyawad bhai samajh gaya', priorTurns);
  console.log('Resolved:', res2.isResolved, '| Remark:', res2.remark);
  if (res2.isResolved) {
    console.log('✔ Test 2 Passed: Hinglish resolution recognized.\n');
  } else {
    console.error('❌ Test 2 Failed!');
    process.exit(1);
  }

  // Test 3: Contradiction / unresolved ("thanks but not working")
  console.log('[Test 3] Testing contradictory "Thanks, but it is still not working for me"...');
  const res3 = ChatResolutionEngine.detectResolution('Thanks, but it is still not working for me', priorTurns);
  console.log('Resolved:', res3.isResolved);
  if (!res3.isResolved) {
    console.log('✔ Test 3 Passed: Blocked resolution when problem persists.\n');
  } else {
    console.error('❌ Test 3 Failed!');
    process.exit(1);
  }

  // Test 4: Closure message format
  console.log('[Test 4] Checking closure message formatting...');
  const closureText = ChatResolutionEngine.getClosureMessage();
  console.log('Closure Text:\n' + closureText);
  if (closureText.includes('How would you rate') && closureText.includes('1 to 5')) {
    console.log('✔ Test 4 Passed: Closure message ready for WhatsApp.\n');
  } else {
    console.error('❌ Test 4 Failed!');
    process.exit(1);
  }

  console.log('🎉 All ChatResolutionEngine tests passed successfully!');
}

runTest().catch(err => {
  console.error(err);
  process.exit(1);
});
