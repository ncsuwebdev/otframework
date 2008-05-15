/*	Script: stickyWinFx.Drag.js
		Implements drag and resize functionaity into <StickyWinFx>. See <StickyWinFx> for the options.
		
		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)

		Dependencies:
		<stickyWin.js>, <stickyWinFx.js>, and all their dependencies plus <Drag.Base.js>.
*/
if(typeof Drag != "undefined"){
	StickyWinFx.implement({
		makeDraggable: function(){
			var toggled = this.toggleVisible(true);
			if(this.options.useIframeShim) {
				this.makeIframeShim();
				var dragComplete = this.options.dragOptions.onComplete || Class.empty;
				this.options.dragOptions.onComplete = function(){
					dragComplete();
					this.shim.position();
				}.bind(this);
			}
			if(this.options.dragHandleSelector) {
				var handle = this.win.getElement(this.options.dragHandleSelector);
				if (handle) {
					handle.setStyle('cursor','move');
					this.options.dragOptions.handle = handle;
				}
			}
			this.win.makeDraggable(this.options.dragOptions);
			if (toggled) this.toggleVisible(false);
		}, 
		makeResizable: function(){
			var toggled = this.toggleVisible(true);
			if(this.options.useIframeShim) {
				this.makeIframeShim();
				var resizeComplete = this.options.resizeOptions.onComplete || Class.empty;
				this.options.resizeOptions.onComplete = function(){
					resizeComplete();
					this.shim.position();
				}.bind(this);
			}
			if(this.options.resizeHandleSelector) {
				var handle = this.win.getElement(this.options.resizeHandleSelector);
				if(handle) this.options.resizeOptions.handle = this.win.getElement(this.options.resizeHandleSelector);
			}
			this.win.makeResizable(this.options.resizeOptions);
			if (toggled) this.toggleVisible(false);
		},
		toggleVisible: function(show){
			if(!this.visible && window.khtml && $pick(show, true)) {
				this.win.setStyles({
					display: 'block',
					opacity: 0
				});
				return true;
			} else if(!$pick(show, false)){
				this.win.setStyles({
					display: 'none',
					opacity: 1
				});
				return false;
			}
			return false;
		}
	});
}
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/stickyWinFx.Drag.js,v $
$Log: stickyWinFx.Drag.js,v $
Revision 1.5  2007/03/29 23:12:00  newtona
confirmer now checks for a bg color in IE6 to use crossfading (see Element.fxOpacityOk)
fixed an IE7 css layout issue in stickyDefaultHTML
StickyWin now uses Element.flush
StickyWinFx.Drag now temporarily shows the sticky win (with opacity 0) to execute makeDraggable and makeResizable to prevent a Safari bug

Revision 1.4  2007/02/21 00:26:33  newtona
drag handle now gets cursor: move

Revision 1.3  2007/01/26 05:49:42  newtona
added docs

Revision 1.2  2007/01/22 22:00:15  newtona
numerous bug fixes to modalizer, stickywin, and popupdetails
updated for mootools 1.0
fixed date validation in form.validator

Revision 1.1  2007/01/11 20:55:23  newtona
changed the way options are set, split up stickywin into 4 files, refactored popupdetails to use stickywin and modalizer


*/
