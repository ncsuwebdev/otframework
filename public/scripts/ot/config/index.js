$('document').ready(function() {
    $('.tips').tooltip();

    $('#section').change(function(e) {
        $('fieldset').hide();
        $('#fieldset-' + $('#section').val()).show();
    }).change();
   
    $('.mask').iphonePassword();
        
});