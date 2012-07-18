$('document').ready(function() {
    $('.tips').tipsy({gravity: 'w', fade: false});

    
    $('#section').change(function(e) {
        $('fieldset').hide();
        $('#fieldset-' + $('#section').val()).show();
    }).change();
    
    $('.mask').iphonePassword();
});