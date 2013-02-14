$(document).ready(function() {
    $('#jumpbox').change(function(event) {
        var anchor = $('#jumpbox').val();
        
        if (anchor != '') {
            location.href = '#' + anchor;
        }        
        return false;
    });
});