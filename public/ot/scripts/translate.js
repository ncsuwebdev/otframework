var baseUrl = '';

$('document').ready(function() {
        
	baseUrl = $('#baseUrl').val();
		
	var editLink = $('#overrideTranslate a');
	
	if (editLink.length == 0) {
		return;
	}
		
	var modalSource = $('<div></div>');
	modalSource.attr('id', 'overrideTranslateModal');
	
	var language = editLink.attr('id').replace(/^[^_]*\_/, '');
	
	modalSource.attr('title', 'Edit ' + language + ' Text On This Page');
	modalSource.html('<div id="loading"></div>');
	$(document.body).append(modalSource);
	
	modalSource.dialog({ 
        modal: true, 
        autoOpen: false,
        resizable: false,
        overlay: { 
            opacity: 0.5, 
            background: "black" 
        }, 
        width: 600,
        height: 400,
        buttons: { 
            "Cancel": function() { 
                $(this).dialog("close"); 
            }, 
            "Save": function() {
            	
            	if ($('form#translationForm').length != 0) {
	            	var tForm = $('form#translationForm');
	            	
	            	tForm.css('display', 'none');
	            	$('#loading').css('display', 'block');
	        	
	        		$.post(tForm.attr('action'), tForm.serialize(), 
	        				  function (data) {
	        					$("#overrideTranslateModal").dialog("close"); 
	        				    if (data.rc == 1) {
	        				    	alert(data.msg + "\n\n The page will be refreshed immediately.");
	        				    	window.location.href = window.location.href;
	        				    } else {
	        				    	alert('ERROR! ' + data.msg);
	        				    }
	        				  }, "json");
				} else {
					$(this).dialog("close");
				}
            }
        }     	
    }, "close");
    
	editLink.click(function(e) {
		e.preventDefault();
		$("#overrideTranslateModal").dialog("open"); 
		$('#loading').css('display', 'block');
    	$.ajax({
    		type: "get",
    		url: $('#overrideTranslate a').attr('href'),
    		success: function(data){
    			$('#overrideTranslateModal').html(data + '<div id="loading"></div>');
    	        $('#loading').css('display', 'none');
    			$("#translateKeys").accordion({
    				header: "h3",
    				alwaysOpen: false,
    				active: false
    			});
    		},
    		error: function(msg) {
    			alert(msg);
    		}
    	});
    });
});