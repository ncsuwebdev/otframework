/*	Script: element.legacy.js
		Extends <Element> for backwards compatibility with Prototype.js. See <prototype.compatability.js>.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools - <Moo.js>, <Utilities.js>, <Common.js>, <Array.js>, <String.js>, <Element.js>, <Function.js>
	*/
Element.extend({
/*	Property: cleanWhitespace
		Removes all empty text nodes from an element and its children
		
		Example:
		> $(id).cleanWhitespace()	*/
	cleanWhitespace: function() {
		$A(this.childNodes).each(function(node){
			if (node.nodeType == 3 && !/\S/.test(node.nodeValue)) node.parentNode.removeChild(node);
		});
		return this;
	},
/*	Property: find
		Returns an element from the node's array (such as parentNode), deprecated (left over from Prototype.lite).
		
		Arguments:
		what - the value you wish to find (such as 'parentNode')

		Example:
		> $(id).find(parentNode)
	*/
	find: function(what) {
		var element = this[what];
		while (element.nodeType != 1) element = element[what];
		return element;
	},
/*	Property: replace
		Replaces an html element with the html passed in.
		
		Arguments:
		html - the html with which to replace the node.
		evalScripts - (boolean; optional) evaluate javascript in the new node. defaults to true.
		
		Example:
		>$(id).replace(myHTML) */
	replace: function(html, evalScripts) {
		if (this.outerHTML) {
			this.outerHTML = html.stripScripts();
		} else {
			var range = this.ownerDocument.createRange();
			range.selectNodeContents(this);
			this.parentNode.replaceChild(
				range.createContextualFragment(html.stripScripts()), this);
		}
		if($pick(evalScripts, true)) html.evalScripts.delay(10, html);
	},
/*	Property: isEmpty
		Returns a boolean: true = the Node is empty, false, it isn't.
		
		Example:
		> $(id).empty
		> true (the node is empty) | false (the node is not empty)
	*/
	isEmpty: function() {
		return !!this.innerHTML.match(/^\s*$/);
	},
	/*	Property: getOffsetHeight
			Returns the offset height of an element, deprecated.
			You should instead use <Element.getStyle>('height')
			or just Element.offsetHeight.
			
			Example:
			> $(id).getOffsetHeight()
		*/
	getOffsetHeight: function(){ return this.offsetWidth; },
	/*	Property: getOffsetWidth
			Returns the offset width of an element, deprecated.
			You should instead use <Element.getStyle>('width')
			or just Element.offsetWidth.
			
			Example:
			> $(id).getOffsetWidth()
		*/
	getOffsetWidth: function(){ return this.offsetWidth; }
});	





/*	Mootools 1.11 extensions for compatability support	*/
if(!Element.setText) {
	Element.extend({
		setText: function(text){
			var tag = this.getTag();
			if (['style', 'script'].test(tag)){
				if (window.ie){
					if (tag == 'style') this.styleSheet.cssText = text;
					else if (tag ==  'script') this.setProperty('text', text);
					return this;
				} else {
					if (this.firstChild) this.removeChild(this.firstChild);
					return this.appendText(text);
				}
			}
			this[$type(this.innerText) ? 'innerText' : 'textContent'] = text;
			return this;
		}
	});
}



/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/element.legacy.js,v $
$Log: element.legacy.js,v $
Revision 1.2  2007/07/16 21:00:22  newtona
using Element.setText for all style injection methods (fixes IE6 problems)
moving Element.setText to element.legacy.js; this function is in Mootools 1.11, but if your environment is running 1.0 you'll need this.

Revision 1.1  2007/05/29 21:25:31  newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories


*/
