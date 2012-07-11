var baseUrl = '';

$('document').ready(function() {
        
    baseUrl = $('#baseUrl').val();
    
    // adds go back functionality to all items with id "cancel"
    if ($('#cancel')) {
        $('#cancel').click(function(e) {
            history.go(-1);
        });
    }
    
    $('input[type=submit], input[type=button], input[type=reset], button').addClass('ui-state-default ui-corner-all');

    // adds hover class to elements with the class state-default
    $('a.ui-state-default, input.ui-state-default, button.ui-state-default').hover(
        function(){ $(this).addClass('ui-state-hover'); }, 
        function(){ $(this).removeClass('ui-state-hover'); }
      );

    
    // adds *'s to required fields and moves it to the top right corner of 
    // textareas and multi-select boxes
    $('label.required').each(function() {        
        $(this).append('<span style="padding-left: 5px;" class="required">*</span>');
    });
    
    $('#systemMessages .alert').prepend('<div class="systemMessageCloseButton"><a class="ui-state-default ui-corner-all linkButtonNoText"><span class="ui-icon ui-icon-closethick"></span></a></div>');
        
    $('.systemMessageCloseButton').click(function() {
        $(this).closest('.alert').slideUp();
    });

    $("#authLogoutDialog").dialog({ 
        modal: true, 
        autoOpen: false,
        resizable: false,
        overlay: { 
            opacity: 0.5, 
            background: "black" 
        }, 
        buttons: {  
            "Cancel": function() { 
                $(this).dialog("close"); 
            },
            "Logout": function() { 
                location.href = baseUrl + '/login/logout/';
            } 
        }         
    }, "close");
    
    $('#authLogout').click(function(e) {
        e.preventDefault();
        $("#authLogoutDialog").dialog("open");        
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