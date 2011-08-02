$('document').ready(function() {
    $("#queueResults").flexigrid({
        url: baseUrl + '/ot/emailqueue/index/',
        dataType: 'json',
        colModel : [
            {display: 'To',         name: 'to',            width: 150, sortable: false, align:'left'},
            {display: 'Subject',    name: 'subject',       width: 150, sortable: false, align:'left'},
            {display: 'Status',     name: 'status',        width: 50,  sortable: true,  align:'left'},
            {display: 'Queue Date', name: 'queueDt',       width: 150, sortable: true,  align:'center'},
            {display: 'Sent Date',  name: 'sentDt',        width: 150, sortable: true,  align:'center'},
            {display: 'Attr. Name', name: 'attributeName', width: 100, sortable: true,  align:'center'},
            {display: 'Attr. ID',   name: 'attributeId',   width: 50,  sortable: true,  align:'center'}
            ],
        searchitems : [
            {display: 'Status', name : 'status'},
            {display: 'Attribute Name', name : 'attributeName'},
            {display: 'Attribute Id', name : 'attributeId'},
            ],
        sortname: "queueDt",
        sortorder: "desc",
        singleSelect: true,
        usepager: true,
        title: false,
        useRp: true,
        rp: 40,
        showTableToggleBtn: false
    });
});