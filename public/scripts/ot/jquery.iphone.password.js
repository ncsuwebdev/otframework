$.fn.iphonePassword = function(options) {
    
    var values = Array();
    this.each(function() {
        
        var $that = $(this);

        var ret = {
            
            isMasked: false,
            pass:null,
            text:null,
        
            maskNow: function() {
                
                if (Modernizr.testProp('textShadow')) {
                    
                    $that.css({
                        'color': 'transparent',
                        'text-shadow': '0 0 7px rgba(0,0,0,0.5)'
                    });
                    
                } else {
                    
                    var textColor = $that.css('color');
                    $that.css({
                        'background-color': textColor
                    });
                }
                
            },
        
            reMask: function() {
                $that.maskNow();
            },
        
            unMask: function() {
                
                if (Modernizr.testProp('textShadow')) {
                    $that.css({
                        'color': '',
                        'text-shadow': ''
                    });               
                    
                } else {
                    
                    $that.css({
                        'background-color': ''
                    });
                }
            }
        }
        
        $that.attr('autocomplete', 'off');
        
        var $link = $('<a style="margin-left: 5px;" class="toggleLink" href="">Mask</a>');
        
        if ($that.val() != '') {
            ret.maskNow();
            ret.isMasked = true;
            $link.text('Unmask');
        }
        
        $that.after($link);
        
        $link.click(function(e) {
            
            e.stopPropagation();
            e.preventDefault();
            
            if (ret.isMasked) {
                ret.unMask();
                $link.text('Mask');
                ret.isMasked = false;
                
            } else {
                
                $link.text('Unmask');
                ret.maskNow();
                ret.isMasked = true;
            }
        });
        
        values.push(ret);        
                
    });
    
    
    values = $(values);
    values.$ = this;
    return values;
};
