/*	Script: stickyWin.js
		Creates a div within the page with the specified contents at the location relative to the element you specify; basically an in-page popup maker.

		Dependencies:
		Moo - <Moo.js>, <Common.js>, <Utility.js>, <Element.js>, <Function.js>, <Dom.js>, <Array.js>, <Window.Base.js>, <Window.Size.js>, <Events.js>
		CNET - <element.shortcuts.js>, <element.dimensions.js>, <element.position.js>, <element.pin.js>, <IframeShim.js>
		
		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)
		
		Class: StickyWin
		Creates a div within the page with the specified contents at the location relative to the element you specify; basically an in-page popup maker.

		Arguments:
		options - an object with key/value options
		
		Options:
			onDisplay - function to execute when the popup is shown
			onClose - function to execute when the popup is closed
			closeClassName - class name of the element(s) in your popup content that, 
					when clicked, should close the window; defaults to "closeSticky"
			pinClassName - class name of the elements(s) in your popup content that,
					when clicked, should pin the sticky in place; defaults to "pinSticky"
			content - the content of your popup; this should be layout html and your message or a dom element
			zIndex - the zIndex of the popup; defaults to 10000
			id - the id of the wrapper div that gets created that will contain your content; 
					defaults to 'StickyWin' + the date (so it's unique)
			className - optional class name for the wrapper dive that gets created that will
					contain your content
			position - "center", "upperRight", "bottomRight", "upperLeft", "bottomLeft"; the point in the popup that is positioned;
					defaults to 'center'
			edge - same options as position (center, upperRight, etc.) but specifies the edge of the stickyWin to position
				to the point specified in position. see <Element.setPosition> for details. Optional; defaults to the
				<Element.setPosition> default state.
			offset - object containing {x: # and y: #} (integers) the top and left offset from the element in the 
					page that the popup is relative to; this offset is applied to the center of the popup 
					or the corner, depending on  the value you specify in the 'position' option.
			relativeTo - a dom element to position the popup relative to; defaults to document.body (i.e. the window)
			width - an optional width for the wrapper div for your popup
			height - an optional height for the wrapper div for your popup
			timeout - (integer) an optional timeout interval to hide the popup after a specified time
			allowMultiple - (boolean) allow multiple instance of StickyWin on the page; defaults to true
			allowMultipleByClass - (boolean) allow multiple popups that share the same className as specified in 
				the className option; defaults to false
			showNow - display the popup on instantiation; defaults to true
			useIframeShim - use an <IframeShim> to mask content below the element; defaults to true.
			iframeShimSelector - the css selector to find the element within your popup under which 
				the iframe shim should be placed to obscure select lists and the like (see <IframeShim>)
			
	Example:
(start code)
var myWin = new StickyWin({
	content: '<div id="myWin">hi there!<br><a href="javascript:void(0);" class="closeSticky">close</a></div>'
});
//popups up a box in the middle of the window with "hi there" and a close link(end)
	*/
var StickyWin = new Class({
	options: {
		onDisplay: Class.empty,
		onClose: Class.empty,
		closeClassName: 'closeSticky',
		pinClassName: 'pinSticky',
		content: '',
		zIndex: 10000,
		className: '',
		//id: ... set above in initialize function
		edge: false, //see Element.setPosition in element.cnet.js
		position: 'center', //center, corner == upperLeft, upperRight, bottomLeft, bottomRight
		offset: {x:0,y:0},
		relativeTo: document.body, 
		width: false,
		height: false,
		timeout: -1,
		allowMultipleByClass: false,
		allowMultiple: true,
		showNow: true,
		useIframeShim: true,
		iframeShimSelector: ''
	},
	css: '.SWclearfix:after {content: "."; display: block; height: 0; clear: both; visibility: hidden;}'+
			 '.SWclearfix {display: inline-table;}'+
			 '* html .SWclearfix {height: 1%;}'+
			 '.SWclearfix {display: block;}',
	initialize: function(options){
		this.setOptions(options);
		this.id = this.options.id || 'StickyWin_'+new Date().getTime();
		this.makeWindow();
		if(this.options.content) this.setContent(this.options.content);
		if(this.options.showNow) this.show();
		//add css for clearfix
		window.addEvent('domready', function(){
			try {
				if(!$('StickyWinClearfix')) {
					var style = new Element('style').setProperty('id','StickyWinClearfix').injectInside($$('head')[0]);
					if (!style.setText.attempt(this.css, style)) style.appendText(this.css);
				}
			}catch(e){dbug.log('error: %s',e);}
		}.bind(this));
	},
	makeWindow: function(){
		this.destroyOthers();
		if(!$(this.id)) {
			this.win = new Element('div').setProperty('id',			this.id).addClass(this.options.className).addClass('StickyWinInstance').addClass('SWclearfix').setStyles({
				 	'display':'none',
					'position':'absolute',
					'zIndex':this.options.zIndex
				}).injectInside(document.body);
		} else this.win = $(this.id);
		if(this.options.width && $type(this.options.width.toInt())=="number") this.win.setStyle('width', this.options.width.toInt() + 'px');
		if(this.options.height && $type(this.options.height.toInt())=="number") this.win.setStyle('height', this.options.height.toInt() + 'px');
		return this;
	},
/*	Property: show
		Shows the popup.
	*/
	show: function(){
		this.fireEvent('onDisplay');
		if(!this.positioned) this.position();
		this.showWin();
		if(this.options.useIframeShim) this.showIframeShim();
		this.visible = true;
		return this;
	},
	showWin: function(){
		this.win.setStyle('display','block');
	},
/*	Property: hide
		Hides the popup.
	*/
	hide: function(){
		this.fireEvent('onClose');
		this.hideWin();
		if(this.options.useIframeShim) this.hideIframeShim();
		this.visible = false;
		return this;
	},
	hideWin: function(){
		this.win.setStyle('display','none');
	},
	destroyOthers: function() {
		if(!this.options.allowMultipleByClass || !this.options.allowMultiple) {
			$$('div.StickyWinInstance').each(function(sw) {
				if(!this.options.allowMultiple || (!this.options.allowMultipleByClass && sw.hasClass(this.options.className))) 
					sw.remove();
			}, this);
		}
	},
/*	Property: setContent
		Replaces the content of the popup with the content passed in.
		
		Arguments:
		html - the new content
	*/
	setContent: function(html) {
		if(this.win.getChildren().length>0) this.win.empty();
		if($type(html) == "string") this.win.setHTML(html);
		else if ($(html)) this.win.adopt(html);
		this.win.getElements('.'+this.options.closeClassName).each(function(el){
			el.addEvent('click', this.hide.bind(this));
		}, this);
		this.win.getElements('.'+this.options.pinClassName).each(function(el){
			el.addEvent('click', this.togglepin.bind(this));
		}, this);
		return this;
	},
	
	position: function(){
		this.positioned = true;
		this.win.setPosition({
			relativeTo: this.options.relativeTo,
			position: this.options.position,
			offset: this.options.offset,
			edge: this.options.edge
		});
		if(this.shim) this.shim.position();
		return this;
	},
/*	Property: pin
		Affixes the stickywin to a fixed position, even if the window is scrolled. See <Element.pin>.
	*/
	pin: function(pin) {
		if(!this.win.pin) {
			dbug.log('you must include element.pin.js!');
			return false;
		}
		this.pinned = $pick(pin, true);
		return this.win.pin(pin);
	},
/*	Property: unpin
		Affixes the stickywin to a fixed position, even if the window is scrolled. See <Element.unpin>.
	*/
	unpin: function(){
		this.pin(false);
	},
/*	Property: togglepin
		Toggle the pinned state of the Sticky;
	*/
	togglepin: function(){
		this.pin(!this.pinned);
	},
	makeIframeShim: function(){
		if(!this.shim){
			this.shim = new IframeShim({
				element: (this.options.iframeShimSelector)?this.win.getElement(this.options.iframeShimSelector) : $('StickyWinOverlay') || this.win,
				display: false,
				name: 'StickyWinShim'
			});
		}
	},
	showIframeShim: function(){
		if(this.options.useIframeShim) {
			this.makeIframeShim();
			this.shim.show();
		}
	},
	hideIframeShim: function(){
		if(this.options.useIframeShim)
			this.shim.hide();
	},
/*	Property: destroy
		Removes the popup element.
	*/
	destroy: function(){
		this.win.remove();
		if(this.options.useIframeShim) this.shim.remove();
		if($('StickyWinOverlay'))$('StickyWinOverlay').remove();
	}
});
StickyWin.implement(new Options);
StickyWin.implement(new Events);

var stickyWin = StickyWin;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/stickyWin.js,v $
$Log: stickyWin.js,v $
Revision 1.27  2007/11/29 19:00:57  newtona
- fixed an issue with StickyWin that could create inheritance issues because of a defect in $merge. this fixed an actual issue with DatePicker, specifically, having more than one on a page
- fixed an issue with the basehref in simple.error.popup

Revision 1.26  2007/07/18 16:15:21  newtona
forgot to bind the style objects in the setText.attempt method...

Revision 1.25  2007/07/16 21:00:21  newtona
using Element.setText for all style injection methods (fixes IE6 problems)
moving Element.setText to element.legacy.js; this function is in Mootools 1.11, but if your environment is running 1.0 you'll need this.

Revision 1.24  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.23  2007/05/11 00:10:48  newtona
syntax fix

Revision 1.22  2007/05/11 00:05:29  newtona
adding clearfix css to all sticky win instances

Revision 1.21  2007/05/04 01:22:47  newtona
added togglepin

Revision 1.19  2007/05/04 00:32:39  newtona
stickwinHTML: added the ability for buttons to not close the sticky (className option)
stickyWin: added .pin (see Element.pin.js)

Revision 1.18  2007/04/13 19:06:11  newtona
dependency update in the docs

Revision 1.17  2007/03/30 19:32:20  newtona
changing .flush to .empty

Revision 1.16  2007/03/29 23:12:00  newtona
confirmer now checks for a bg color in IE6 to use crossfading (see Element.fxOpacityOk)
fixed an IE7 css layout issue in stickyDefaultHTML
StickyWin now uses Element.flush
StickyWinFx.Drag now temporarily shows the sticky win (with opacity 0) to execute makeDraggable and makeResizable to prevent a Safari bug

Revision 1.15  2007/03/23 23:01:16  newtona
stickywin: implemented current options options method (no functional change)
stickywinFx: removed an old function that was empty; should have been gone a long time ago (getDefaultOptions)

Revision 1.14  2007/03/23 20:19:48  newtona
Iframeshim: added className; updated docs
StickyWin: added edge support (see Element.setPosition)

Revision 1.13  2007/03/08 00:06:13  newtona
stickyWin now empties it's content before setContent adds new stuff

Revision 1.12  2007/02/21 00:24:56  newtona
no change, but cvs says it's different. maybe a space?

Revision 1.11  2007/02/08 22:54:00  newtona
removed a comment

Revision 1.10  2007/02/08 20:50:54  newtona
fixed a bug where .show repositioned the window every time

Revision 1.9  2007/02/08 01:29:51  newtona
fixed syntax error

Revision 1.8  2007/02/07 20:51:41  newtona
implemented Options class
implemented Events class
StickyWin now uses Element.position

Revision 1.7  2007/01/26 18:24:41  newtona
docs update

Revision 1.6  2007/01/26 05:49:10  newtona
syntax update for mootools 1.0

Revision 1.5  2007/01/23 20:54:24  newtona
a little better position handling

Revision 1.4  2007/01/22 22:00:15  newtona
numerous bug fixes to modalizer, stickywin, and popupdetails
updated for mootools 1.0
fixed date validation in form.validator

Revision 1.3  2007/01/19 01:22:32  newtona
fixed a few syntax errors

Revision 1.2  2007/01/11 20:55:23  newtona
changed the way options are set, split up stickywin into 4 files, refactored popupdetails to use stickywin and modalizer

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.2  2007/01/09 01:26:04  newtona
docs syntax fix

Revision 1.1  2007/01/05 19:29:30  newtona
first check in


*/

