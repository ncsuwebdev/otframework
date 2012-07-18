/**
 * jQuery Caret Range plugin is needed to run
 */
String.prototype.repeat = function( num ) {
    return new Array(num + 1).join(this);
}
$.fn.iphonePassword = function(options) {
    var defaults = {
        duration: 3000,
        mask: '\u25CF'
    }
    var values = Array();
    this.each(function() {
        var ret = {
            pass:null,
            text:null,
            focused:false,
            timeout:null,
            opts: null,
        
            maskNow: function(ival) {
                clearTimeout(ret.timeout);
                if(ret.opts.mask != null) {
                    var vl;
                    var ss = ret.text.caret().start;
                    if($.isArray(ival)) {
                        vl = ret.opts.mask.repeat(ival[0])
                            + ret.text.val().substring(ival[0], ival[1])
                            + ret.opts.mask.repeat(ret.text.val().length - ival[1]);
                    } else {
                        vl = ret.opts.mask.repeat(ret.text.val().length);
                    }
                    if(vl!=ret.text.val()) {
                    	  ret.text.removeAttr("lastpos").val(vl);
                    }
                    if(ret.focused) {
                        ret.text.caret(ss, ss);
                    }
                }
            },
        
            reMask: function(ival) {
                if(ret.opts.mask == null) return;
                ret.maskNow(ival);
                if($.isArray(ival)) {
                    ret.timeout = setTimeout(ret.maskNow, ret.opts.duration);
                }
            },
        
            unMask: function() {
                clearTimeout(ret.timeout);
                ret.opts.mask = null;
                ret.text.val(ret.pass.val());
            }
        }
        ret.opts = $.extend(defaults, options);
        ret.pass = $(this);
        var caretMoved = true;
        function sel(ev) {
            if(!caretMoved && (jQuery.browser.safari || jQuery.browser.webkit)) {
                caretMoved = true;
                ret.text.change();
            }
            var el = $(ev.target);
            var range = el.caret();
            if(range.start != range.end) {
                el.attr("lastpos", range.start + "," + range.end)
            } else {
                el.removeAttr("lastpos");
            }
        }
        var ieChange = function(ev) {
            if(event.propertyName=="value") {
                ret.text.unbind("propertychange").change();
            }
        }
        if($.browser.msie) {
            var htm = this.outerHTML.replace("password", "text");
            ret.text = $(htm).val(ret.pass.val()).bind("propertychange", ieChange);
            ret.pass.closest("form").submit(function() {
                ret.text.attr("disabled", "disabled");
            });
        } else ret.text = ret.pass.clone().attr("type", "text");
        var last = null;
        ret.text.attr("autocomplete", "off").removeAttr("name").change(function(evt) {
        	if(last==ret.text.val()) return;
            var t = last = ret.text.val();
            var tr = ret.pass.val();
            var lp = ret.text.attr("lastpos");
            if(lp == null) {
                lp = $(evt.target).caret().end - (t.length - tr.length);
            } else {
                lp = lp.split(",");
                tr = tr.substring(0, parseInt(lp[0])) + tr.substring(parseInt(lp[1]));
                lp = parseInt(lp[0]);
            }
            var added = t.length - tr.length;
            if(added > 0) {
                tr = tr.substring(0,lp) + t.substring(lp, lp + added) + tr.substring(lp);
                ret.reMask([lp, lp + added]);
            } else
                tr = tr.substring(0,lp + added) + tr.substring(lp);
            ret.pass.val(tr);
            ret.text.attr("real", tr).attr("autocomplete", "off").removeAttr("lastpos");
            if($.browser.msie) ret.text.bind("propertychange", ieChange);
        }).keyup(sel).mouseup(sel).select(sel)
        .bind("input", function() {
            if(jQuery.browser.opera || jQuery.browser.mozilla) ret.text.change();
            else caretMoved = false;
         })
        .focus(function() { ret.focused = true; }).blur(function() { ret.focused = false; })
        ret.pass.after(ret.text).hide().removeAttr("id");
        ret.reMask();
        values.push(ret);
    });
    values = $(values);
    values.$ = this;
    return values;
};
