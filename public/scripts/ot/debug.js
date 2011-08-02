$('document').ready(function() {
    
    var cookieOptions = {path: $('#baseUrl').val()};
    
    $('#debugInfo').hide();
    
    $('#toggleDebugInfoButton').click(function(e) {
        if ($("#debugInfo").is(":hidden")) {  
            $("#debugInfo").slideDown("slow");  
            $('#toggleDebugInfoButton').addClass("active");  
            $.cookie('showDebugInfoState', 'expanded', cookieOptions);    
        } else {  
            $("#debugInfo").slideUp("slow");  
            $('#toggleDebugInfoButton').removeClass("active");  
            $.cookie('showDebugInfoState', 'collapsed', cookieOptions);  
        }
    });
               
    // Gets the cookie and sets the debug info state  
    var showDebugInfoState = $.cookie('showDebugInfoState');
    
    if (showDebugInfoState == 'expanded') {  
        $("#debugInfo").show();
        $("#toggleDebugInfoButton").addClass("active");
    }
});