import { GreetingCache } from '../worker/chat-greeting-cache.mjs';

async function runTest() {
  console.log('=== Running GreetingCache Unit Tests ===\n');

  const cache = new GreetingCache({ cooldownMs: 1000 });
  const userId = 1;
  const phone = '918581065107';

  // 1. First greeting
  console.log('[Test 1] First greeting "Hii"...');
  const res1 = cache.evaluate(userId, phone, 'Hii');
  console.log('First greeting intercepted:', res1.shouldIntercept);
  if (!res1.shouldIntercept) {
    console.log('✔ Test 1 Passed: First greeting allowed through to LLM.\n');
  } else {
    console.error('❌ Test 1 Failed!');
    process.exit(1);
  }

  // 2. Second greeting within cooldown
  console.log('[Test 2] Second greeting "hii" within 100ms...');
  const res2 = cache.evaluate(userId, phone, 'hii');
  console.log('Second greeting intercepted:', res2.shouldIntercept);
  console.log('Canned Reply:', res2.replyText);
  if (res2.shouldIntercept && res2.replyText) {
    console.log('✔ Test 2 Passed: Second greeting intercepted locally without burning LLM tokens!\n');
  } else {
    console.error('❌ Test 2 Failed!');
    process.exit(1);
  }

  // 3. Substantive query that contains a greeting word
  console.log('[Test 3] Substantive query "Hello, can you tell me the pricing of Assistgroup?"...');
  const res3 = cache.evaluate(userId, phone, 'Hello, can you tell me the pricing of Assistgroup?');
  console.log('Substantive query intercepted:', res3.shouldIntercept);
  if (!res3.shouldIntercept) {
    console.log('✔ Test 3 Passed: Substantive questions are NEVER intercepted!\n');
  } else {
    console.error('❌ Test 3 Failed!');
    process.exit(1);
  }

  console.log('🎉 All GreetingCache tests passed successfully!');
}

runTest().catch(err => {
  console.error(err);
  process.exit(1);
});
