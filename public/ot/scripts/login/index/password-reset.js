window.addEvent('domready', function() {
    new mooStrength($('password'));
    
    var span = new Element('span');
    span.setAttribute('class', 'required');
    span.innerHTML ='*';
        
    span.injectAfter($('password'));     
});