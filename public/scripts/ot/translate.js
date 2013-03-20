var baseUrl = '';

$('document').ready(function() {
        
    baseUrl = $('#baseUrl').val();
    
    $('#overrideTranslationModal').on('shown', function (e) {
        if ($(e.target).hasClass('modal')) {
            var m = $('#overrideTranslation_m').val();
            var c = $('#overrideTranslation_c').val();
            var a = $('#overrideTranslation_a').val();

            $.get(baseUrl + '/ot/translate', {
                'm': m,
                'c': c,
                'a': a
            }, function(data) {
                $('#overrideTranslationContent').html(data);
            });    
        }
    });
    
    $('#overriteTranslationSave').click(function(e) {
        
            var tForm = $('form#translationForm');

            $.post(tForm.attr('action'), tForm.serialize(), 
                function (data) {
                    $("#overrideTranslateModal").modal('hide'); 
                    
                    if (data.rc == 1) {
                        alert(data.msg + "\n\n The page will be refreshed immediately.");
                        window.location.href = window.location.href;
                    } else {
                        alert('ERROR! ' + data.msg);
                    }
                }, "json"
            );
    }); 
});