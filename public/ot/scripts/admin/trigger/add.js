window.addEvent('domready', function() {
    var helper = $('helper');
        
    helper.addEvent('change', function(e) {
        location.href = $('sitePrefix').value + 
            '/admin/trigger/add/?triggerId=' +
            $('triggerId').value +
            '&helper=' +
            helper.options[helper.options.selectedIndex].value
    });
});