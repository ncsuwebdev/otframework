$('document').ready(function() {

    $('#adapterList').sortable({
        cursor: 'ns-resize',
        update: function(event, ui) {
            $.post($('#baseUrl').val() + '/ot/auth/save-adapter-order/', {
                    'adapterKeys[]': $('#adapterList').sortable('toArray')
                },
                function (data) {
                    $('#orderMessage').text(data.msg);
                    if (data.rc == 1) {
                        $('#orderMessage').removeClass('ui-state-error').addClass('ui-state-highlight');
                    } else {
                        $('#orderMessage').removeClass('ui-state-highlight').addClass('ui-state-error');
                    }
                    
                    $('#orderMessage').fadeIn();
                    
                    setTimeout(function() { $('#orderMessage').fadeOut(); }, 2500); 
                },
                "json"
            );        
        }
    });
});