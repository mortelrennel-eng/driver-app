
// Auto Reload System for Euro Taxi
(function() {
    let lastModified = {};
    let reloadDelay = 1000; // 1 second
    
    // Check for file changes
    function checkForChanges() {
        fetch('/check-changes')
            .then(response => response.json())
            .then(data => {
                if (data.changed) {
                    console.log('🔄 Changes detected, reloading...');
                    setTimeout(() => {
                        window.location.reload();
                    }, reloadDelay);
                }
            })
            .catch(error => {
                console.log('Auto-reload check failed:', error);
            });
    }
    
    // Check every 2 seconds
    setInterval(checkForChanges, 2000);
    
    console.log('🔄 Auto-reload system activated');
})();
