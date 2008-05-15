/*	Script: clipboard.js
		Provides access to the OS clipboard so that data can be copied to it (using a flash plugin).
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		Original source: http://www.jeffothy.com/weblog/clipboard-copy/
		
		Dependencies:
		Mootools - <Moo.js>, <Utilities.js>, <Common.js>, <Array.js>, <String.js>, <Element.js>, <Function.js>
		CNET - (optional) <element.forms.js>
		
		Class: Clipboard
		Provides access to the OS clipboard so that data can be copied to it (using a flash plugin).
*/
var Clipboard = {
	swfLocation: 'http://www.cnet.com/html/rb/assets/global/clipboard/_clipboard.swf',
/*	Property: copyFromElement
		Copies the selected text in an element to the clipboard.
		
		Arguments:
		element - the element that has selected text.
	*/
	copyFromElement: function(element) {
		element = $(element);
		if(!element) return null;
		if (window.ie) {
			try {
				window.addEvent('domready', function() {
					var range = element.createTextRange();
					if(range) range.execCommand('Copy');
				});
			}catch(e){
				dbug.log('cannot copy to clipboard: %s', o)
			}
		} else {
			var text = (element.getSelectedText)?element.getSelectedText():element.getValue();
			if (text) Clipboard.copy(text);
		}
		return element;
	},
/*	Property: copy
		Copies a string to the clipboard.
		
		Arguments:
		text - (string) value to be copied to the clipboard.
	*/
	copy: function(text) {
		if(window.ie){
			window.addEvent('domready', function() {
				var cb = new Element('textarea', {styles: {display: 'none'}}).injectInside(document.body);
				cb.setProperty('value', text).select();
				Clipboard.copyFromElement(cb);
				cb.remove();
			});
		} else {
			var swf = ($('flashcopier'))?$('flashcopier'):new Element('div').setProperty('id', 'flashcopier').injectInside(document.body);
			swf.empty();
			swf.setHTML('<embed src="'+this.swfLocation+'" FlashVars="clipboard='+escape(text)+'" width="0" height="0" type="application/x-shockwave-flash"></embed>');
		}
	}
};
/* do not edit below this line */	 
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/clipboard.js,v $
$Log: clipboard.js,v $
Revision 1.3  2007/09/05 18:37:03  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.2  2007/05/16 21:09:26  newtona
fixed element reference in clipboard (added $())

Revision 1.1  2007/05/16 20:09:41  newtona
adding new js files to redball.common.full
product.picker.js now has no picklets; these are in the implementations/picklets directory
ProductPicker now detects if there is no doctyp and, if not, sets the position of the picker to be fixed (no IE6 support)
small docs update in element.cnet.js
added new picklet: CNETProductPicker_PricePath
added new picklet: NewsStoryPicker_Path
new file: clipboard.js (allows you to insert text into the OS clipboard)
new file: html.table.js (automates building html tables)
new file: element.forms.js (for managing text inputs - get selected text information, insert content around selection, etc.)


*/
