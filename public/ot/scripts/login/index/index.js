window.addEvent('domready', function() {

    var realm = $('realm');
    
    var span = new Element('span');
    span.id = 'loginDescription';
    span.injectAfter(realm);
    
    realm.addEvent('change', function() {
        var selected = realm.options.selectedIndex;
        
        var sel = $(realm.options[selected].value);
        
        var username = $('username');
        var password = $('password');
        
        if (sel.hasClass('autoLogin')) {
            username.getParent().style.display = 'none';
            username.getParent().getPrevious().style.display = 'none';
            password.getParent().style.display = 'none';
            password.getParent().getPrevious().style.display = 'none';
        } else {
            username.getParent().style.display = '';
            username.getParent().getPrevious().style.display = '';
            password.getParent().style.display = '';
            password.getParent().getPrevious().style.display = '';  
        }       
    
        if (sel.hasClass('signup')) {
            $('signup').style.display = 'inline';
        } else {
            $('signup').style.display = 'none';
        }          
        
        $('loginDescription').setHTML(sel.value);
    });
    
    var a = new Element('a');
    a.id = 'forgotLink';
    a.href = 'javascript:goto("' + $('sitePrefix').value + '/login/index/forgot/")';
    a.innerHTML = 'I Forgot My Password...Help!';
    a.injectInside($('password').getParent());
    
    $('signup').addEvent('click', function(e) {
        goto($('sitePrefix').value + '/login/index/signup/');
    });
    
    realm.fireEvent('change');
});

function goto(link)
{
    if (link.indexOf('?') != -1) {
        link += '&';
    } else {
        link += '?';
    }
    
    link += 'realm=' + $('realm').value;

    location.href=link;
}