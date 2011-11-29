$('document').ready(function() {
    
    var currentVal = $('#helper').val();
    
    $('#helper').change(function(e) {       
        if (confirm('Changing the trigger action will reset your form.  Are you sure you want to continue?')) {
            location.href = $('#baseUrl').val() + '/ot/trigger/add/?name=' + $('#triggerId').val() + '&helper=' + $('#helper').val();
        } else {
            $('#helper').val(currentVal);
        }
    });
});