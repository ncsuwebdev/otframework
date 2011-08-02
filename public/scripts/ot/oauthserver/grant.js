$('document').ready(function() {
    setTimeout(function() {
        location.href=$('#callbackUrl').attr('href');
    }, 5000);
});