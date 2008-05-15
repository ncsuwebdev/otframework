/*	Script: MooScroller.js
		Basically recreates the standard scrollbar behavior for elements with overflow but using DOM elements so that the scroll bar elements are completely styleable by css.

		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools 1.1 - <Core.js>, <Class.js>, <Class.Extras.js>, <Function.js>, <Number.js>, <String.js>, <Element.js>, <Element.Dimensions.js>, <Element.Event.js>, <Element.Selectors.js>
		
		Arguments:
		content - (DOM element or id) element that contains the overflown content
		knob - (DOM element or id) element that acts as the scroll bar
		options - (object) a key/value set of options
		
		Options:
		mode - (string) 'vertical' or 'horizontal'; defaults to 'vertical'
		scrollSteps - (integer) how many steps to move when the user moves their mouse wheel or clicks the up/down scroll buttons
		wheel - (boolean) true (default) will enable mouse wheel scrolling
		scrollLinks - object with elements for up and down scrolling
		maxThumbSize - (integer) the maximum size to allow the scroll knob to be; defaults to the height of the container it is in.
		
		Options.scrollLinks:
		forward - (DOM element or id) element that, when clicked, will scroll the area forward 
							(right in horizontal mode, down in vertical mode); defaults to $('scrollForward'); 
							(if not found, nothing bad happens)
		back - (DOM element or id) element that, when clicked, will scroll the area back 
							(left in horizontal mode, up in vertical mode); defaults to $('scrollBack');
			
		Events:
		onScroll - (function) callback for when the user scrolls
		onPage - (function) callback for when the user paginates up or down; passed a boolean - true if paging forward.

		
Example:
(start code)
<div id="scroller">
	<div id="content">
		<ol id="scrollerOL">
			<li>one</li>
			<li>two</li>
			<li>three</li>
			<li>four</li>
			<li>five</li>
			<li>six</li>
			<li>seven</li>
			<li>eight</li>
			<li>nine</li>
			<li>ten</li>
		</ol>
		<p>a paragraph</p>
		<ol>
			<li>blah</li>
			<li>blah</li>
		</ol>
	</div>
	<div id="scrollarea">
		<div id="scrollBack"></div>
		<div id="scrollBarContainer">
			<div id="scrollKnob"></div>
		</div>
		<div id="scrollForward"></div>
	</div>
</div>
<script>
	new MooScroller('content', 'scrollKnob');
</script>
(end)
	*/
var MooScroller = new Class({

		options: {
			maxThumbSize: 10,
			mode: 'vertical',
			width: 0, //required only for mode: horizontal
			scrollSteps: 10,
			wheel: true,
			scrollLinks: {
				forward: 'scrollForward',
				back: 'scrollBack'
			},
			onScroll: Class.empty,
			onPage: Class.empty
		},

		initialize: function(content, knob, options){
			this.setOptions(options);
			this.horz = (this.options.mode == "horizontal");

			this.content = $(content).setStyle('overflow', 'hidden');
			this.knob = $(knob);
			this.track = this.knob.getParent();
			this.setPositions();
			
			if(this.horz && this.options.width) {
				this.wrapper = new Element('div');
				this.content.getChildren().each(function(child){
					this.wrapper.adopt(child);
				});
				this.wrapper.injectInside(this.content).setStyle('width', this.options.width);
			}
			

			this.bound = {
				'start': this.start.bind(this),
				'end': this.end.bind(this),
				'drag': this.drag.bind(this),
				'wheel': this.wheel.bind(this),
				'page': this.page.bind(this)
			};

			this.position = {};
			this.mouse = {};
			this.update();
			this.attach();
			
			var clearScroll = function (){
				$clear(this.scrolling);
			}.bind(this);
			['forward','back'].each(function(direction) {
				var lnk = $(this.options.scrollLinks[direction]);
				if(lnk) {
					lnk.addEvents({
						mousedown: function() {
							this.scrolling = this[direction].periodical(50, this);
						}.bind(this),
						mouseup: clearScroll.bind(this),
						click: clearScroll.bind(this)
					});
				}
			}, this);
			this.knob.addEvent('click', clearScroll.bind(this));
			window.addEvent('domready', function(){
				try {
					$(document.body).addEvent('mouseup', clearScroll.bind(this));
				}catch(e){}
			}.bind(this));
		},
		setPositions: function(){
			[this.track, this.knob].each(function(el){
				if (el.getStyle('position') == 'static') el.setStyle('position','relative');
			});

		},
/*	Property: update
		Updates the size of the scroll knob; execute this method when the content changes or the container's size is altered.
	*/
		update: function(){
			var plain = this.horz?'Width':'Height';
			this.contentSize = this.content['offset'+plain];
			this.contentScrollSize = this.content['scroll'+plain];
			this.trackSize = this.track['offset'+plain];

			this.contentRatio = this.contentSize / this.contentScrollSize;

			this.knobSize = (this.trackSize * this.contentRatio).limit(this.options.maxThumbSize, this.trackSize);

			this.scrollRatio = this.contentScrollSize / this.trackSize;
			this.knob.setStyle(plain.toLowerCase(), this.knobSize+'px');

			this.updateThumbFromContentScroll();
			this.updateContentFromThumbPosition();
		},

		updateContentFromThumbPosition: function(){
			this.content[this.horz?'scrollLeft':'scrollTop'] = this.position.now * this.scrollRatio;
		},

		updateThumbFromContentScroll: function(){
			this.position.now = (this.content[this.horz?'scrollLeft':'scrollTop'] / this.scrollRatio).limit(0, (this.trackSize - this.knobSize));
			this.knob.setStyle(this.horz?'left':'top', this.position.now+'px');
		},

		attach: function(){
			this.knob.addEvent('mousedown', this.bound.start);
			if (this.options.scrollSteps) this.content.addEvent('mousewheel', this.bound.wheel);
			this.track.addEvent('mouseup', this.bound.page);
		},

		wheel: function(event){
			event = new Event(event);
			this.scroll(-(event.wheel * this.options.scrollSteps));
			this.updateThumbFromContentScroll();
			event.stop();
		},

		scroll: function(steps){
			steps = steps||this.options.scrollSteps;
			this.content[this.horz?'scrollLeft':'scrollTop'] += steps;
			this.updateThumbFromContentScroll();
		},
		forward: function(steps){
			this.scroll(steps);
		},
		back: function(steps){
			steps = steps||this.options.scrollSteps;
			this.scroll(-steps);
		},

		page: function(event){
			var axis = this.horz?'x':'y';
			event = new Event(event);
			var forward = (event.page[axis] > this.knob.getPosition()[axis]);
			this.scroll((forward?1:-1)*this.content['offset'+(this.horz?'Width':'Height')]);
			this.updateThumbFromContentScroll();
			this.fireEvent('onPage', forward);
			event.stop();
		},

		
		start: function(event){
			event = new Event(event);
			var axis = this.horz?'x':'y';
			this.mouse.start = event.page[axis];
			this.position.start = this.knob.getStyle(this.horz?'left':'top').toInt();
			document.addEvent('mousemove', this.bound.drag);
			document.addEvent('mouseup', this.bound.end);
			this.knob.addEvent('mouseup', this.bound.end);
			event.stop();
		},

		end: function(event){
			event = new Event(event);
			document.removeEvent('mousemove', this.bound.drag);
			document.removeEvent('mouseup', this.bound.end);
			this.knob.removeEvent('mouseup', this.bound.end);
			event.stop();
		},

		drag: function(event){
			event = new Event(event);
			var axis = this.horz?'x':'y';
			this.mouse.now = event.page[axis];
			this.position.now = (this.position.start + (this.mouse.now - this.mouse.start)).limit(0, (this.trackSize - this.knobSize));
			this.updateContentFromThumbPosition();
			this.updateThumbFromContentScroll();
			event.stop();
		}

	});
	MooScroller.implement(new Events);
	MooScroller.implement(new Options);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/layout.widgets/MooScroller.js,v $
$Log: MooScroller.js,v $
Revision 1.8  2007/09/24 20:55:49  newtona
new file: StickyWin.Ajax - adds ajax support to all stickywin classes (creates new classes, just append .Ajax to any of the existing ones)
updated redball common full to include StickyWin.Ajax
date.picker, product.picker - updated syntax to use Element.empty
form.validator - now passes along the event object to the onFormValidate event so that the form submit event can be stopped if you like
popupdetails - added html response support; you can now return the html you wish to display rather than a json object; only applies to ajax. Also added a cache so that multiple requests are not made for the same url.
stickyWinHTML - ractored so that options are now, you know, *optional*
MooScroller - added support for width option for horizontal scrolling

Revision 1.7  2007/09/13 23:21:22  newtona
slight docs tweak

Revision 1.6  2007/09/13 23:18:54  newtona
removing a dbug line

Revision 1.5  2007/09/13 23:15:37  newtona
woops. didn't actually check in my mooscroller changes...

Revision 1.1  2007/08/25 00:52:06  newtona
got lazy with my semi-colons...


*/
