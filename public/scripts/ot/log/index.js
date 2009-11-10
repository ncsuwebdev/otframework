$('document').ready(function() {
	
	$("#logResults").flexigrid({
		url: baseUrl + '/ot/log/index/',
		dataType: 'json',
		colModel : [
		    {display: 'AccountId',  name: 'accountId',     width: 75, sortable: true, align:'left'},
		    {display: 'User',       name: 'accountId',     width: 120, sortable: false, align:'left'},
			{display: 'Role',       name: 'role',          width: 100, sortable: true,  align:'left'},
			{display: 'Request',    name: 'request',       width: 100, sortable: true,  align:'left'},
			{display: 'Session',    name: 'sid',           width: 100, sortable: true,  align:'center'},
			{display: 'Timestamp',  name: 'timestamp',     width: 150, sortable: true,  align:'center'},
			{display: 'Message',    name: 'message',       width: 150, sortable: true,  align:'left'},
			{display: 'Priority',   name: 'priorityName',  width: 50,  sortable: true,  align:'center'},
			{display: 'Attr. Name', name: 'attributeName', width: 100, sortable: true,  align:'left'},
			{display: 'Attr. ID',   name: 'attributeId',   width: 50,  sortable: true,  align:'left'}
			],
		searchitems : [
			{display: 'Account ID', name : 'accountId'},
			{display: 'Role', name : 'role'},
			{display: 'Request', name : 'request'},
			{display: 'Session', name : 'session'},
			{display: 'Message', name : 'message'},
			{display: 'Priority', name : 'priorityName'},
			{display: 'Attribute Name', name : 'attributeName'},
			{display: 'Attribute Id', name : 'attributeId'}
			],
		sortname: "timestamp",
		sortorder: "desc",
		singleSelect: true,
		usepager: true,
		title: false,
		useRp: true,
		rp: 40,
		showTableToggleBtn: false
	});
});