/*	Script: element.pin.js
		Extends the Mootools <Window> and <Element> classes to allow fixed positioning for an element.

		Dependencies:
		mootools = <Element.js>, <Window.js> and all their dependencies
	
		Class: window
		This extends the <window> class from the <http://mootools.net> library.
	*/
window.extend({
/*	Property: supportsPositionFixed
		Returns true if the browser supports fixed positioning; must be called after DomReady (or it returns null);
	*/
	supportsPositionFixed: function(){
		if(!window.loaded) return null;
		var test = new Element('div').setStyles({
			position: 'fixed',
			top: '0px',
			right: '0px'
		}).injectInside(document.body);
		var supported = (test.offsetTop === 0);
		test.remove();
		return supported;
	}
});

/*	Class: Element
		Extends the <Element> class from the <http://mootools.net> library.
	*/
Element.extend({
/*	Property: pin
		Affixes an element at its current position, even if the window is scrolled.
		
		Arguments: 
		pin - (boolean) true: pin, false: release pin. See also <Element.unpin>.
	*/
	pin: function(enable){
		var p = this.getPosition();
		if(enable!==false) {
			if(!this.pinned) {
				var pos = {
					top: (p.y - window.getScrollTop())+'px',
					left: (p.x - window.getScrollLeft())+'px'
				};
				if(window.supportsPositionFixed()) {
					this.setStyle('position','fixed').setStyles(pos);
				} else {
					this.setStyles({
						position: 'absolute',
						top: p.y+'px',
						left: p.x+'px'
					});
					window.addEvent('scroll', function(){
						if(this.pinned) {
							var to = {
								top: (pos.top.toInt() + window.getScrollTop())+'px',
								left: (pos.left.toInt() + window.getScrollLeft())+'px'
							};
							this.setStyles(to);
						}
					}.bind(this));
				}
				this.pinned = true;
			}
		} else {
			this.pinned = false;
			var reposition = (window.supportsPositionFixed())?
				{
					top: (p.y + window.getScrollTop())+'px',
					left: (p.x + window.getScrollLeft())+'px'
				}:
				{
					top: (p.y)+'px',
					left: (p.x)+'px'
				};
			this.setStyles($merge(reposition, {position: 'absolute'}));
		}
		return this;
	},
/*	Property: unpin
		Un-pins an element at its current position (see <Element.pin>).
	*/
	unpin: function(){
		return this.pin(false);
	},
/*	Property: togglepin
		Toggles the pin state of the element.
	*/
	togglepin: function(){
		this.pin(!this.pinned);
	}
});

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/element.pin.js,v $
$Log: element.pin.js,v $
Revision 1.5  2007/09/04 19:43:16  newtona
fixed issues with unpin in non IE6 browsers

Revision 1.4  2007/08/30 17:52:14  newtona
removing some errant dbug lines; added one to jsonp

Revision 1.3  2007/08/27 23:09:02  newtona
MooScroller: removed periodical for scrollbar resizing; the user can implement this if it's needed for each instance; also, renamed refactor to update
dbug: added support for dbug.dir, profile, stackTrace, etc.
element.dimensions: when getting the size of hidden elements the method now restores the previous inline styles to their original state
element.pin: fixed positioning bug

Revision 1.2  2007/05/30 20:32:33  newtona
doc updates

Revision 1.1  2007/05/29 21:25:31  newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories

Revision 1.7  2007/05/04 16:40:50  newtona
fixed a trailing comma typo

Revision 1.6  2007/05/04 01:22:38  newtona
added togglepin

Revision 1.5  2007/05/04 01:06:54  newtona
*sigh* ok, last typo

Revision 1.4  2007/05/04 01:04:35  newtona
woops, missing a "+"

Revision 1.3  2007/05/04 01:03:01  newtona
fixed a bug with unpin

Revision 1.2  2007/05/04 01:01:45  newtona
.pin only pins if the element isn't already pinned.

Revision 1.1  2007/05/04 00:36:19  newtona
*** empty log message ***


*/
