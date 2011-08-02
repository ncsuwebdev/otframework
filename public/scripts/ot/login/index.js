$('document').ready(function() {
    
    $("#loginForms").tabs({
        selected: $('#tabSelectedIndex').val()
    });
    
    $('.signup').click(function () {
        var realm = this.id.replace(/[^_]*\_/, '');
        location.href = baseUrl + '/login/signup/realm/' + realm;
    });
});