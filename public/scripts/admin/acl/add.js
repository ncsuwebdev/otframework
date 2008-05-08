window.addEvent('domready', function() {

    /* Grid Tool Tips */
    var tips = new Tips($$('.description'), {

        className: 'descriptionTab',

        initialize:function(){
            this.fx = new Fx.Style(this.toolTip, 'opacity', {duration: 500, wait: false}).set(0);
        },

        onShow: function(toolTip) {
            this.fx.start(1);
        },

        onHide: function(toolTip) {
            this.fx.start(0);
        }
    });
    
    var selectors = $$('.allAccess');
    
    selectors.each(function(el) {

        el.addEvent('change', function(e) {
            var disp = "none";
            if (el.value == 'some') {
                disp = "";
            } else {
                disp = "none";
            }
            
            var rows = $$('.' + el.id);
            
            rows.each(function(el) {
                el.style.display = disp;
            });            
        });
    });
        
    $('aclEditor').addEvent('submit', function(e) {

        selectors.each(function (el) {
            $$('.' + el.id + '_action').each(function(bx) {
                 if (!bx.checked) {   
                    if (el.value == 'some') {             
                        if (bx.value == 'allow') {
                            bx.value = 'deny';
                        } else {
                            bx.value = 'allow';
                        }
                    } else {
                        bx.value = el.value;
                    }
                    
                    bx.checked = true;
                }
                
                bx.style.display = 'none';
                
            });
            
            if (el.value == 'some') {
                el.value = 'deny';
            }
            
            el.style.display = 'none';
            
        });
    });
});