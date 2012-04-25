var initialStructureCache;

$('document').ready(function() {

    $('.description').tipsy({gravity: 'e', fade: true});
    
    $('#prePopulateButton').click(function() {
        
        if ($('#roleName').val() == "") {
            alert('You must provide a role name');
            return false;
        }
        
        if ($('#roleNamePreset').val() != undefined) {
            if (confirm('You will lose any changes you have made.')) {
                location.href = $('#baseUrl').val() + '/ot/acl/add' 
                              + '/?roleName=' + $('#roleName').val() + '&inheritRoleId=' + $('#inheritRoleId').val();
            } else {
                return false;
            }
        } else {
            location.href = $('#baseUrl').val() + '/ot/acl/add' 
              + '/?roleName=' + $('#roleName').val() + '&inheritRoleId=' + $('#inheritRoleId').val();
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
        
        initialStructureCache = $.toJSON(serialize($('#aclEditor'))); // allow page unload
    });
    initialStructureCache = $.toJSON(serialize($('#aclEditor')));
    $(window).on('beforeunload', catchUnload);
});

/**
 * A custom function to serialize the menu in a way that we can use on the backend to 
 * correctly add the permissions and such.
 */
function serialize (items) {
    var serial = [];
    var i = 0;
    items.find('input,select').each(function() {     
        serial[i] = {
            name:        $(this).attr('id'),
            value:       $(this).val(),
            checked:     $(this).attr('checked')
        };
        i++;
    });
    
    return serial;
}

/**
 * On page change, this function is called. If the nav has been edited since the initial load or last 
 * save, then it confirms with the user that they want to ignore nav changes. If no changes were made,
 * then it doesn't do anything.
 */
function catchUnload(e) {
	currentStructure = $.toJSON(serialize($('#aclEditor')));
	
	if(currentStructure != initialStructureCache) {
		$('#aclEditor input[type="submit"]').addClass('highlight');
		e.preventDefault();
		return 'Access permissions edited, but not yet saved.';
	} else {
		$(window).off('beforeunload', catchUnload);
	}
}