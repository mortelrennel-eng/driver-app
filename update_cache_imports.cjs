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
  'Dashboard.tsx'
];

filesToUpdate.forEach(file => {
  const filePath = path.join(pagesDir, file);
  if (!fs.existsSync(filePath)) {
    console.log(`Skipping ${file} - not found`);
    return;
  }
  
  let content = fs.readFileSync(filePath, 'utf8');
  
  // Skip Tracking.tsx as we don't update it, but it's not in the array anyway.
  
  // Add import if not present
  if (!content.includes("import { cachedGet } from '../utils/cachedGet';")) {
    content = content.replace(/import { endpoints } from '\.\.\/config\/api';/, "import { endpoints } from '../config/api';\nimport { cachedGet } from '../utils/cachedGet';");
  }

  // Replace axios.get with cachedGet, but ONLY for endpoints, NOT external urls
  if (file === 'Vehicle.tsx') {
    content = content.replace(/axios\.get\(endpoints\.driverVehicle\)/g, 'cachedGet(endpoints.driverVehicle)');
  } else if (file === 'Dashboard.tsx') {
    content = content.replace(/await axios\.get\(endpoints\.driverPerformance\)/g, 'await cachedGet(endpoints.driverPerformance)');
  } else {
    // General replacement for await axios.get(endpoints.
    content = content.replace(/await axios\.get\(endpoints\./g, 'await cachedGet(endpoints.');
  }

  fs.writeFileSync(filePath, content, 'utf8');
  console.log(`Updated ${file}`);
});
