var baseUrl = '';

$('document').ready(function() {

    baseUrl = $('#baseUrl').val();

    // adds go back functionality to all items with id "cancel"
    if ($('#cancel')) {
        $('#cancel').click(function(e) {
            history.go(-1);
        });
    }

    // adds *'s to required fields and moves it to the top right corner of
    // textareas and multi-select boxes
    $('label.required').each(function() {
        $(this).append('<span style="padding-left: 5px;" class="required">*</span>');
    });
    

    $('#language_select').change(function(e) {

        if ($(this).val() != 0) {
            var today = new Date();
            today.setTime(today.getTime());

            var expires = 14 * 1000 * 60 * 60 * 24;

            var expiresDt = new Date( today.getTime() + (expires) );

            document.cookie = "language_select=" + $(this).val() +
                ";expires=" + expiresDt.toGMTString() +
                ";path=" + baseUrl;

            window.location.reload(true);
        }
    });

});