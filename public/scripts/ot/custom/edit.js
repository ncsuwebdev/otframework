$('document').ready(function() {
		
	$('#type').change(function(e) {

		if ($(this).val() == 'multiselect' || $(this).val() == 'multicheckbox' || $(this).val() == 'radio' || $(this).val() == 'select') {
	        $('#opt').css('display', 'block');
	        $('#opt').find('input').focus();
	        $('#descriptionInput').css('display', 'none').find('textarea').val('');
	    } else if ($(this).val() == 'description') {
	    	$('#descriptionInput').css('display', 'block');
	    } else {
	        $('#opt').css('display', 'none');
	        $('#descriptionInput').css('display', 'none').find('textarea').val('');
	    }           
	});
	
	$('#addRowButton').click(function() {
		var newRow = $('#optionRow').clone();
		newRow.find('input').each(function() {
			$(this).val('');
		});
		$('#options tbody').append(newRow);
		newRow.find('input').focus();
	});
	
	$('#removeRowButton').click(function() {
		
	    if ($('#options tbody').children().length < 2) {
	        alert('No new rows left to remove');
	    } else {
	    	$('#options tbody tr:last').prev().find('input').focus();
	        $('#options tbody tr:last').remove();
	    }
	});
	
	$('#type').change();
});