/**
 * Code protection script for NOARZ
 * Prevents casual users from viewing source code while allowing right-click and copy
 */
document.addEventListener('DOMContentLoaded', function() {
    // Custom message
    const message = "Are you sure you want to see the code? I won't let you see it.";

    // Allow normal right-click functionality
    // Right-click is fully enabled for better user experience including copy/paste

    // Disable F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
    document.addEventListener('keydown', function(e) {
        // F12 key
        if(e.keyCode == 123) {
            e.preventDefault();
            alert(message);
            return false;
        }

        // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
        if(e.ctrlKey &&
           (e.shiftKey && (e.keyCode == 73 || e.keyCode == 74) ||
           e.keyCode == 85)) {
            e.preventDefault();
            alert(message);
            return false;
        }
    });

    // Allow text selection for better user experience
    // Users can select and copy text from the website

    // Gentle protection against dev tools
    function devToolsChecker() {
        const widthThreshold = window.outerWidth - window.innerWidth > 160;
        const heightThreshold = window.outerHeight - window.innerHeight > 160;

        // Only show a warning in console when dev tools are detected
        if (widthThreshold || heightThreshold) {
            console.clear();
            console.log("%c⚠️ Developer Tools Detected", "color:red; font-size:20px; font-weight:bold;");
            console.log("%c" + message, "font-size:16px;");
        }
    }

    // Check periodically but not too frequently
    setInterval(devToolsChecker, 3000);

    // Disable viewing source
    document.onkeypress = function(event) {
        event = (event || window.event);
        if (event.keyCode == 123) {
            alert(message);
            return false;
        }
    }

    // Allow drag and drop for better user experience

    console.log("%c⚠️ Warning!", "color:red; font-size:40px; font-weight:bold;");
    console.log("%cThis is a protected website. Attempting to view or copy the source code is prohibited.", "font-size:16px;");
});
