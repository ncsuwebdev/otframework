window.addEvent('domready', function() {

    var format = 'm/d/Y';
    
    new Calendar({queueBeginDt: format});
    new Calendar({queueEndDt: format});
    new Calendar({sentBeginDt: format});
    new Calendar({sentEndDt: format});
});