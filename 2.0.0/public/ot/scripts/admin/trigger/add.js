$('document').ready(function() {
    $('#helper').change(function(e) {   	
        location.href = $('#baseUrl').val() + '/admin/trigger/add/?triggerId=' + $('#triggerId').val() + '&helper=' + $('#helper').val();
    });
});