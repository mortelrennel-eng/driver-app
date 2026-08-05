<?php
// One-time OPcache reset - DELETE AFTER USE
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!";
} else {
    echo "OPcache not available, but that is OK.";
}
echo "<br><a href='/'>Go to Dashboard</a>";
