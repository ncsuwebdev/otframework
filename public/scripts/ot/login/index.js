$('document').ready(function() {
    
    $('.signup').click(function () {
        var realm = this.id.replace(/[^_]*\_/, '');
        location.href = baseUrl + '/login/signup/realm/' + realm;
    });
});