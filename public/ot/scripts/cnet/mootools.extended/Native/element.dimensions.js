/*	Script: element.dimensions.js
Extends the <Element> object.

Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
	
Class: Element
		This extends the <Element> prototype.
	*/
Element.extend({
/*	Property: getDimensions
		Returns width and height for element; if element is not visible the element is
		cloned off screen, shown, measured, and then removed.
		
		Arguments:
		options - a key/value set of options
		
		Options:
		computeSize - (boolean; optional) use <Element.getComputedSize> or not; defaults to false
		styles - (array; optional) see <Element.getComputedSize>
		plains - (array; optional) see <Element.getComputedSize>
		
		Returns:
		An object with .width and .height defined as integers. If options.computeSize is true, returns
		all the values that <Element.getComputedSize> returns.
		
		Example:
		>$(id).getDimensions()
		> > {width: #, height: #}
	*/
	getDimensions: function(options) {
		options = $merge({computeSize: false},options);
		var dim = {};
		function getSize(el, options){
			if(options.computeSize) dim = el.getComputedSize(options);
			else {
				dim.width = el.getSize().size.x;
				dim.height = el.getSize().size.y;
			}
			return dim;
		}
		try { //safari sometimes crashes here, so catch it
			dim = getSize(this, options);
		}catch(e){}
		if(this.getStyle('display') == 'none'){
			var before = {};
			//use this method instead of getStyles 
			['visibility', 'display', 'position'].each(function(style){
				before[style] = this.style[style]||'';
			}, this);
			//this.getStyles('visibility', 'display', 'position');
			this.setStyles({
				visibility: 'hidden',
				display: 'block',
				position:'absolute'
			});
			dim = getSize(this, options); //works now, because the display isn't none
			this.setStyles(before); //put it back where it was
		}
		return $merge(dim, {x: dim.width, y: dim.height});
	},
/*	Property: getComputedSize
		Calculates the size of an element including the width, border, padding, etc.
		
		Arguments:
		options - an object with key/value options
		
		Options:
		styles - (array) the styles to include in the calculation; defaults to ['padding','border']	
		plains - (object) an object with height and width properties, each of which is an 
							array including the edges to include in that plain. 
							defaults to {height: ['top','bottom'], width: ['left','right']}
		mode - (string; optional) limit the plain to 'vertical' or 'horizontal'; defaults to 'both'
		
		Returns:
		size - an object that contans dimension values (integers); see list below
		
		
		Dimension Values Returned:
		width - the actual width of the object (not including borders or padding)
		height - the actual height of the object (not including borders or padding)
		border-*-width - (where * is top, right, bottom, and left) the width of the border on that edge
		padding-* - (where * is top, right, bottom, and left) the width of the padding on that edge
		computed* - (where * is Top, Right, Bottom, and Left; e.g. computedRight) the width of all the 
			styles on that edge computed (so if options.styles is left to the default padding and border,
			computedRight is the sum of border-right-width and padding-right)
		totalHeight - the total sum of the height plus all the computed styles on the top or bottom. by
			default this is just padding and border, but if you were to specify in the styles option
			margin, for instance, the totalHeight calculated would include the margin.
		totalWidth - same as totalHeight, only using width, left, and right

		Example:
(start code)
$(el).getComputedSize();
returns:
{
	padding-top:0,
	border-top-width:1,
	padding-bottom:0,
	border-bottom-width:1,
	padding-left:0,
	border-left-width:1,
	padding-right:0,
	border-right-width:1,
	width:100,
	height:100,
	totalHeight:102,
	computedTop:1,
	computedBottom:1,
	totalWidth:102,
	computedLeft:1,
	computedRight:1
}
(end)		
	*/
	getComputedSize: function(options){
		options = $merge({
			styles: ['padding','border'],
			plains: {height: ['top','bottom'], width: ['left','right']},
			mode: 'both'
		}, options);
		var size = {width: 0,height: 0};
		switch (options.mode){
			case 'vertical':
				delete size.width;
				delete options.plains.width;
				break;
			case 'horizontal':
				delete size.height;
				delete options.plains.height;
				break;
		}
		var getStyles = [];
		//this function might be useful in other places; perhaps it should be outside this function?
		$each(options.plains, function(plain, key){
			plain.each(function(edge){
				options.styles.each(function(style){
					getStyles.push((style=="border")?style+'-'+edge+'-'+'width':style+'-'+edge);
				});
			});
		});
		var styles = this.getStyles.apply(this, getStyles);
		var subtracted = [];
		$each(options.plains, function(plain, key){ //keys: width, height, plains: ['left','right'], ['top','bottom']
			size['total'+key.capitalize()] = 0;
			size['computed'+key.capitalize()] = 0;
			plain.each(function(edge){ //top, left, right, bottom
				size['computed'+edge.capitalize()] = 0;
				getStyles.each(function(style,i){ //padding, border, etc.
					//'padding-left'.test('left') size['totalWidth'] = size['width']+[padding-left]
					if(style.test(edge)) {
						styles[style] = styles[style].toInt(); //styles['padding-left'] = 5;
						if(isNaN(styles[style]))styles[style]=0;
						size['total'+key.capitalize()] = size['total'+key.capitalize()]+styles[style];
						size['computed'+edge.capitalize()] = size['computed'+edge.capitalize()]+styles[style];
					}
					//if width != width (so, padding-left, for instance), then subtract that from the total
					if(style.test(edge) && key!=style && 
						(style.test('border') || style.test('padding')) && !subtracted.test(style)) {
						subtracted.push(style);
						size['computed'+key.capitalize()] = size['computed'+key.capitalize()]-styles[style];
					}
				});
			});
		});
		if($chk(size.width)) {
			size.width = size.width+this.offsetWidth+size.computedWidth;
			size.totalWidth = size.width + size.totalWidth;
			delete size.computedWidth;
		}
		if($chk(size.height)) {
			size.height = size.height+this.offsetHeight+size.computedHeight;
			size.totalHeight = size.height + size.totalHeight;
			delete size.computedHeight;
		}
		return $merge(styles, size);
	}
});
/* do not edit below this line */	 
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/element.dimensions.js,v $
$Log: element.dimensions.js,v $
Revision 1.8  2007/09/07 22:19:30  newtona
popupdetails: updating options handling methodology
stickyWinFx: fixed a bug where, if you were fast enough, you could introduce a flicker bug - this is hard to produce so most people probably hadn't seen it

Revision 1.7	2007/08/27 23:09:02	newtona
MooScroller: removed periodical for scrollbar resizing; the user can implement this if it's needed for each instance; also, renamed refactor to update
dbug: added support for dbug.dir, profile, stackTrace, etc.
element.dimensions: when getting the size of hidden elements the method now restores the previous inline styles to their original state
element.pin: fixed positioning bug

Revision 1.6	2007/08/20 19:53:33	newtona
fixing a typo

Revision 1.5	2007/08/20 18:11:51	newtona
Iframeshim: better handling for methods when the shim isn't ready
stickyWin: fixed a bug for the cssClass option in stickyWinHTML
html.table: push now returns the dom elements it creates
jsonp: fixed a bug; request no longer requires a url argument (my bad)
Fx.SmoothMove: just tidying up some syntax.
element.dimensions: updated getDimensions method for computing size of elements that are hidden; no longer clones element

Revision 1.4	2007/08/08 18:22:07	newtona
fixed a bug with Element.getDimensions (which affected Fx.SmoothMove, Fx.SmoothShow, Element.setPosition, and the bazillion other things that use it). would only show up under certain CSS layout situations.

Revision 1.3	2007/05/30 20:32:33	newtona
doc updates

Revision 1.2	2007/05/29 22:01:53	newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.1	2007/05/29 21:25:31	newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories


*/
