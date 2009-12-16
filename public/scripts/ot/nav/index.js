var baseUrl = "";
var saveUrl = "";
var resources = null;

var currentModule = "";
var currentController = "";

var newElementIdCounter = 0;

var currentElementId = "";

$('document').ready(function() {

    baseUrl = $('#baseUrl').val();
    saveUrl = baseUrl + "/ot/nav/save";
    
    $('ul#masterList li').each(function() {
        var newId = "navEditor_" + $(this).attr('id');
        $(this).attr('id', newId);
        
        var link = $(this).children('a:not(.controlButton)').attr('href');
        var linkTarget = $(this).children('a').attr('target').toLowerCase();
            
        if (linkTarget == "_self") {
            if (baseUrl != "") {            
                link = link.split(baseUrl + "/");
                link = link[1];
            } else {
                link = link.substring(1, link.length);
            }
            $(this).children('a:not(.controlButton)').attr('href', link);
        }    
        addControlButtons($(this));
    });
    
	initTree('ul#masterList');
	
	$('#externalLink').click(function() {
		if ($('#externalLink:checked').val() != null) {
			$('#linkPrefix').hide();
		} else {
			$('#linkPrefix').show();
		}
	});
	
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
	
	$('#saveNavButton').click(function() {
		
		var dataArray = serialize($('ul#masterList'));
		var str = $.toJSON(dataArray);
		
		$.post(saveUrl, {data: str}, 
			  function (data) {
			    alert(data.msg);
			    if (data.rc == 1) {
			    	window.location.reload(true);
			    }
			  }, "json");
	});
	
	$.getJSON(baseUrl + "/ot/nav/get-resources", 
	    function(data) {
    		resources = data;
    		$('<option>').attr('value', '').text('Choose A Module').appendTo('#moduleBox');
    		$.each(data.modules, function(i,item) {
    			$('<option>').attr('value', item.name).text(item.name).appendTo('#moduleBox');
    	});
    });
	
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

	        		$('ul#masterList').prepend(newLi);
	        		
	        		addControlButtons(newLi);     	    
	        	    newElementIdCounter++;
	        	    
	        	    var clone = $('ul#masterList').clone();
	        	    
	        	    $('ul#masterList').remove();
	        	    
	        	    $('#navEditorContainer').append(clone);
	        	    
	        	    initTree('ul#masterList');
	        	    
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
    
    $('#addElementButton').click(function(e) {
    	e.preventDefault();
    	currentElementId = "";
    	$("#navElementDialog").dialog("open");
    });
});

function serialize (items) 
{
	var serial = [];
	var i = 0;
	items.children('li').each(function() {
		
		var linkTarget = ($(this).children('target')) ? $(this).children('a:not(.controlButton)').attr('target') : '_self';
		linkTarget = linkTarget.toLowerCase();
		
	    var link = $(this).children('a:not(.controlButton)').attr('href');
	    link = (link != undefined) ? link.toLowerCase() : "";
	    	    
		serial[i] = {
			display:     $(this).attr('name'),
			permissions: ($(this).children('a:not(.controlButton)')) ? $(this).children('a:not(.controlButton)').attr('title') : '',
			link:        link,
			target:      linkTarget,
			children:    ($(this).children('ul')) ? serialize($(this).children('ul')) : []
		};
		i++;
	});
	
	return serial;
}

function addControlButtons(el) {
	
	$(el).prepend('<a class="controlButton deleteElement ui-state-default ui-corner-all linkButtonNoText">&nbsp;<span class="ui-icon ui-icon-minusthick" title="Delete Element"></span></a>');
	$(el).prepend('<a class="controlButton editElement ui-state-default ui-corner-all linkButtonNoText">&nbsp;<span class="ui-icon ui-icon-pencil" title="Edit Element"></span></a>');
	$(el).prepend('<span class="ui-icon ui-icon-arrow-4 moveHandle"></span>');
	
	$('a.ui-state-default').hover(
		function(){ $(this).addClass('ui-state-hover'); }, 
		function(){ $(this).removeClass('ui-state-hover'); }
  	);
}

function initTree(el)
{	
	$('ul#masterList li').children('a').each(function() {
		$(this).click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			return false;
		});
    });
	
	$('ul#masterList li').find('.deleteElement').click(function() {
		if (confirm("Are you sure you want to delete this element?  This action cannot be undone.")) {
			$(this).parent().slideUp('normal', 
				function() {
					$(this).remove();
				});
		}
	});

	$('ul#masterList li').find('.editElement').click(function() {
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
	});
	
	$(el).jTree();
}