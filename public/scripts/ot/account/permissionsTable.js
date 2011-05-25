$('document').ready(function() {
//	var roles = [1,3];
	
	var roles = new Array();
	$.each($('input:checkbox[name=roleSelect[]]:checked'), function() {
		roles.push($(this).val());
	});
	
	$.getJSON($('#baseUrl').val() + '/ot/account/get-permissions', {'roles' : roles}, function(data) {
		console.log(data);
		createTable(data);
	});
	
});

function createTable(permissions) {
////	var table = $('table').addClass('accessTable');
//	
//	permissions.each(function(i, permission){
////		var table 
//	});
//	
////	table.append();
//	$('div.accessRoles').append(table);
}
