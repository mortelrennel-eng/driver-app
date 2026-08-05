const fs = require('fs');
const path = require('path');

const pagesDir = path.join(__dirname, 'driver-app', 'src', 'pages');

const filesToUpdate = [
  'Violations.tsx',
  'Vehicle.tsx',
  'Terms.tsx',
  'Support.tsx',
  'Settings.tsx',
  'Performance.tsx',
  'Notifications.tsx',
  'Incentives.tsx',
  'Earnings.tsx',
  'Announcements.tsx',
  'Accidents.tsx',
  'Dashboard.tsx',
  'Debts.tsx',
  'History.tsx'
];

filesToUpdate.forEach(file => {
  const filePath = path.join(pagesDir, file);
  if (!fs.existsSync(filePath)) return;
  
  let content = fs.readFileSync(filePath, 'utf8');

  // Fix Support.tsx missing import
  if (file === 'Support.tsx' && !content.includes("import { cachedGet } from '../utils/cachedGet';")) {
    content = content.replace(/import { endpoints } from '\.\.\/config\/api';/, "import { endpoints } from '../config/api';\nimport { cachedGet } from '../utils/cachedGet';");
  }
  
  // If the file uses cachedGet, but doesn't use axios anywhere else, remove axios import
  // Dashboard and Support (posting messages), Settings (posting updates) might still use axios.
  // Let's use a regex to see if `axios.` is still used in the file
  if (content.includes("import axios") || content.includes("import axios ")) {
     const hasAxiosUsage = /axios\./.test(content) || /axios\(/.test(content);
     if (!hasAxiosUsage) {
       content = content.replace(/import axios from 'axios';\n?/, "");
       // Also check for `import axios, { AxiosError }` etc, but we mostly just have `import axios from 'axios';`
     }
  }

  fs.writeFileSync(filePath, content, 'utf8');
});

console.log("Imports fixed");
