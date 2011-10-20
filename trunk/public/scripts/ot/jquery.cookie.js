(function($){
    $.cookie = function(key, value, options) {
        if(arguments.length > 1) {
            var o = $.extend({}, $.cookie.defaults, options);
            if (value === null || value === undefined) {
                value = '';
                o.expires = -1;
            }
            if (o.expires.constructor != Date) {
                var today = new Date();
                today.setDate(today.getDate() + o.expires);
                o.expires = today;
            }
            // Create the cookie string
            document.cookie = 
                key + '=' + value +
                '; expires=' + o.expires.toUTCString() +
                (o.path? '; path=' + (o.path) : '') +
                (o.domain? '; domain=' + (o.domain) : '') +
                (o.secure? '; secure' : '');
        } else {
            if(result = new RegExp(key+"=(.*?)(?:;|$)").exec(document.cookie))
                return decodeURIComponent(result[1]);
            return false;
        }
    };
    $.cookie.defaults = {
        expires: 365,
        path: '/'
    }
})(jQuery);