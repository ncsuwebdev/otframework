var baseUrl = "";
var saveUrl = "";
var resources = null;

var currentModule = "";
var currentController = "";

var newElementIdCounter = 0;

var currentElementId = "";

var initialStructureCache;

$('document').ready(function() {

    baseUrl = $('#baseUrl').val();
    saveUrl = baseUrl + "/ot/nav/save";
    
    // setup the li so we have all information we need to edit it and such
    // also add the control buttons to each one (the move handle, edit, and delete)
    $('#navList ol li').each(function(index, el) {
        
        $this = $(el);
        
        var newId = "navEditor_" + $this.attr('id');
        $this.attr('id', newId);
        
        var a    = $this.find('a.link');
        var link = a.attr('href');
        var linkTarget = a.attr('target').toLowerCase();
            
        if (linkTarget == "_self") {
            if (baseUrl != "") {            
                link = link.split(baseUrl + "/");
                link = link[1];
            } else {
                link = link.substring(1, link.length);
            }
                        
            a.attr('href', link);
        }    
        
        if ($this.children('ol').children().length != 0) {
            $this.addClass('liOpen');
        }
    });
    
    // add the live events to watch for edit and delete buttons
    setupLiveEvents();
   
    // set up the nestable sortable stuff
    $('#navList').nestable({});
    
    // show or hide the prefix for external links
    $('#externalLink').click(function() {
        if ($('#externalLink:checked').val() != null) {
            $('#linkPrefix').hide();
        } else {
            $('#linkPrefix').show();
        }
    });
    
    // this stuff all handles the conditional drop down stuff to let you select
    // the module, controller, and action for the permissions for a menu item
    $('#moduleBox').change(function() {
        
        if ($(this).val() == "") {
            $('#controllerBox').empty();
            $('#actionBox').empty();
            return;
        }
        
        $('#controllerBox').empty();
        currentModule = $(this).val();
        $('<option>').attr('value', '').text('Choose A Controller').appendTo('#controllerBox');
        $.each(resources.modules[currentModule].controllers, function(i,item) {
            $('<option>').attr('value', item.name).text(item.name).appendTo('#controllerBox');    
        });
        
        $('#controllerBox').change();
    });
    
    $('#controllerBox').change(function() {
        
        if ($(this).val() == "") {
            $('#actionBox').empty();
            return;
        }
        
        $('#actionBox').empty();
        currentController = $(this).val();
        $('<option>').attr('value', '').text('Choose An Action').appendTo('#actionBox');
        $.each(resources.modules[currentModule].controllers[currentController].actions, function(i,item) {
            $('<option>').attr('value', item).text(item).appendTo('#actionBox');    
        });
    });
    
    // does the stuff to save the nav to the database
    $('#saveNavButton').click(function() {
        
        var dataArray = serialize($('#navList ol:first'));
        var str = $.toJSON(dataArray);
        
        $.post(saveUrl, {data: str}, 
              function (data) {
                alert(data.msg);
                if (data.rc == 1) {
                    window.location.reload(true);
                }
              }, "json");
        initialStructureCache = str;
        $('#saveNavButton').removeClass('highlight');
    });
    
    // grabs the modules, controllers, and actions, available from the ACL to
    // populate the corresponding boxes in the edit/add window
    $.getJSON(baseUrl + "/ot/nav/get-resources", 
        function(data) {
            resources = data;
            $('<option>').attr('value', '').text('Choose A Module').appendTo('#moduleBox');
            $.each(data.modules, function(i,item) {
                $('<option>').attr('value', item.name).text(item.name).appendTo('#moduleBox');
        });
    });
    
   
    // the modal dialog for adding and editing a menu item
    $("#addElementModal").modal()
        .on('hide', function() {
            resetForm();
        });
    
    $('#addElementButton').click(function(e) {
        e.preventDefault();
        
        $('#modal-title').text('Add New Navigation Element');
        currentElementId = "";      
        resetForm();
        $("#navElementModal").modal('show');
    });
    
    
    $('#navElementSaveButton').click(function(e) {
        saveForm();
    });
    
    initialStructureCache = $.toJSON(serialize($('#navList ol:first')));
    $(window).on('beforeunload', catchUnload);
    
});


function resetForm() {
    
    $('#moduleBox').val('').change();
    $('#controllerBox').val('');
    $('#actionBox').val('');
    $('#displayBox').val('');
    $('#linkBox').val('');                
    $('#externalLink').attr('checked', false);
    $('#linkPrefix').show();
}


function saveForm() {
    
    var display = $('#displayBox').val();
    var link = $('#linkBox').val();

    var module     = ($('#moduleBox').val() == '' || $('#moduleBox').val() == null) ? 'default' : $('#moduleBox').val();
    var controller = ($('#controllerBox').val() == '' || $('#controllerBox').val() == null) ? 'index' : $('#controllerBox').val();
    var action     = ($('#actionBox').val() == '' || $('#actionBox').val() == null) ? 'index' : $('#actionBox').val();

    var linkTarget = "_self";

    if ($('#externalLink').is(':checked')) {
        linkTarget = "_blank";
    }

    if (display == "") {
        alert('You must enter a display name.');
        return;
    }

    if (currentElementId == "") {

        var htmlStr = '<li class="dd-item" data-id="newElement' + newElementIdCounter + '" id="newElement' + newElementIdCounter + '" name="' + display + '">'
                    + '<div class="dd-handle dd3-handle">Drag</div>'
                    + '<div class="dd3-content" name="' + display + '">'
                    + '<a class="link" title="' + module + ':' + controller + ':' + action + '" href="' + link + '" target="' + linkTarget + '">' + display + '</a>'
                    + '</div>';
               
        var newLi = $(htmlStr);

        $('#navList ol:first').prepend(newLi);

        addControlButtons(newLi);

        newElementIdCounter++;

    } else {

        $('#' + currentElementId).attr('name', display);
        $('#' + currentElementId).find('a.link').attr('title', module + ":" + controller + ":" + action);
        $('#' + currentElementId).find('a.link').attr('target', linkTarget);
        $('#' + currentElementId).find('a.link').attr('href', link);
        $('#' + currentElementId).find('a.link').text(display);
    }

    currentElementId = "";

    $('#moduleBox').val('').change();
    $('#controllerBox').val('');
    $('#actionBox').val('');
    $('#displayBox').val('');
    $('#linkBox').val('');
    $('#externalLink').attr('checked', false);
    $('#linkPrefix').show();

    $('#navElementModal').modal("hide");
}

/**
 * Adds the edit, delete, and move handle to an li
 * @param el The element you want to add the buttons to
 */
function addControlButtons(el) {
    
    var $content = $(el).find('div.dd3-content');
    
    $content.prepend('<a class="btn btn-mini controlButton editElement" title="Edit"><i class="icon icon-pencil"></i></a>');
    $content.prepend('<a class="btn btn-mini btn-danger controlButton deleteElement" title="Delete"><i class="icon-white icon-minus"></i></a>');
}


/**
 * A custom function to serialize the menu in a way that we can use on the backend to 
 * correctly add the permissions and such.
 */
function serialize (items) {
    
    var serial = [];
    var i = 0;
    items.children('li').each(function(index, el) {
        
        $this = $(el);
        var $link = $(el).find('a.link');
        
        var linkTarget = ($link.attr('target')) ? $link.attr('target') : '_self';
        linkTarget = linkTarget.toLowerCase();
        
        var href = $link.attr('href');
        href = (href != undefined) ? href : "";
                
        serial[i] = {
            display:     $this.attr('name'),
            permissions: ($this.find('a.link').length != 0) ? $this.find('a.link').attr('title') : '',
            link:        href,
            target:      linkTarget,
            children:    ($this.children('ol').length != 0) ? serialize($(this).children('ol')) : []
        };
        i++;
    });
    
    return serial;
}


/**
 * Sets up the live events for all current and future edit and delete buttons.
 * We do it this way so we don't have to do add the click functionality to each
 * individual one as it gets created. 
 */
function setupLiveEvents() {
    
    // Prevent any links from sending the user to that page.  We need this since
    // we actually use the href as a property.
    $(document).on("click", '#navList ol li a.link', function(e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    });
        
    $('#navList').delegate('.deleteElement', 'click', function(e) {
        if (confirm("Are you sure you want to delete this element?  This action cannot be undone.")) {
            $(this).closest('li').slideUp('normal', 
                function() {
                    $(this).closest('li').remove();
                });                
        }
        
        e.stopImmediatePropagation();
        e.stopPropagation();
    });


    // populates the modal dialog with the link's properties when you click edit
    $('#navList').delegate('.editElement', 'click', function(e) {
        
        $('#modal-title').text('Edit Navigation Element');
        
        var $el = $(this).parent();
        
        currentElementId = $el.parent().attr('id');

        $('#displayBox').val($el.attr('name'));
        
        var $link = $el.find('a.link');
        
        var linkTarget = $link.attr('target').toLowerCase();
        
        if (linkTarget == "" || linkTarget == "_self") {
            $('#externalLink').attr('checked', false);
            $('#linkPrefix').show();
        } else {
            $('#externalLink').attr('checked', true);
            $('#linkPrefix').hide();
        }
        
        $('#linkBox').val($link.attr('href'));
        
        var permissions = $link.attr('title').split(':');
        
        $('#moduleBox').val(permissions[0] || 'default').change();
        $('#controllerBox').val(permissions[1] || 'index').change();
        $('#actionBox').val(permissions[2] || 'index');
        
        $("#navElementModal").modal("show");      
        
        e.stopPropagation();
    });
}

/**
 * On page change, this function is called. If the nav has been edited since the initial load or last 
 * save, then it confirms with the user that they want to ignore nav changes. If no changes were made,
 * then it doesn't do anything.
 */
function catchUnload(e) {
    
	currentStructure = $.toJSON(serialize($('#navList ol:first')));
	
	if(currentStructure != initialStructureCache) {
            $('#saveNavButton').addClass('highlight');
            e.preventDefault();
            return 'Navigation edited, but not yet saved.';
	} else {
            $(window).off('beforeunload', catchUnload);
	}
}