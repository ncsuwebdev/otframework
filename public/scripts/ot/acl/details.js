$('document').ready(function() {
    $('.description').tipsy({gravity: 'e', fade: true});
    
    $('.allAccess').each(function() {

        $(this).change(function() {
            var disp = "none";
            if ($(this).val() == 'some') {
                disp = "";
            } else {
                disp = "none";
            }
            
            $('.' + $(this).attr('id')).each(function() {
                $(this).css('display', disp);
            });        
        });
    });
});