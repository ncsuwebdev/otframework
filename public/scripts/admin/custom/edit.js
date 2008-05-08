function addNewRow(list, row)
{
    var ir = $(row).clone();

    ir.getChildren().each(function (el) {
        if (el.getFirst().getTag().toLowerCase() == 'input') {
            el.getFirst().value = '';
        }
    }
    );

    $(list).adopt(ir);
}

function removeRow(list)
{
    var ic = $(list).getChildren();

    if (ic.length < 2) {
        alert('No new rows left to remove');
    } else {
        ic[ic.length - 1].remove();
    }
}

window.addEvent('domready', function() {

    var type = $('type');
    
	type.addEvent('change', function(e) {

	    if (type.value == 'radio' || type.value == 'select') {
	        $('opt').style.display = 'block';
	    } else {
	        $('opt').style.display = 'none';
	    }
           
	});
});