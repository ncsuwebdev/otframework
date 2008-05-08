/*	Script: prototype.compatability.js
		This library extends the <http://mootools.net> framework on which the cnet.global.framework is based.


Dependancies:
	 mootools - <Moo.js>, <Utility.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>, <XHR.js>, <Ajax.js>
	 cnet libraries - <element.cnet.js>, <string.cnet.js>
	
Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>

Description:
		This script specifically extends Mootools to add functions to make the framework backwards
		compatable with Prototype lite, originally authored by the team at mad4milk.net, and then altered by
		CNET.com to include a few other things, like the Event object.
	*/

/*	Prototype JavaScript (lite version from CNET via mad4milk.net) framework, modified to work on top of MooTools
 *	(c) 2005 Sam Stephenson <sam@conio.net>
 *	Prototype is freely distributable under the terms of an MIT-style license.
 *	For details, see the Prototype web site: http://prototype.conio.net/
/*--------------------------------------------------------------------------*/

/*	Class: Prototype	
		This is the "lite" version of Prototype, originally authored by the mad4milk team.
*/
var Prototype = {
	Version: 'CNET Prototype Lite, MooTools edition ver. 1.0',
	ScriptFragment: '(?:<script.*?>)((\n|\r|.)*?)(?:<\/script>)',
	emptyFunction: function() {},
	K: function(x) {return x}
};
/*	Class: String
		This extends the <String> prototype.
	*/
Object.extend(String.prototype, {
/*	Property: camelize
		returns the string in camelCase function.
		
		Example:
		>"this is a string".camelize()
		> > thisIsAString
	*/
	camelize: function() {
		var oStringList = this.split('-');
		if (oStringList.length == 1) return oStringList[0];

		var camelizedString = this.indexOf('-') == 0
			? oStringList[0].charAt(0).toUpperCase() + oStringList[0].substring(1)
			: oStringList[0];

		for (var i = 1, len = oStringList.length; i < len; i++) {
			var s = oStringList[i];
			camelizedString += s.charAt(0).toUpperCase() + s.substring(1);
		}

		return camelizedString;
	}
});
/*	Class: Position(deprecated)
		Deprecated class to get the position of a DOM element.
	*/

var Position = {
/*	
		Property: cumulativeOffset
		Returns the offset top and left values for an element. This is deprecated. You should 
		instead use <Element.getOffset> in the mootools library.
		
		Arguments:
		element - the DOM element to get the left and top values
		
		Returns:
		Array: [left, top]
		
		Example:
		>Position.cumulativeOffset(myElement);
		> > [100, 100]
	*/
	cumulativeOffset: function(element) {
		return [Element.getTop(), Element.getLeft()];
	}
};
/*	Class: Event(deprecated)
		Part of the Prototype library; deprecated. You should
		instead use <Element.addEvent> from the mootools library.
	*/
if (!window.Event) { var Event = new Object(); }
Object.extend(Event, {
/*	Property: element
		Deprecated, returns the target of the event (i.e. the clicked (or whatever) DOM element.
		You should instead use <Element.addEvent> from the mootools library.
		
		Parameter: 
		event - the event that fired
		
		Example:
		>Event.element(eventObject)
		> > clickedElement
	*/
	element: function(event) {return new Event(event).target()},
/*	Property: stop
		Stops an event monitor; deprecated. You should
		instead use <Element.addEvent> from the mootools library.
		
		Parameter:
		event - the event you wish to stop monitoring
		
		Example:
		>Event.stop(eventObject);
	*/
	stop: function(event) {
		new Event(event).stop();
	},
/*	Property: findElement
		Finds the first node with the given tagName starting from the
		node the event was triggered on; traverses the DOM upwards; deprecated.
		You should instead use <Element.getBrother>, <Element.getPrevious>, 
		<Element.getNext>, or <Element.getFirst> from the mootools library.
		
		Arguments:
		event - the event object that fired.
		tagName - the html tag name (a, div, etc.) that you want to find
	*/
	findElement: function(event, tagName) {
		var element = new Event(event).target;
		while (element.parentNode && (!element.tagName || (element.tagName.toUpperCase() != tagName.toUpperCase())))
			element = element.parentNode; return element;
	},
/*	Property: observe
		Observes an element for an event and then executes the passed in function, deprecated.
		You should instead use <Element.addEvent> from the mootools library.
		
		Arguments:
		element - the element you wish to monitor
		name - the event you wish to capture ("click", "load", "mouseover", etc.)
		observer - the function you wish to execute when the event fires
		useCapture - if true, handles the event in the capture phase and if false in the bubbling phase.
	*/
	observe: function(element, name, observer, useCapture) {
		$(element).addEvent(name, observer);
	},
/*	Property: stopObserving
		Removes an event handler from the event, deprecated.
		You should instead use <Element.removeEvent> from the mootools library.
		
		Arguments:
		element - object or id
		name - event name (like 'click')
		observer - function that is handling the event
		useCapture - if true handles the event in the capture phase and if false in the bubbling phase.
	*/
	stopObserving: function(element, name, observer, useCapture) {
		$(element).removeEvent(name, observer);
	},
/*	Property: onDOMReady
		Executes a function when the DOM is ready; deprecated. You should use <window.onDomReady> instad
		
		Arguments:
		function - the function you wish to execute when the DOM is ready
		
		Examples:
		>Event.onDOMReady(myFunction)
		>
		>Event.onDOMReady(function(){
		> alert('the DOM is ready');
		>});	*/
	onDOMReady : function(f) {
		window.onDomReady(f);
	}
});

/*	Class: Element(deprecated)
		These extentions are deprecated and are here for Prototype.lite backwards compatibility.
	*/
Object.extend(Element, {
/*	Property: getDimensions
		Gets the width and height of the element; deprecated.
		You should instead use <Element.getDimensions> (as in $(id).getDimensions) or <Element.getStyle>.
		
		Returns:
		object - {width: #, height: #}
		
		Arguments:
		element - the element to get the dimensions.
		
		Example:
		Element.getDimensions(myDOMElement | myDOMElementId)
			*/
	getDimensions: function(element) {
		return $(element).getDimensions();
	},
/*	Property: visible
		Returns true (the element is visible) or false (it is not), deprecated.
		You should instead use <Element.visible> as in $(id).visible();
		
		Returns:
		boolean - true: visible, false: hidden
		
		Arguments:
		element - the element to inspect
		
		Example:
		Element.visible(myDOMElement | myDOMElementId)
	*/
	visible: function(element) {
		return $(element).visible();
	},
/*	Property: toggle
		hides/unhides an element, deprecated.
		You should instead use <Element.toggle> (as in $(id).toggle())
		
		Arguments:
		element - the element to hide/show
		
		Example:
		Element.toggle(myDOMElement | myDOMElementId)
	*/
	toggle: function(element) {
		return $(element).toggle();
	},
/*	Property: hide
		hides an element; deprecated.
		You should instead use <Element.hide> (as in $(id).hide())
		
		Arguments:
		element - the element to hide
		
		Example:
		Element.hide(myDOMElement | myDOMElementId)
	*/
	hide: function(element) {
		return $(element).hide();
	},
/*	Property: show
		shows an element; deprecated.
		You should instead use <Element.show> (as in $(id).show())
		
		Arguments:
		element - the element to show
		
		Example:
		Element.show(myDOMElement | myDOMElementId)
	*/
	show: function(element) {
		return $(element).show();
	},
/*	Property: cleanWhitespace
		Removes all empty nodes from an element and its children, deprecated.
		You should instead use <Element.cleanWhitespace> as in ($(id).cleanWhitespace()).
		
		Arguments:
		element - the element to clean
		
		Example:
		>Element.cleanWhitespace(myDOMElement | myDOMElementId)
	*/
	cleanWhitespace: function(element) {
		return $(element).cleanWhitespace();
	},
/*	Property: find
		Returns an element from the node's array (such as parentNode), deprecated.
		
		Arguments:
		element - the element you wish to find from.
		what - the value you wish to find (such as 'parentNode')
	*/
	find: function(element, what) {
		return $(element).find(what);
	},
/*	Property: replace
		Replaces the element with the html you pass in, deprecated.
		You should instead use <Element.replace> (as in $(id).replace(html)).
		
		Parameters - 
		element - the element you want to replace
		html - the html you want to replace it with.
		
		Example:
		>Element.replace(myDOMElement | myDOMElementId, htmlToReplaceMyElement)
	*/
	replace: function(element, html) {
		$(element).replace(html);
	},
	/*	Property: empty
			Returns a boolean; true = the node is empty, deprecated.
			You should instead use <Element.empty> (as in $(id).empty()).
			
			Arguments: 
			element - the element you wish inspect
			
			Example:
			>Element.empty(myDOMElement | myDOMElementId)
				*/
	empty: function(element) {
		return $(element).empty();
	},
	/*	Property: hasClassName
			Returns a boolean; true = the node has the class name, deprecated.
			You should use <Element.hasClassName> (as in $(id).hasClassName(class)).
			
			Arguments:
			element - the element or element id you wish to inspect
			className - the class name to check for
			
			Example:
			>Element.hasClassName('myElement', 'selected') //does my element have the 'selected' class?
		*/
	hasClassName: function(element, className){
		return $(element).hasClass(className);
	}
});

/*	Class: Ajax.Request (deprecated)
		This a compatability syntax for Prototype.js Ajax requests; you should use <Ajax> in Mootools.
	*/
Ajax.Request = new Class({
  initialize: function(url, options) {
		if(options.parameters) url += "?"+options.parameters;
		dbug.log('using legacy Ajax.Request object\n options: %1.o', options);
		if(options.onComplete){
			this.onCompleteFunction = options.onComplete;
			options.onSuccess = this.onComplete.bind(this);
		}
		if(options.onFailure){
			this.onFailureFunction = options.onComplete;
			options.onFailure = this.onFailure.bind(this);
		}
		this.ajax = new Ajax(url,options).request();
    this.transport = (ajax).transport;
  },
	
	onComplete: function() {
		if(this.onCompleteFunction && this.ajax.isSuccess(this.ajax.transport.status))
			this.onCompleteFunction(this.ajax.transport);
	},
	onFailure: function() {
		if(this.onFailureFunction && !this.ajax.isSuccess(this.ajax.transport.status))
			this.onFailureFunction(this.ajax.transport);
	},

  request: function(url) {
    this.ajax.request(url);
  },

  header: function(name) {
    try {
      return this.ajax.transport.getResponseHeader(name);
    } catch (e) { return null;}
  },

  evalJSON: function() {
    try {
      return Json.evaluate(this.ajax.header('X-JSON'));
    } catch (e) { return null;}
  },

  evalResponse: function() {
    try {
      return Json.evaluate(this.ajax.transport.responseText);
    } catch (e) {
      this.dispatchException(e);
			return null;
    }
  },

  dispatchException: function(exception) {
    (this.options.onException || Prototype.emptyFunction)(this, exception);
    Ajax.Responders.dispatch('onException', this, exception);
  }
});
/*	Class: Ajax.Updater (deprecated)
		This a compatability syntax for Prototype.js Ajax requests; you should use <Ajax> in Mootools.
	*/
Ajax.Updater = Ajax.Request.extend({
  initialize: function(container, url, options) {
		if(options.parameters) url += "?"+options.parameters;
		dbug.log('using legacy Ajax.Updater object\n options: %1.o', options);
		this.options = options;
    this.onComplete = options.onComplete || Class.empty;
    this.options.onComplete = (function(transport, object) {
      this.updateContent();
      this.onComplete(transport, object);
    }).bind(this);
    this.containers = {
      success: container.success ? $(container.success) : $(container),
      failure: container.failure ? $(container.failure) :
        (container.success ? null : $(container))
    };
		this.options.fireNow = false;
		this.ajax = new Ajax(url,this.options);
    this.transport = this.ajax.transport;
		this.ajax.request();
  },

  updateContent: function() {
    var receiver = this.ajax.isSuccess(this.ajax.transport.status) ?
      this.containers.success : this.containers.failure;
    var response = this.transport.responseText;

    if (!this.options.evalScripts)
      response = response.stripScripts();

    if (receiver) {
      $(receiver).setHTML(response);
    }
  }
});

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/legacy/prototype.compatability.js,v $
$Log: prototype.compatability.js,v $
Revision 1.11  2007/03/09 20:17:14  newtona
strict javascript warnings cleaned up

Revision 1.10  2007/03/03 00:38:39  newtona
fixed a bug with the ajax compatibility

Revision 1.9  2007/01/26 06:00:58  newtona
syntax update for mootools 1.0

Revision 1.8  2007/01/22 22:49:23  newtona
added .request() to the ajax call

Revision 1.7  2007/01/22 21:53:23  newtona
updated for mootools version 1.0

Revision 1.6  2006/11/21 23:56:57  newtona
bug fixes for onComplete functionality

Revision 1.5  2006/11/17 19:58:18  newtona
syntax fixes

Revision 1.4  2006/11/17 19:41:49  newtona
updating prototype compatability; switching onDOMReady to use Mootools, adding ajax compatability

Revision 1.3  2006/11/17 19:39:33  newtona
updating prototype compatability; switching onDOMReady to use Mootools, adding ajax compatability

Revision 1.2  2006/11/02 21:34:40  newtona
added cvs footer


*/
