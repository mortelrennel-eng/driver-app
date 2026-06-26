const Client = require('ssh2-sftp-client');
const sftp = new Client();

const config = {
  host: '195.35.62.133',
  port: 65002,
  username: 'u747826271',
  password: '@Admineuro2026',
  readyTimeout: 30000
};

const BASE = '/home/u747826271/domains/eurotaxisystem.site/public_html';

// All files changed in this session:
// 1. Driver suspend/ban/delete + token revocation
// 2. Dashboard has_unit flag
// 3. Unit chassis/motor validation fix
const files = [
  // === BACKEND ===
  // Auth: Suspension days remaining + auto-unsuspend logic
  ['app/Http/Controllers/Api/AuthController.php',             `${BASE}/app/Http/Controllers/Api/AuthController.php`],
  // Driver Management: Token revocation + user hard-delete on ban
  ['app/Http/Controllers/DriverManagementController.php',     `${BASE}/app/Http/Controllers/DriverManagementController.php`],
  // Driver Behavior: Auto-ban token revocation + user deletion
  ['app/Http/Controllers/DriverBehaviorController.php',       `${BASE}/app/Http/Controllers/DriverBehaviorController.php`],
  // Driver App API: has_unit flag in performance response
  ['app/Http/Controllers/Api/DriverAppController.php',        `${BASE}/app/Http/Controllers/Api/DriverAppController.php`],
  // Unit Controller: Relaxed chassis_no & motor_no validation
  ['app/Http/Controllers/UnitController.php',                 `${BASE}/app/Http/Controllers/UnitController.php`],

  // === DRIVER APP (PWA build) ===
  // Auth context: Auto-logout on suspended/banned 403 response
  // Dashboard: No-unit warning cards
  // (These are bundled in the public/driver-app build output)
  ['public/driver-app/index.html',                            `${BASE}/public/driver-app/index.html`],
  ['public/driver-app/assets/Dashboard-Bg1eX1te.js',         `${BASE}/public/driver-app/assets/Dashboard-Bg1eX1te.js`],
  ['public/driver-app/assets/index-Bkm_2nkZ.js',             `${BASE}/public/driver-app/assets/index-Bkm_2nkZ.js`],
  ['public/driver-app/assets/index-ubNyAGG6.css',            `${BASE}/public/driver-app/assets/index-ubNyAGG6.css`],
];

async function upload() {
  try {
    console.log('Connecting to Hostinger SFTP...');
    await sftp.connect(config);
    console.log('✅ Connected!\n');

    for (const [local, remote] of files) {
      try {
        await sftp.fastPut(local, remote);
        console.log(`✅ Uploaded: ${local}`);
      } catch (err) {
        console.error(`❌ FAILED: ${local} → ${err.message}`);
      }
    }

    console.log('\n🎉 Deploy complete!');
  } catch (err) {
    console.error('Connection error:', err.message);
  } finally {
    sftp.end();
  }
}

upload();
