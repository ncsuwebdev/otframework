$(document).ready(function() {
    $('#regenerateKeyBtn').click(function(event) {
        if (!confirm('Are you sure you want to generate a new API key? Your old key will immediately cease to work.')) {
            event.stopPropagation();
            event.preventDefault();
            return false;
        }
    });
});