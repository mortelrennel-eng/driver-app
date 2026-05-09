const fs = require('fs');
const path = require('path');
const { execSync, spawn } = require('child_process');

console.log('========================================');
console.log('Euro Taxi System - Node.js Deployer');
console.log('========================================\n');

// Configuration
const config = {
    projectPath: __dirname + '/..',
    defaultBranch: 'main',
    defaultCommitMessage: `Auto deployment update - ${new Date().toLocaleString()}`
};

function checkGitRepository() {
    const gitPath = path.join(config.projectPath, '.git');
    if (!fs.existsSync(gitPath)) {
        console.error('ERROR: Not a Git repository. Please run this from the project root.');
        process.exit(1);
    }
    console.log('✓ Git repository found');
}

function checkGitInstalled() {
    try {
        execSync('git --version', { stdio: 'pipe' });
        console.log('✓ Git is installed');
    } catch (error) {
        console.error('ERROR: Git is not installed or not in PATH');
        console.error('Please install Git from: https://git-scm.com/download/win');
        process.exit(1);
    }
}

function executeCommand(command, description) {
    return new Promise((resolve, reject) => {
        console.log(`\n${description}...`);
        
        const child = spawn('git', command.split(' '), {
            cwd: config.projectPath,
            stdio: ['pipe', 'pipe', 'pipe']
        });

        let stdout = '';
        let stderr = '';

        child.stdout.on('data', (data) => {
            stdout += data.toString();
            process.stdout.write(data);
        });

        child.stderr.on('data', (data) => {
            stderr += data.toString();
            process.stderr.write(data);
        });

        child.on('close', (code) => {
            if (code === 0) {
                resolve(stdout);
            } else {
                reject(new Error(`Command failed: ${command}\n${stderr}`));
            }
        });

        child.on('error', (error) => {
            reject(error);
        });
    });
}

async function deploy() {
    try {
        // Check prerequisites
        checkGitRepository();
        checkGitInstalled();

        // Check current status
        await executeCommand('status', 'Checking current Git status');

        // Add all changes
        await executeCommand('add .', 'Adding all changes to staging');

        // Get commit message
        const commitMessage = process.argv.slice(2).join(' ') || config.defaultCommitMessage;
        
        // Commit changes
        await executeCommand(`commit -m "${commitMessage}"`, 'Committing changes');

        // Push to GitHub
        await executeCommand('push origin main', 'Pushing to GitHub');

        console.log('\n========================================');
        console.log('✓ Deployment completed successfully!');
        console.log('========================================');

    } catch (error) {
        console.error('\n========================================');
        console.error('✗ DEPLOYMENT FAILED');
        console.error('========================================');
        console.error(error.message);
        console.error('\nPlease check:');
        console.error('1. Internet connection');
        console.error('2. GitHub credentials');
        console.error('3. Repository permissions');
        process.exit(1);
    }
}

// Run deployment
if (require.main === module) {
    deploy();
}

module.exports = { deploy, config };
