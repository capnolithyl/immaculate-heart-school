#!/usr/bin/env node

import { execFileSync, execSync } from 'node:child_process';
import { createInterface } from 'node:readline/promises';
import { stdin as input, stdout as output } from 'node:process';

function run(command) {
    console.log(`\n> ${command}`);
    execSync(command, { stdio: 'inherit', shell: true });
}

function outputOf(command) {
    return execSync(command, {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'pipe'],
        shell: true,
    }).trim();
}

function fail(message) {
    console.error(`\nShip aborted: ${message}`);
    process.exit(1);
}

function commit(message) {
    console.log(`\n> git commit -m "${message}"`);
    execFileSync('git', ['commit', '-m', message], { stdio: 'inherit' });
}

async function main() {
    const branch = outputOf('git branch --show-current');
    if (branch !== 'main') {
        fail(`current branch is "${branch || '(detached HEAD)'}"; switch to main first`);
    }

    const status = outputOf('git status --porcelain');
    let commitMessage = '';

    if (status) {
        console.log('Changes to ship:');
        console.log(status);

        const readline = createInterface({ input, output });
        try {
            commitMessage = (await readline.question('\nCommit message: ')).trim();
        } finally {
            readline.close();
        }

        if (!commitMessage) fail('commit message cannot be empty');
    } else {
        console.log('Working tree is clean; shipping existing commit(s) from main.');
    }

    try {
        run('npm run bundle');

        if (status) {
            run('git add -A');
            commit(commitMessage);
        }

        run('git push origin main');
        run('npm run deploy:upload');
    } catch {
        fail('a build, commit, push, or deployment command failed; see the output above');
    }

    console.log('\nShip complete.');
}

main();
