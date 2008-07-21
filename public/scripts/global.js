window.addEvent('domready', function() {
    
    var sitePrefix = $('sitePrefix').value;
    
    $$('.sub_menu').each(function (el){
        
        el.setStyle('display', 'none');
        
        el.getParent().addEvents({
        
            'mouseover': function(e) {
                    el.setStyle('display', 'block');
                    el.setPosition({
                        relativeTo: el.getPrevious(),
                        position: 'topRight'
                    })
                },
                
            'mouseout': function(e) {
                    el.setStyle('display', 'none');
                }
            }
        );
        
        el.addEvent('mouseout', function(e) {
            el.setStyle('display', 'none');
        });
        
    });
    
    if ($('cancel')) {
        $('cancel').addEvent('click', function(e) {
            history.go(-1);
        });
    }
    
    $$('label.required').each(function (el) {
       
        var span = new Element('span');
        span.addClass('required');
        span.innerHTML ='*';
        
        var target = el.attributes['for'].nodeValue;
        if ($(target)) {
            span.injectAfter($(target));
        }
    });

});