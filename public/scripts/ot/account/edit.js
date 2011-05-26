$('document').ready(function() {
    $('.description').tipsy({gravity: 'e', fade: true});
    $("#access").tabs({
    	selected:0
    });
    
    $.each($('div.actions'), function(i, action){
    	
    });
});

//$('document').ready(function() {
////	var roles = [1,3];
//	
//	var roles = new Array();
//	$.each($('input:checkbox[name=roleSelect[]]:checked'), function() {
//		roles.push($(this).val());
//	});
//	
//	$.getJSON($('#baseUrl').val() + '/ot/account/get-permissions', {'roles' : roles}, function(data) {
//		console.log(data);
//		createTable(data);
//	});
//	
//});