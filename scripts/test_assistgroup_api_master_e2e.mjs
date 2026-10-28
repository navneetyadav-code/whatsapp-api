import { spawn } from 'child_process';
import path from 'path';

const scripts = [
  'scripts/test_assistgroup_api_phase1.mjs',
  'scripts/test_assistgroup_api_phase2.mjs',
  'scripts/test_assistgroup_api_phase3.mjs',
  'scripts/test_assistgroup_api_phase4.mjs'
];

function runScript(scriptPath) {
  return new Promise((resolve, reject) => {
    console.log(`\n========================================================`);
    console.log(` RUNNING: ${scriptPath}`);
    console.log(`========================================================`);
    const proc = spawn('node', [scriptPath], { stdio: 'inherit' });
    proc.on('close', code => {
      if (code === 0) resolve();
      else reject(new Error(`Script ${scriptPath} exited with code ${code}`));
    });
  });
}

async function main() {
  console.log('********************************************************');
  console.log(' MASTER REGRESSION RUN: ASSISTGROUP ADMIN API SUITE     ');
  console.log('********************************************************');

  for (const script of scripts) {
    await runScript(script);
  }

  console.log('\n********************************************************');
  console.log(' ALL 4 API PHASES VERIFIED: 100% GREEN PASS             ');
  console.log('********************************************************\n');
}

main().catch(err => {
  console.error('Master regression failed:', err);
  process.exit(1);
});
