window.addEvent('domready', function() {

    var realm = $('realm');
    
    var span = new Element('span');
    span.id = 'loginDescription';
    span.injectAfter(realm);
    
    realm.addEvent('change', function() {
        var selected = realm.options.selectedIndex;
        
        var sel = $(realm.options[selected].value);
        
        $('loginDescription').setHTML(sel.value);
    });
    
    realm.fireEvent('change');
});