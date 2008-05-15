/*	Script: element.shortcuts.js
Extends the <Element> object with some basic shortcuts (like .hide and .show).

Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
	
Class: Element
		This extends the <Element> prototype.
	*/
Element.extend({

/*	Property: isVisible
		Returns a boolean; true = visible, false = not visible.
		
		Example:
		>$(id).isVisible()
		> > true | false	*/
	isVisible: function() {
		return this.getStyle('display') != 'none';
	},
/*	Property: toggle
		Toggles the state of an element from hidden (display = none) to 
		visible (display = what it was previously or else display = block)
		
		Example:
		> $(id).toggle()
	*/
	toggle: function() {
		return this[this.isVisible() ? 'hide' : 'show']();
	},
/*	Property: hide
		Hides an element (display = none)
		
		Example:
		> $(id).hide()
		*/
	hide: function() {
		this.originalDisplay = this.getStyle('display'); 
		this.setStyle('display','none');
		return this;
	},
/*	Property: show
		Shows an element (display = what it was previously or else display = block)
		
		Example:
		>$(id).show() */
	show: function(display) {
		this.originalDisplay = (this.originalDisplay=="none")?'block':this.originalDisplay;
		this.setStyle('display',(display || this.originalDisplay || 'block'));
		return this;
	},
/*	Property: tidy
		Uses <String.tidy> to clean up common special characters with their ASCII counterparts (smart quotes, elipse characters, stuff from MS Word, etc.).
	*/
	tidy: function(){
		try {	
			if(this.getValue().tidy())this.value = this.getValue().tidy();
		}catch(e){dbug.log('element.tidy error: %o', e);}
	},
/*	Property: findParent
		Returns the first element that contains	this element within a collection.
		
		Arguments:
		collection - (string or array) Either a css selector ("div.className") or a collection ($$(elements)) to inspect; the first element in the collection that contains this element is returned.
		*/
	findParent: function(collection){
		return $$(collection).filter(function(el){
			return el.hasChild(this);
		}, this)[0];
	},
	//DO NOT USE THIS METHOD
	//it is temporary, as Mootools 1.1 will negate its requirement
	fxOpacityOk: function(){
		if (!window.ie6)return true;
		var isColor = false;
		try {
			if (new Color(this.getStyle('backgroundColor'))) isColor = true;
		}catch(e){}
		return isColor;
	}
});
//legacy namespace
Element.visible = Element.isVisible;


if(!Element.empty) {
	Element.extend({
		/*
		Property: empty
			Empties an element of all its children (overridden by Mootools 1.1 <Element.empty> if present).
	
		Example:
			>$('myDiv').empty() // empties the Div and returns it
	
		Returns:
			The element.
		*/
		empty: function(){
			//Garbage.trash(this.getElementsByTagName('*'));
			return this.setHTML('');
		}
	});
}
/*	legacy support for $S	*/
var $S = $$;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/element.shortcuts.js,v $
$Log: element.shortcuts.js,v $
Revision 1.5  2007/10/15 18:21:56  newtona
element.findParent was buggy; fixed.

Revision 1.4  2007/09/17 22:06:08  newtona
added Element.getParent(selector) method.

Revision 1.3  2007/05/30 20:32:33  newtona
doc updates

Revision 1.2  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.1  2007/05/29 21:25:31  newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories

Revision 1.29  2007/05/17 19:45:57  newtona
updated element.empty for mootools 1.1

Revision 1.28  2007/05/16 20:09:42  newtona
adding new js files to redball.common.full
product.picker.js now has no picklets; these are in the implementations/picklets directory
ProductPicker now detects if there is no doctyp and, if not, sets the position of the picker to be fixed (no IE6 support)
small docs update in element.cnet.js
added new picklet: CNETProductPicker_PricePath
added new picklet: NewsStoryPicker_Path
new file: clipboard.js (allows you to insert text into the OS clipboard)
new file: html.table.js (automates building html tables)
new file: element.forms.js (for managing text inputs - get selected text information, insert content around selection, etc.)

Revision 1.27  2007/05/05 01:01:26  newtona
stickywinHTML: tweaked the options for buttons
element.cnet: tweaked smoothshow/hide css handling

Revision 1.26  2007/04/09 21:35:22  newtona
removing garbage collection from Element.empty (will have to wait for Mootools 1.1)

Revision 1.25  2007/04/02 18:04:48  newtona
syntax fix

Revision 1.24  2007/03/30 21:42:43  newtona
adding Element.isEmpty

Revision 1.23  2007/03/30 19:27:58  newtona
moved .empty to .flush

Revision 1.22  2007/03/29 22:36:42  newtona
Element.fxOpacityOk now only checks bgcolor for ie6
Added Element.flush

Revision 1.21  2007/03/28 23:22:54  newtona
Element.smoothShow/smoothHide: added Element.fxOpacityOk to deal with the IE bug where text gets blurry when you fade an element in and out without a bgcolor set

Revision 1.20  2007/03/28 18:07:21  newtona
added Element.fxOpacityOk to deal with the IE bug where text gets blurry when you fade an element in and out without a bgcolor set

Revision 1.19  2007/03/26 18:30:12  newtona
iframeShim: fixed reference to options (should be this.options)
element.cnet: removed some dbug lines

Revision 1.18  2007/03/23 20:13:38  newtona
getDimensions: added support for getComputedSize
getComputedSize: added
setPosition: added edge option, uses getComputedSize
smoothHide: uses getComputedSize
smoothShow: uses getComputedSize
sumObj: removed function; no longer needed

Revision 1.17  2007/03/16 00:23:24  newtona
added string.tidy and element.tidy

Revision 1.16  2007/03/08 23:32:14  newtona
strict javascript warnings cleaned up

Revision 1.15  2007/03/01 00:50:35  newtona
type.isNumber now returns false for NaN
element.smoothshow/hide now works (in IE specifically) when there are no values for border

Revision 1.14  2007/02/27 19:37:56  newtona
element.show now enforces that the original display was not 'none'

Revision 1.13  2007/02/22 21:05:35  newtona
smoothHide now checks that the element is not already hidden

Revision 1.12  2007/02/21 00:21:22  newtona
added legacy support for $S

Revision 1.11  2007/02/08 22:14:04  newtona
added border widths to smoothshow/hide

Revision 1.10  2007/02/08 01:30:58  newtona
tweaking element.setPosition, now can use effects

Revision 1.9  2007/02/07 20:52:34  newtona
added Element.position

Revision 1.8  2007/02/06 18:13:13  newtona
added element.smoothShow and smoothHide; depends on latest svn of mootools

Revision 1.7  2007/02/03 01:40:05  newtona
fixed a typo bug

Revision 1.6  2007/01/26 06:06:13  newtona
element.replace now takes a 2nd argument to eval scripts or not
element.getDimensions now returns w & h for hidden elements

Revision 1.5  2007/01/05 19:45:48  newtona
made getDimensions capable of discovering dimensions of hidden elements

Revision 1.4  2006/12/06 20:14:59  newtona
carousel - improved performance, changed some syntax, actually deployed into usage and tested
cnet.nav.accordion - improved css selectors for time
multiple accordion - fixed a typo
dbug.js - added load timers
element.cnet.js - changed syntax to utilize mootools more effectively
function.cnet.js - equated $set to $pick in preparation for mootools v1

Revision 1.3  2006/11/27 17:59:32  newtona
small change to replace and the way it uses timeouts

Revision 1.2  2006/11/02 21:34:00  newtona
Added cvs footer


*/
