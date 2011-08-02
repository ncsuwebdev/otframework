$('document').ready(function() {
    $('a.themePreview').click(function(e) {
        if (confirm('Are you sure you want to change your theme?')) {
            return true;
        }
        return false;
    });
});