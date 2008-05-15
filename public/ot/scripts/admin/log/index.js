window.addEvent('domready', function() {

    var format = 'm/d/Y';
    
    new Calendar({beginDt: format});
    new Calendar({endDt: format});
});