/*	Script: element.forms.js
		Handles numerous element functions for editing text.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools - <Moo.js>, <Utilities.js>, <Common.js>, <Array.js>, <String.js>, <Element.js>, <Function.js>
	*/

Element.extend({

/*	Property: getTextInRange
		Returns the text of an input within a range.
		
		Arguments:
		start - beginning select position
		end - end position
	*/

	getTextInRange: function(start, end) {
		return this.getValue().substring(start, end);
	},

/*	Property: getSelectedText
		Get the text selected in an input, returns a range (see <Element.getTextInRange>).
	*/
	getSelectedText: function() {		
		if(window.ie) return document.selection.createRange().text;
		return this.getValue().substring(this.getSelectionStart(), this.getSelectionEnd());
	},

/*	Property: getSelectionStart
		Returns the index of start of the selected text.
	*/

	getSelectionStart: function() {
		if(window.ie) {
			this.focus();
			var range = document.selection.createRange();
			var tmp = range.duplicate();
			tmp.moveToElementText(this);
			tmp.setEndPoint('EndToEnd', range);
			return tmp.text.length - range.text.length;
		}
		return this.selectionStart;
	},

/*	Property: getSelectionEnd
		Returns the index of end of the selected text.
	*/

	getSelectionEnd: function() {
		if(window.ie) {
			this.focus();
			var range = document.selection.createRange();
			var tmp = range.duplicate();
			tmp.moveToElementText(this);
			tmp.setEndPoint('EndToEnd', range);
			return this.getSelectionStart() + range.text.length;
		}
		return this.selectionEnd;
	},


/*	Property: getSelectedRange
		Gets the range of what is selected within the element.
		
		Returns:
		Object with start and end properties.
		
		Example:
		>{start: 2, end: 12} */

	getSelectedRange: function() {
		return {
			start: this.getSelectionStart(),
			end: this.getSelectionEnd()
		}
	},
	
/*	Property: setCaretPosition
		Sets the caret at the given position.
		
		Arguments:
		pos - (integer) the location to place the caret OR "end" to place it at the end.
	*/

	setCaretPosition: function(pos) {
		if(pos == 'end') pos = this.getValue().length;
		this.selectRange(pos, pos);
		return this;
	},

/*	Property: getCaretPosition
		Returns the caret position (integer). */

	getCaretPosition: function() {
		return this.getSelectedRange().start;
	},
	
/*	Property: selectRange
		Selects text within a given range.
		
		Arguments:
		start - (integer) starting integer
		end - (integer) ending integer
		
		Examples:
(start code)
<input id="test" value="012345">
<script>
$('test').selectRange(2,4); //selects "23"
</script>
(end)
	*/

	selectRange: function(start, end) {
		this.focus();
		if(window.ie) {
			var range = this.createTextRange();
			range.collapse(true);
			range.moveStart('character', start);
			range.moveEnd('character', end - start);
			range.select();
			return this;
		}
		this.setSelectionRange(start, end);
		return this;
	},

/*	Property: insertAtCursor
		Inserts a value at the cursor location; if text is selected, it replaces this text.
		
		Arguments:
		value - (string) value to insert.
		selectText - (boolean) selects the text after it's been inserted
	*/

	insertAtCursor: function(value, select) {
		var start = this.getSelectionStart();
		var end = this.getSelectionEnd();
		this.value = this.getValue().substring(0, start) + value + this.getValue().substring(end, this.getValue().length);
 		if($pick(select, true)) this.selectRange(start, start + value.length);
		else this.setCaretPosition(start + value.length);
		return this;
	},
 
/*	Property: insertAroundCursor
		Inserts values around selected text (think HTML).
		
		Arguments:
		options - (object) key/value set of options.
		
		Options:
		before - (string) the prefix to insert before the selected text
		after - (string) the suffix to insert after the selected text
		defaultMiddle - (string) value to insert between the prefix and the suffix if no text was selected (defaults to "SOMETHING HERE")
	*/

	insertAroundCursor: function(options, select) {
		options = $merge({
			before: '',
			defaultMiddle: 'SOMETHING HERE',
			after: ''
		}, options);
		value = this.getSelectedText() || options.defaultMiddle;
		var start = this.getSelectionStart();
		var end = this.getSelectionEnd();
		if(start == end) {
			var text = this.getValue();
			this.value = text.substring(0, start) + options.before + value + options.after + text.substring(end, text.length);
			this.selectRange(start + options.before.length, end + options.before.length + value.length);
			text = null;
		} else {
			text = this.getValue().substring(start, end);
			this.value = this.getValue().substring(0, start) + options.before + text + options.after + this.getValue().substring(end, this.getValue().length);
			var selStart = start + options.before.length;
			if($pick(select, true)) this.selectRange(selStart, selStart + text.length);
			else this.setCaretPosition(selStart + text.length);
		}	
		return this;
	}
});

/* do not edit below this line */	 
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/element.forms.js,v $
$Log: element.forms.js,v $
Revision 1.6  2007/08/28 23:16:09  newtona
IconMenu now handles deleteing items off screen more effectively
reverted some logic in element.forms; the new stuff was a little buggy

Revision 1.5  2007/08/02 18:38:30  newtona
fixed a bug in element.forms for IE

Revision 1.4  2007/05/30 20:32:33  newtona
doc updates

Revision 1.3  2007/05/29 23:06:47  newtona
fixed a few returns in element.form.js

Revision 1.2  2007/05/29 22:58:29  newtona
fixed a bug in reference to range as an array (which is no longer the case)

Revision 1.1  2007/05/29 21:25:31  newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories

Revision 1.3  2007/05/16 22:20:18  newtona
fixded a bug with insertAround

Revision 1.2  2007/05/16 21:39:41  newtona
added missing ;

Revision 1.1  2007/05/16 20:09:42  newtona
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