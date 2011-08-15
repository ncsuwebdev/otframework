var baseUrl = '';

$('document').ready(function() {
        
    baseUrl = $('#baseUrl').val();

    $('ul.sf-menu').supersubs({ 
        minWidth:    12,   // minimum width of sub-menus in em units 
        maxWidth:    27,   // maximum width of sub-menus in em units 
        extraWidth:  1     // extra width can ensure lines don't sometimes turn over due to slight rounding differences and font-family 
    }).superfish({
        animation: {height:'show', opacity:'show'},   // slide-down effect without fade-in
        delay: 500,
        speed: 'fast',
        disableHI: true
    });

    /*
     * Superfish causes scren readers to skip over menu items if their <a> does
     * not contain an "href" attribute. To prevent this, we add the "href" to
     * empty links and then disable the the link via javascript instead.
     */
    $('a.no-link')
        .css('cursor', 'default')
        .click(function() {
            return false;
        });

});