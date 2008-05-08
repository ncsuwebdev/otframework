window.addEvent('domready', function() {
    new mooStrength($('newPassword'));
    
    var span = new Element('span');
    span.setAttribute('class', 'required');
    span.innerHTML ='*';
        
    span.injectAfter($('newPassword'));     
});