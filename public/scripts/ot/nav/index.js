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

    /// setup handlers for undo support
    $('#undoMoveButton').click(sitemapHistory.restoreState);
    $(document).on('keypress', function(e) {
        
        if ((e.ctrlKey || e.metaKey) && (e.which == 122 || e.which == 26)) {
            sitemapHistory.restoreState();
        }
    });
    
    // set up the sortable stuff
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
        
        var dataArray = serialize($('#navList'));
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
    
    /*
    
    // the modal dialog for adding and editing a menu item
    $("#navElementDialog").dialog({ 
        modal: true, 
        autoOpen: false,
        resizable: false,
        width: 720,
        height: 575,
        overlay: { 
            opacity: 0.5, 
            background: "black" 
        },
        buttons: {
             
            "Cancel": function() {
                
                $('#moduleBox').val('').change();
                $('#controllerBox').val('');
                $('#actionBox').val('');
                $('#displayBox').val('');
                $('#linkBox').val('');                
                $('#externalLink').attr('checked', false);
                $('#linkPrefix').show();
            
                $(this).dialog("close"); 
            },       
            "Save": function() { 
                
                var display = $('#displayBox').val();
                var link = $('#linkBox').val();
                
                var module     = ($('#moduleBox').val() == '' || $('#moduleBox').val() == null) ? 'default' : $('#moduleBox').val();
                var controller = ($('#controllerBox').val() == '' || $('#controllerBox').val() == null) ? 'index' : $('#controllerBox').val();
                var action     = ($('#actionBox').val() == '' || $('#actionBox').val() == null) ? 'index' : $('#actionBox').val();
                
                var linkTarget = "_self";
                
                if ($('#externalLink:checked').val() != null) {
                    linkTarget = "_blank";
                }
                
                if (display == "") {
                    alert('You must enter a display name.');
                    return;
                }
                
                if (currentElementId == "") {
                                      
                    var newLi = $('<li id="newElement' + newElementIdCounter + '" name="' + display + '"><a title="' + module + ':' + controller + ':' + action + '" href="' + link + '" target="' + linkTarget + '">' + display + '</a></li>');
                    
                    $('ul#navList').prepend(newLi);
                    
                    addControlButtons(newLi);             
                    newLi.prepend('<div class="dropzone"></div>');
                    
                    newElementIdCounter++;
                    
                    // refresh the sortable so that the new element can be sorted too
                    initDragDrop();                    
                    
                } else {
                    
                    $('#' + currentElementId).attr('name', display);
                    $('#' + currentElementId).children('a:not(.controlButton)').attr('title', module + ":" + controller + ":" + action);
                    $('#' + currentElementId).children('a:not(.controlButton)').attr('target', linkTarget);
                    $('#' + currentElementId).children('a:not(.controlButton)').attr('href', link);
                    $('#' + currentElementId).children('a:not(.controlButton)').text(display);
                }
                
                currentElementId = "";
                
                $('#moduleBox').val('').change();
                $('#controllerBox').val('');
                $('#actionBox').val('');
                $('#displayBox').val('');
                $('#linkBox').val('');
                $('#externalLink').attr('checked', false);
                $('#linkPrefix').show();

                $(this).dialog("close");
            }      
        }       
    }, "close");
        */
    
    $('#addElementButton').click(function(e) {
        e.preventDefault();
        currentElementId = "";
        $("#navElementDialog").dialog("open");
    });
    
    initialStructureCache = $.toJSON(serialize($('#navList ol')));
    $(window).on('beforeunload', catchUnload);
    
    console.log(initialStructureCache);
    
});

//function initDragDrop() {
//    
//    $('#navList li').draggable({
//        handle: ' > span.moveHandle',
//        opacity: .8,
//        addClasses: false,
//        helper: 'clone',
//        zIndex: 100,
//        start: function(e, ui) {
//            sitemapHistory.saveState(this);
//        }
//    });
//    
//    $('#navList li a, ul#masterList div.dropzone').droppable({
//        accept: 'ul#masterList li',
//        tolerance: 'pointer',
//        drop: function(e, ui) {
//            
//            var li = $(this).parent();
//            
//            //if we're dropping this on an element and it's the first child, we'll need a ul to drop into.
//            if (li.children('ul').length == 0 && !$(this).hasClass('dropzone')) {
//                li.append('<ul>');
//            }
//            
//            //ui.draggable is our reference to the item that's been dragged.
//            if ($(this).hasClass('dropzone')) {
//                li.before(ui.draggable);
//            }
//            else {
//                li.addClass('liOpen')
//                  .removeClass('liClosed')
//                  .children('ul').append(ui.draggable);
//            }
//            
//            $('#masterList li.liOpen').not(':has(li:not(.ui-draggable-dragging))').removeClass('liOpen');
//            
//            //reset our background colours.
//            li.find('a,.dropzone').css({ backgroundColor: '', borderColor: '' });
//            li.find('.dropzone').css({ backgroundColor: '', borderColor: '' });
//            
//            sitemapHistory.commit();
//        },
//        over: function() {
//            $(this).filter('a').css({ backgroundColor: '#ccc' });
//            $(this).filter('.dropzone').css({ borderColor: '#aaa' });
//        },
//        out: function() {
//            $(this).filter('a').css({ backgroundColor: '' });
//            $(this).filter('.dropzone').css({ borderColor: '' });
//        }
//    });
//}

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
        
        var linkTarget = ($this.children('target').length != 0) ? $link.attr('target') : '_self';
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
 * Adds the edit, delete, and move handle to an li
 * @param el The element you want to add the buttons to
 */
function addControlButtons(el) {
    
    $(el).prepend('<a class="btn btn-mini controlButton" title="Edit"><i class="icon icon-pencil"></i></a>');
    $(el).prepend('<a class="btn btn-mini btn-danger controlButton" title="Delete"><i class="icon-white icon-minus"></i></a>');
}

/**
 * Sets up the live events for all current and future edit and delete buttons.
 * We do it this way so we don't have to do add the click functionality to each
 * individual one as it gets created. 
 */
function setupLiveEvents() {
    
    // Prevent any links from sending the user to that page.  We need this since
    // we actually use the href as a property.
    $(document).on("click", '#navList ol li a:not(.controlButton)', function(e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    });
        
    $('#navList ol li').on('click', '.deleteElement', function(e) {
        if (confirm("Are you sure you want to delete this element?  This action cannot be undone.")) {
            $(this).parent().slideUp('normal', 
                function() {
                    $(this).remove();
                });                
        }
        
        e.stopImmediatePropagation();
        e.stopPropagation();
    });


    // populates the modal dialog with the link's properties when you click edit
    $('#navList ol li').on('click', '.editElement', function(e) {
        
        var el = $(this).parent();
        
        currentElementId = $(el).attr('id');

        $('#displayBox').val($(el).attr('name'));
        
        var linkTarget = $(el).children('a:not(.controlButton)').attr('target');
        if (linkTarget.toLowerCase() == "_self") {
            $('#externalLink').attr('checked', false);
            $('#linkPrefix').show();
        } else {
            $('#externalLink').attr('checked', true);
            $('#linkPrefix').hide();
        }
        
        var link = $(el).children('a:not(.controlButton)').attr('href');
        
        $('#linkBox').val(link);
        
        var permissions = $(el).children('a:not(.controlButton)').attr('title').split(':');
        
        $('#moduleBox').val(permissions[0] || 'default').change();
        $('#controllerBox').val(permissions[1] || 'index').change();
        $('#actionBox').val(permissions[2] || 'index');
        
        $("#navElementDialog").dialog("open");        
        
        
        e.stopPropagation();
    });
}

var sitemapHistory = {
    stack: new Array(),
    temp: null,
    //takes an element and saves it's position in the sitemap.
    //note: doesn't commit the save until commit() is called!
    //this is because we might decide to cancel the move
    saveState: function(item) {
        sitemapHistory.temp = { item: $(item), itemParent: $(item).parent(), itemAfter: $(item).prev() };
    },
    commit: function() {
        if (sitemapHistory.temp != null) sitemapHistory.stack.push(sitemapHistory.temp);
    },
    //restores the state of the last moved item.
    restoreState: function() {
        var h = sitemapHistory.stack.pop();
        if (h == null) return;
        if (h.itemAfter.length > 0) {
            h.itemAfter.after(h.item);
        }
        else {
            h.itemParent.prepend(h.item);
        }
        //checks the classes on the lists
        $('#navList li.liOpen').not(':has(li)').removeClass('liOpen');
        $('#navList li:has(ol li):not(.liClosed)').addClass('liOpen');
    }
};

/**
 * On page change, this function is called. If the nav has been edited since the initial load or last 
 * save, then it confirms with the user that they want to ignore nav changes. If no changes were made,
 * then it doesn't do anything.
 */
function catchUnload(e) {
    
	currentStructure = $.toJSON(serialize($('#navList')));
	
	if(currentStructure != initialStructureCache) {
            $('#saveNavButton').addClass('highlight');
            e.preventDefault();
            return 'Navigation edited, but not yet saved.';
	} else {
            $(window).off('beforeunload', catchUnload);
	}
}