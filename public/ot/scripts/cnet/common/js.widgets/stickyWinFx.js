/*	Script: stickyWinFx.js
		Extends <StickyWin> to create popups that fade in and out and can be dragged and resized (requires <stickyWinFx.Drag.js>).
	
		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)

		Dependencies:
		mootools - <Fx.Base.js>
		cnet - <stickyWin.js> and all its dependencies.
		optional - <stickyWinFx.Drag.js> and <Drag.Base.js>

		Class: StickyWinFx
		Creates a <StickyWin> that optionally fades in and out, is draggable, and is resizable (requires <stickyWinFx.Drag.js>).
		
		Arguments:
		options - an object with key/value options
		
		Options:
		see <StickyWin>; inherits all those options in addition to the following.
		
			fade - (boolean) fade in and out; defaults to true
			fadeDuration - (integer) the duration of the fade effect; defaults to 150
			fadeTransition - an <Fx.Transitions> to use for the fade effect; defaults to <Fx.Transitions.sineInOut>
		
		Additional Options:
		These options depend on <stickyWinFx.Drag.js> and <Drag.Base.js>; so they don't do anything if those
		files are not included in your environment.
		
			draggable - (boolean) make the popup draggable, defaults to false
			dragOptions - (object) optional options to pass to the drag effect
			dragHandleSelector - optional css selector to select the element(s) within in 
				your popup to use as a drag handle.
			resizable - (boolean) make the popup resizable or not; defaults to false
			resizeOptions - (object) optional options to pass to the resize effect
			resizeHandleSelector - optional css selector to select the element(s) within in 
				your popup to use as a resize handle.
		
		Example:
(start code)
var myWin = new StickyWinFx({
	content: '<div id="myWin">hi there!<br><a href="javascript:void(0);" class="closeSticky">close</a></div>',
	fadeDuration: 500,  //slow it down
	draggable: true, //make it draggable
	dragHandleSelector: 'img.handle' //get the img with the class "handle" for the handle
});
//fades in a box in the middle of the window with "hi there" and a close link(end)
//window is draggable using the image(s) with the class "handle"
(end)		
	*/
var StickyWinFx = StickyWin.extend({
	options: {
		fade: true,
		fadeDuration: 150,
		fadeTransition: Fx.Transitions.sineInOut,
		draggable: false,
		dragOptions: {},
		dragHandleSelector: 'h1.caption',
		resizable: false,
		resizeOptions: {},
		resizeHandleSelector: ''
	},
	setContent: function(html){
		this.parent(html);
		if(this.options.draggable) this.makeDraggable();
		if(this.options.resizable) this.makeResizable();
		return this;
	},	
	hideWin: function(){
		if(this.options.fade) this.fade(0);
		else this.win.hide();
	},
	showWin: function(){
		if(this.options.fade) this.fade(1);
		else this.win.show();
	},
	fade: function(to){
		if(!this.fadeFx) {
			this.win.setStyles({
				opacity: 0,
				display: 'block'
			});
			this.fadeFx = this.win.effect('opacity', {
				duration: this.options.fadeDuration,
				transition: this.options.fadeTransition
			});
		}
		if (to > 0) this.win.setStyle('display','block');
		this.fadeFx.clearChain();
		this.fadeFx.start(to).chain(function (){
			if(to == 0) this.win.setStyle('display', 'none');
		}.bind(this));
		return this;
	},
	makeDraggable: function(){
		dbug.log('you must include Drag.js, cannot make draggable');
	},
	makeResizable: function(){
		dbug.log('you must include Drag.js, cannot make resizable');
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/stickyWinFx.js,v $
$Log: stickyWinFx.js,v $
Revision 1.15  2007/09/07 22:19:28  newtona
popupdetails: updating options handling methodology
stickyWinFx: fixed a bug where, if you were fast enough, you could introduce a flicker bug - this is hard to produce so most people probably hadn't seen it

Revision 1.14  2007/08/30 17:09:27  newtona
stickyWinFx, modalizer: updated syntax a litle
jlogger: updated docs
fixed a bug in Fx.Sort w/ IE6

Revision 1.13  2007/03/29 23:12:00  newtona
confirmer now checks for a bg color in IE6 to use crossfading (see Element.fxOpacityOk)
fixed an IE7 css layout issue in stickyDefaultHTML
StickyWin now uses Element.flush
StickyWinFx.Drag now temporarily shows the sticky win (with opacity 0) to execute makeDraggable and makeResizable to prevent a Safari bug

Revision 1.12  2007/03/23 23:01:16  newtona
stickywin: implemented current options options method (no functional change)
stickywinFx: removed an old function that was empty; should have been gone a long time ago (getDefaultOptions)

Revision 1.11  2007/03/08 02:42:15  newtona
fixed an error where the handle was being assigned before the content

Revision 1.10  2007/02/27 21:46:43  newtona
docs update; fixing references

Revision 1.9  2007/02/22 18:20:18  newtona
fixed bug where the element faded out, but wasn't set to display-none

Revision 1.8  2007/02/21 00:26:52  newtona
added a default drag handle

Revision 1.7  2007/02/08 01:30:07  newtona
fixed syntax error

Revision 1.6  2007/02/07 20:51:41  newtona
implemented Options class
implemented Events class
StickyWin now uses Element.position

Revision 1.5  2007/01/26 05:50:58  newtona
added footer cvs tags


*/
