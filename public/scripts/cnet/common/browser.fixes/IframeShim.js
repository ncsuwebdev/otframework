/*
Script: IframeShim.js
Iframe shim class for hiding elements below a floating DOM element.

Dependancies:
	 mootools - <Moo.js>, <Utility.js>, <Common.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>


Class: IframeShim
		There are two types of elements that (sometimes) prohibit you from 
		positioning a DOM element over them: some form elements and some
		flash elements. The two options you have are:
			- to hide these elements when your dom is going to be over them; 
			this works if you know your DOM element is going to completely
			obscure that element
			
			- an iframe shim - where you put an iframe below your element but
			ABOVE the form/flash element. more details here:
			http://www.macridesweb.com/oltest/IframeShim.html
			
		The IframeShim class handles a lot of the dirty work for you.

			Arguments:
			element -  (required; DOM element or its id) the element you want to put this shim under
			display -  (boolean; optional) display the shim on instantiation; defaults to false
			name -  (string; optional) the id you want to give the new DOM element of the iframe shim; gets "_shim" added to it
			zindex -  (integer; optional) the index of the shim; optional, default is 1 less than the element
			margin -  (integer; optional) make the iframe smaller than the element to give a buffer (for 
							things like shadows)
			offset -  (object: {x:#, y:#}; optional) move the iframe up/down, left/right relative to 
							the element
			className - (string; optional) className for the shim; defaults to "iframeShim"
			browsers - (boolean; optional) allows you to specify the browsers that the iframe should show up for;
							defaults to ie6 or gecko on a mac (window.ie6 || (window.gecko && navigator.userAgent.test('mac', 'i'))). 
							Example usage: *browsers: window.ie6 || window.khtml* //will show for safari, konqueror, and ie6

			Events:
			onInject - (callback) function executed when the iframe is added to the DOM (which waits until window.onload)
		
		then, when you make your floating DOM show up you just execute .hide() or .show()
		to make the shim do its magic. You can also call .position() if the element the
		shim is supposed to be under happens to move.
		
		example:
		
		> <div id="myFloatingDiv">stuff</div>
		> <script>
		> 	var myFloatingDivShim = new IframeShim({
		> 		element: 'myFloatingDiv',
		> 		display: false,
		> 		name: 'myFloatingDivShimId'
		> 	});
		> 	function showMyFloatingDiv(){
		> 		$('myFloatingDiv').show();
		> 		myFloatingDivShim.show();
		> 	}
		> </script>
		
		See also <hide>, <show>, <position>
	*/
	
var IframeShim = new Class({
	options: {
		element: false,
		name: '',
		className:'iframeShim',
		display:false,
		name: '',
		zindex: false,
		margin: 0,
		offset: {
			x: 0,
			y: 0
		},
		browsers: (window.ie6 || (window.gecko && navigator.userAgent.test('mac', 'i')))
	},
	initialize: function (options){
		this.setOptions(options);
		//legacy
		if(this.options.offset && this.options.offset.top) this.options.offset.y = this.options.offset.top;
		if(this.options.offset && this.options.offset.left) this.options.offset.x = this.options.offset.left;
		this.element = $(this.options.element);
		if(!this.element) return;
		else this.makeShim();
		return;
	},
	makeShim: function(){
		this.shim = new Element('iframe');
		this.id = (this.options.name || new Date().getTime()) + "_shim";
		if(this.element.getStyle('z-Index').toInt()<1 || isNaN(this.element.getStyle('z-Index').toInt()))
			this.element.setStyle('z-Index',5);
		var z = this.element.getStyle('z-Index')-1;
		
		if($chk(this.options.zindex) && 
			 this.element.getStyle('z-Index').toInt() > this.options.zindex)
			 z = this.options.zindex;
			
 		this.shim.setStyles({
			'position': 'absolute',
			'zIndex': z,
			'border': 'none',
			'filter': 'progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)'
		}).setProperties({
			'src':'javascript:void(0);',
			'frameborder':'0',
			'scrolling':'no',
			'id':this.id
		}).addClass(this.options.className);
		
		var inject = function(){
			this.shim.injectInside(document.body);
			if(this.options.display) this.show();
			else this.hide();
			this.fireEvent('onInject');
		};
		if(this.options.browsers){
			if(window.ie && !IframeShim.ready) {
				window.addEvent('load', inject.bind(this));
			} else {
				inject.bind(this)();
			}
		}
	},

/*	
		Property: position
		This will reposition the iframe element. Call this when you move or resize
		the iframe element.
	*/
	position: function(shim){
		if(!this.options.browsers || !IframeShim.ready) return;
		var wasVis = this.element.getStyle('display')!='none';
		if(!wasVis) this.element.setStyle('display','block');
		var size = this.element.getSize().size;
		var pos = this.element.getPosition();
		if(! wasVis) this.element.setStyle('display','none');
		if($type(this.options.margin)){
			size.x = size.x-(this.options.margin*2);
			size.y = size.y-(this.options.margin*2);
			this.options.offset.x += this.options.margin; 
			this.options.offset.y += this.options.margin;
		}
		//offset.x+=100;// ******* This is my change ********
 		this.shim.setStyles({
			'width': size.x + 'px',
			'height': size.y + 'px'
		}).setPosition({
			relativeTo: this.element,
			offset: this.options.offset
		});
	},
/*	
		Property: hide
		This will hide the IframeShim object. If you don't call this when you
		hide the element that's over the flash or select list, then that thing
		will still be hidden.
	*/
	hide: function(){
		if(!this.options.browsers) return;
		this.shim.setStyle('display','none');
	},

/*	
		Property: show
		This will obscure any form elements or flash elements below the iframe
		shim element. Call this when you show your floating element.
	*/
	show: function(){
		if(!this.options.browsers) return;
		this.shim.setStyle('display','block');
		this.position();
	},
/*	
		Property: remove
		This will remove the iframe from the DOM.
	*/
	remove: function(){
		if(!this.options.browsers) return;
		this.shim.remove();
	}
});
IframeShim.implement(new Options);
IframeShim.implement(new Events);
//legacy namespace
var iframeShim = IframeShim;
window.addEvent('load', function(){
	IframeShim.ready = true;
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/browser.fixes/IframeShim.js,v $
$Log: IframeShim.js,v $
Revision 1.22  2007/08/20 18:11:49  newtona
Iframeshim: better handling for methods when the shim isn't ready
stickyWin: fixed a bug for the cssClass option in stickyWinHTML
html.table: push now returns the dom elements it creates
jsonp: fixed a bug; request no longer requires a url argument (my bad)
Fx.SmoothMove: just tidying up some syntax.
element.dimensions: updated getDimensions method for computing size of elements that are hidden; no longer clones element

Revision 1.21  2007/08/15 01:03:32  newtona
Added more event info for Autocompleter.js
Slimbox no longer adds css to the page if there aren't any images found for the instance
Iframeshim now exits quietly if you try and position it before the dom is ready
jsonp now handles having more than one request open at a time
removed a console.log statement from window.cnet.js (shame on me for leaving it there)

Revision 1.20  2007/08/08 18:22:06  newtona
fixed a bug with Element.getDimensions (which affected Fx.SmoothMove, Fx.SmoothShow, Element.setPosition, and the bazillion other things that use it). would only show up under certain CSS layout situations.

Revision 1.19  2007/05/03 18:24:24  newtona
iframeshim: removed a dbug line
modalizer: only hide select lists for browsers that need it
product picker: added a try/catch, updated cnet api link/code

Revision 1.18  2007/04/12 19:21:11  newtona
iframeshim now defaults to only activate for ie6 AND Firefox on a mac

Revision 1.17  2007/04/12 17:09:32  newtona
*** empty log message ***

Revision 1.16  2007/04/12 17:03:28  newtona
iframeshim now defaults to only activate for ie6

Revision 1.15  2007/04/11 23:11:51  newtona
because IframeShim appends the iframe to the document.body, in IE iframe now waits for window.onload

Revision 1.14  2007/04/09 19:04:18  newtona
fixed a binding problem

Revision 1.13  2007/04/05 00:13:12  newtona
local.vars.js: removing $type.isNumber dependency
login.status.js: no change; fixed typo in docs
search.functions.js: removing $type.isNumber dependency
stickyWinDefaultLayout: infinite buttons!
iframeShim.js: fixed an ie bug that caused it to abort the page

Revision 1.12  2007/03/26 18:30:10  newtona
iframeShim: fixed reference to options (should be this.options)
element.cnet: removed some dbug lines

Revision 1.11  2007/03/23 21:18:42  newtona
fixed reference to options (should be this.options)

Revision 1.10  2007/03/23 20:19:48  newtona
Iframeshim: added className; updated docs
StickyWin: added edge support (see Element.setPosition)

Revision 1.9  2007/03/23 17:17:37  newtona
iframe in iframeshim now gets it's id set (again)

Revision 1.8  2007/03/20 21:02:51  newtona
docs update

Revision 1.7  2007/03/20 19:22:50  newtona
continued refactoring; fixing some IE inconsistencies.

Revision 1.6  2007/03/16 05:24:36  newtona
refactored and cleaned up.

Revision 1.5  2007/02/23 20:02:51  newtona
adjusted z-index logic so that the iframe is always a positive number

Revision 1.4  2007/02/23 18:47:29  newtona
iframe target now gets $() around it.

Revision 1.3  2007/02/07 20:49:21  newtona
implemented Options class

Revision 1.2  2007/01/22 21:09:24  newtona
updated docs for namespace change (IframeShim.js)

Revision 1.1  2007/01/22 21:08:30  newtona
renamed from iframeshim.js

Revision 1.3  2007/01/22 19:54:59  newtona
removed browser.sniffer - this stuff is in mootools 1.0
renamed iframeShim to IframeShim

Revision 1.2  2007/01/11 20:45:49  newtona
fixed syntax error with setProperties

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.3  2007/01/05 19:30:37  newtona
removed any dependencies on cnet libraries; now only depends on mootools.

Revision 1.2  2006/11/02 21:26:42  newtona
checking in commerce release version of global framework.

notable changes here:
cnet.functions.js is the only file really modified, the rest are just getting cvs footers (again).

cnet.functions adds numerous new classes:

$type.isNumber
$type.isSet
$set

*/