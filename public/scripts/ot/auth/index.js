$('document').ready(function() {

    $('#adapterList').sortable({
        cursor: 'ns-resize',
        update: function(event, ui) {
            $.post($('#baseUrl').val() + '/ot/auth/save-adapter-order/', {
                    'adapterKeys[]': $('#adapterList').sortable('toArray')
                },
                function (data) {

                    $('#orderMessage').text(data.msg);

                    $('#message').fadeIn();

                    if (data.rc == 1) {
                        $('#orderMessage').removeClass('alert-block').addClass('alert-success');
                    } else {
                        $('#orderMessage').removeClass('alert-success').addClass('alert-block');
                    }

                    setTimeout(function() { $('#message').fadeOut(); }, 2500);
                },
                "json"
            );
        }
    });

    $('#message').hide();
});