/*	Script: modalizer.js
		Provides functionality to overlay the window contents with a semi-transparent layer that prevents interaction with page content until it is removed.
		
		Dependencies:
		Mootools - <Moo.js>, <Array.js>, <String.js>, <Function.js>, <Utility.js>, <Dom.js>, <Element.js>, <Window.Size.js>, <Event.js>, <Window.Base.js>
		
		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)

		Class: Modalizer
		Provides functionality to overlay the window contents with a semi-transparent layer that prevents interaction with page content until it is removed. This class is intended to be implemented into other classes to provide them access to this functionality.
	*/
var Modalizer = new Class({
	defaultModalStyle: {
		display:'block',
		position:'fixed',
		top:'0px',
		left:'0px',	
		'z-index':5000,
		'background-color':'#333',
		opacity:0.8
	},
/*	Property: setOptions
		Sets the options for the modal overlay.
		
		Arguments:
		options - an object with name/value definitions
		
		See <modalShow> for options list.
	*/
	setModalOptions: function(options){
		this.modalOptions = $merge({
			width:(window.getScrollWidth()+300)+'px',
			height:(window.getScrollHeight()+300)+'px',
			elementsToHide: 'select',
			onModalHide: Class.empty,
			onModalShow: Class.empty,
			hideOnClick: true,
			modalStyle: {},
			updateOnResize: true
		}, this.modalOptions, options || {});
	},
	resize: function(){
		if($('modalOverlay')) {
			$('modalOverlay').setStyles({
				width:(window.getScrollWidth()+300)+'px',
				height:(window.getScrollHeight()+300)+'px'
			});
		}
	},
/*	Property: setModalStyle
		Sets the style of the modal overlay to those in the object passed in.
		
		Arguments:
		styleObject - object with key/value css properties
		
		Default styleObject:
(start code){
	'display':'block',
	'position':'fixed',
	'top':'0px',
	'left':'0px',	
	'width':'100%',
	'height':'100%',
	'z-index':this.modalOptions.zIndex,
	'background-color':this.modalOptions.color,
	'opacity':this.modalOptions.opacity
}(end)

	The object you pass in can contain any portion of this object, and the options you specify will overwrite the defaults; any option you do not specify will remain.		
	*/
	setModalStyle: function (styleObject){
		this.modalOptions.modalStyle = styleObject;
		this.modalStyle = $merge(this.defaultModalStyle, {
			width:this.modalOptions.width,
			height:this.modalOptions.height
		}, styleObject);
		if($('modalOverlay'))$('modalOverlay').setStyles(this.modalStyle);
		return(this.modalStyle);
	},
/*	Property: modalShow
		Shows the modal window.
		
		Arguments:
		options - key/value options object
		
		Options:
		elementsToHide - comma seperated string of selectors to hide when the overlay is applied;
			example: 'select, input, img.someClass'; defaults to 'select'
		modalHide - the funciton that hides the modal window; defaults to 
			"function(){if($('modalOverlay'))$('modalOverlay').hide();}"
		modalShow - the function that shows the modal window; defaults to
			"function(){$('modalOverlay').setStyle('display','block');}"
		onModalHide - function to execute when the modal window is removed
		onModalShow - function to execute when the modal window appears
		hideOnClick - allow the user to click anywhere on the modal layer to close it; defaults to true.
		modalStyle - a css style object to apply to the modal overlay. See <setModalStyle>.
		updateOnResize - (boolean) will update the size of the modal layer to fit the window if the user resizes; defaults to true.
	*/
	modalShow: function(options){
		this.setModalOptions(options||{});
		var overlay = null;
		if($('modalOverlay')) overlay = $('modalOverlay');
		if(!overlay) overlay = new Element('div').setProperty('id','modalOverlay').injectInside(document.body);
		overlay.setStyles(this.setModalStyle(this.modalOptions.modalStyle));
		if(window.ie6) overlay.setStyle('position','absolute');
		$('modalOverlay').removeEvents('click').addEvent('click', function(){
			this.modalHide(this.modalOptions.hideOnClick);
		}.bind(this));
		this.bound = this.bound||{};
		if(!this.bound.resize && this.modalOptions.updateOnResize) {
			this.bound.resize = this.resize.bind(this);
			window.addEvent('resize', this.bound.resize);
		}
		this.modalOptions.onModalShow();
		this.togglePopThroughElements(0);
		overlay.setStyle('display','block');
		return this;
	},
/*	Property: modalHide
		Hides the modal layer.
	*/
	modalHide: function(override){
		if(override === false) return false; //this is internal, you don't need to pass in an argument
		this.togglePopThroughElements(1);
		this.modalOptions.onModalHide();
		if($('modalOverlay'))$('modalOverlay').setStyle('display','none');
		if(this.modalOptions.updateOnResize) {
			this.bound = this.bound||{};
			if(!this.bound.resize) this.bound.resize = this.resize.bind(this);
			window.removeEvent('resize', this.bound.resize);
		}

		return this;
	},
	togglePopThroughElements: function(opacity){
		if((window.ie6 || (window.gecko && navigator.userAgent.test('mac', 'i')))) {
			$$(this.modalOptions.elementsToHide).each(function(sel){
				sel.setStyle('opacity', opacity);
			});
		}
	}
});
//legacy namespace
var modalizer = Modalizer;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/modalizer.js,v $
$Log: modalizer.js,v $
Revision 1.21  2007/09/17 17:27:41  newtona
fixed a bug in modalizer; reference to this.bound.resize wasn't yet defined in certain circumstances.

Revision 1.20  2007/09/13 22:19:44  newtona
modalizer now fixes it's overlay if hte window is resized
mooscroller refactors and faster; no more dependencies on Drag or Scroller
window.cnet - updating the query string method and returning a consistent value (an object)

Revision 1.19  2007/09/05 18:37:03  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.18  2007/08/30 17:09:27  newtona
stickyWinFx, modalizer: updated syntax a litle
jlogger: updated docs
fixed a bug in Fx.Sort w/ IE6

Revision 1.17  2007/06/29 21:54:27  newtona
fixed a bug with the hideOnClick option

Revision 1.16  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.15  2007/05/03 18:24:24  newtona
iframeshim: removed a dbug line
modalizer: only hide select lists for browsers that need it
product picker: added a try/catch, updated cnet api link/code

Revision 1.14  2007/04/13 19:06:11  newtona
dependency update in the docs

Revision 1.13  2007/03/27 16:05:40  newtona
fixed bug where select elements were not being hidden on modal show

Revision 1.12  2007/03/20 20:59:54  newtona
fixed a problem where the modal stickywin didn't close when the modal layer was clicked.

Revision 1.11  2007/03/19 17:34:38  newtona
fixed a bug; modal overlay width/height wasn't being set.

Revision 1.10  2007/03/05 23:33:38  newtona
moved width declarations to setModalOptions function; fixed a bug in opera

Revision 1.9  2007/03/05 19:36:00  newtona
now setModalOptions is always called, even if options are not supplied (so the defaults will be used)

Revision 1.8  2007/02/27 21:46:43  newtona
docs update; fixing references

Revision 1.7  2007/02/21 00:27:28  newtona
better option handling

Revision 1.6  2007/02/07 20:51:41  newtona
implemented Options class
implemented Events class
StickyWin now uses Element.position

Revision 1.5  2007/02/06 18:11:29  newtona
refactored to re-use the overlay div because IE hogs memory for each one. god I hate IE.

Revision 1.4  2007/01/26 05:48:22  newtona
docs update

Revision 1.3  2007/01/22 22:00:15  newtona
numerous bug fixes to modalizer, stickywin, and popupdetails
updated for mootools 1.0
fixed date validation in form.validator

Revision 1.2  2007/01/11 20:55:23  newtona
changed the way options are set, split up stickywin into 4 files, refactored popupdetails to use stickywin and modalizer

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.3  2007/01/09 01:25:05  newtona
numerous improvements; ability to set individual css styles, some other tweaks

Revision 1.2  2007/01/05 18:31:51  newtona
fixed documentation syntax
added cvs footer


*/
