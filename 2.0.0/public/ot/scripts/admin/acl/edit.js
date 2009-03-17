$('document').ready(function() {

    $('.description').tipsy({gravity: 'e', fade: true});
    
    $('#prePopulateButton').click(function() {
    	
    	if ($('#roleName').val() == "") {
    		alert('You must provide a role name');
    		return false;
    	}
    	
    	if (confirm('You will lose any changes you have made.')) {
    		location.href = $('#baseUrl').val() + '/admin/acl/edit' 
    					  + '/?roleId=' + $('#roleId').val() + '&inheritRoleId=' + $('#inheritRoleId').val();
    	} else {
    		return false;
    	}
    });
      
    $('.allAccess').each(function() {

        $(this).change(function() {
            var disp = "none";
            if ($(this).val() == 'some') {
                disp = "";
            } else {
                disp = "none";
            }
            
            $('.' + $(this).attr('id')).each(function() {
                $(this).css('display', disp);
            });        
        });
    });
        
    $('#aclEditor').submit(function() {
    	
    	if ($('#roleName').val() == "") {
			alert('You must provide a role name');
			return false;
		}

        $('.allAccess').each(function () {
        
            var parentVal = $(this).val();
        
            $('.' + $(this).attr('id') + '_action').each(function() {
                 if (!$(this).attr('checked')) {   
                    if (parentVal == 'some') {             
                        if ($(this).val() == 'allow') {
                            $(this).val('deny');
                        } else {
                            $(this).val('allow');
                        }
                    } else {
                        $(this).val(parentVal);
                    }
                    
                    $(this).attr('checked', true);
                }
                $(this).css('display', 'none');
            });
            
            if ($(this).val() == 'some') {
                $(this).val('deny');
            }
            
            $(this).css('display', 'none');
            
        });
    });
});