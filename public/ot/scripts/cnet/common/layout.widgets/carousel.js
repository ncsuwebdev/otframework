/*	
Script: carousel.js
	Builds a carousel object that manages the basic functions of a generic carousel (a carousel
	here being a collection of "slides" that play from one to the next, with a collection of
	"buttons" that reference each slide).
	
Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>
	
Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
	


Class: CNETcarousel
	This class is for the standard cnet carousel for doors on our
	network. Instantiate this carousel class, configured to your names and
	preferences, and you're done. You can have as many on a page as
	you like.
	
	This class should work for any type of layout provided that:
	- The carousel is made up of buttons and slides, and there are
		an equal amount of both.
	- The buttons have an "on state" class and an "off state" class
	- The slides are "on top" of each other; this class fades one
		out and fades another in. It does not create a slide or position
		it.
	
Arguments:
  container - a DOM element containing the slides and buttons
	options - optional, an object containing options.

Options:
	carouselContainer - the id of the parent element that contains
											the carousel (which is typically hidden in css
											with visibility: hidden)
											default: "Carousel"
	slidesSelector 		- the css selector for the slide elements
											note: this is relative to the carouselContainer object,
											so only elements that match this selector within that object
											will be included in the carousel
											default: ".slide"
	buttonsSelector 	- the css selector for the buttons; same rules as slidesSelector
											default: ".button"
	startIndex 				- the first item to show
											default: 0
	buttonOnClass 		- the class for the "on" state of the buttons
											default: "selected",
	buttonOffClass	 	- the class for the "off" state of the buttons
											default: "off",
	rotateAction 			- the action the user takes to rotate to the next button;
											options: mouseover, click, or none
											default: 'none'
	rotateActionDuration - the duration to use when the user interacts with the buttons
	 										if rotateAction != "none". default: 100
	slideInterval 		- the interval between slide rotations in the slideshow
											default: 4000
	transitionDuration - the duration of the transition effect
											default: 700
	autoplay 					-  turn the slideshow on on instantiation
											default: true	
	
	
	Examples:
	>var testCrsl = null;
	>window.addEvent('domready', {
	>	testCrsl = new CNETcarousel({});
	>});
	>
	>OR
	>...
	>	testCrsl = new CNETcarousel({
	> 	slideInterval: 8000,
	>		rotateAction: 'mouseover',
	>		etc...
	>	});
	
	HTML layout example:
	(start code)
		<div id="Carousel">
			<!-- SLIDE #1 -->
			<div class="slide dark">
				...slide stuff goes here...
			</div>
			<!-- SlIDE #2 -->
			...
			<!-- SlIDE #3 -->
			...
			<!-- SlIDE #4 -->
			...
		
			<div class="bubbles">
				<div class="button">
					... bubble text or whatever goes here...
				</div>
			</div>
			<!-- BUTTON #2 -->
			<!-- BUTTON #3 -->
			<!-- BUTTON #4 -->
		</div>
	(end)
-- */
var CNETcarousel = new Class({
	initialize: function(container, options){
		this.container = $(container);
		if(!this.container.hasClass('hasCarousel')){
			this.container.addClass('hasCarousel');
			this.slides = [];
			this.buttons = [];
			this.setOptions({
				onRotate: Class.empty,
				onStop: Class.empty,
				onAutoPlay: Class.empty,
				onShowSlide: Class.empty,
				slidesSelector: ".slide",
				buttonsSelector: ".button",
				slideInterval: 4000,
				transitionDuration: 700,
				startIndex: 0,
				buttonOnClass: "selected",
				buttonOffClass: "off",
				rotateAction: "none",
				rotateActionDuration: 100,
				autoplay: true
			}, options);
			this.slides = $(container).getElements(this.options.slidesSelector);
			this.buttons = $(container).getElements(this.options.buttonsSelector);
			this.createFx();
			this.showSlide(this.options.startIndex);
			if(this.options.autoplay) this.autoplay();
			if(this.options.rotateAction != 'none') this.setupAction(this.options.rotateAction);
			return this;
		} else return false;
	},
/*
Property: setupAction
	*Private internal function; do not call directly.*
	Applies <showSlide>	to the user action.
	
Arguments:
	string - the action to apply the slide change to; 'click' or 'mouseover'
	*/
	setupAction: function(action) {
		this.buttons.each(function(el, idx){
			$(el).addEvent(action, function() {
				this.slideFx.setOptions(this.slideFx.options, {duration: this.options.rotateActionDuration});
				if(this.currentSlide != idx) this.showSlide(idx);
				this.stop();
			}.bind(this));
		}, this);
	},
/*	
Property: createFx
	*Private internal function; do not call directly.*
	Creates the effects objects for each slide and stores them in this.slideFx array.	*/
	createFx: function(){
		this.slideFx = new Fx.Elements(this.slides, {duration: this.options.transitionDuration});
		this.slides.each(function(slide){
			slide.setStyle('opacity',0);
		});
	},
/*	
Property: showSlide
	*Private internal function; do not call directly.*
	Shows a slide (and hides the others).
		
Arguments:
	slideIndex - the slide index to show
		
Example:
	>myCarousel.showSlide(0) //shows the first slide
	*/
	showSlide: function(slideIndex){
		var action = {};
		this.slides.each(function(slide, index){
			if(index == slideIndex && index != this.currentSlide){ //show
				$(this.buttons[index]).removeClass(this.options.buttonOffClass).addClass(this.options.buttonOnClass);
				action[index.toString()] = {
					'opacity': [1]
				};
			} else {
				$(this.buttons[index]).removeClass(this.options.buttonOnClass).addClass(this.options.buttonOffClass);
				action[index.toString()] = {
					'opacity':[0]
				};
			}
		}, this);
		this.fireEvent('onShowSlide', slideIndex);
		this.currentSlide = slideIndex;
		this.slideFx.start(action);
	},
	
/*	
Property: autoplay
	Turns autoplay on.
	
Example:
	>myCarousel.autoplay() //start cycling slides
	*/	
	autoplay: function(){
		this.createFx();
		this.slideshowInt = this.rotate.periodical(this.options.slideInterval, this);
		this.fireEvent('onAutoPlay');
	},
/*	
Property: stop
	Stops autoplaying the slides.
	
Example:
	>myCarousel.stop() //stop cycling slides
	*/
	stop: function(){
		clearInterval(this.slideshowInt);
		this.fireEvent('onStop');
	},
/*	
Property: rotate
	*Private internal function; do not call directly.*
	Progresses to the next slide.	*/
	rotate: function(){
		current = this.currentSlide;
		next = (current+1 >= this.slides.length) ? 0 : current+1;
		this.showSlide(next);
		this.fireEvent('onRotate');
	},
/*	
Property: show
	Shows the carousel component (visibility: visible);	
	
	>myCarousel.show() //makes the carousel visible
	*/
	show: function() {
		$(this.options.carouselContainer).setStyle('visibility','visible');
		if(!$(this.options.carouselContainer).isVisible())$(this.options.carouselContainer).setStyle('display','block');
	},
/*	
Property: hide
	Hides the carousel component (visibility: hidden);
	
Example:
	>myCarousel.hide() //makes the carousel invisible
		*/
	hide: function(){
		$(this.options.carouselContainer).setStyle('visibility','hidden');
	}
});
CNETcarousel.implement(new Options);
CNETcarousel.implement(new Events);

/*	Class: CNETcarouselWithButtons
		Extends <CNETcarousel> to include button imgs that are rotated with the slides.
		
		Arguments:
		el - the element containing the carousel
		options - the options object
		
		Options:
		bubbleButtonBGImgSelector - (optional) the selector to find the images inside the carousel container.
				defaults to ".bbg".
		buttonOnGifSrc - (optional) the url to the "on" button. defaults to
				http://i.i.com.com/cnwk.1d/i/fd/c/green_button.gif
		buttonOffSrc - (optional) the url to the "off" button. defaults to
				http://i.i.com.com/cnwk.1d/i/fd/c/gray_button.gif

		See <CNETcarousel> for additional options.
			*/

var CNETcarouselWithButtons = CNETcarousel.extend({
	initialize:function(el, options){
		this.parent(el, $merge({
			bubbleButtonBGImgSelector: '.bbg',
			buttonOnGifSrc: 'http://i.i.com.com/cnwk.1d/i/fd/c/green_button.gif',
			buttonOffGifSrc: 'http://i.i.com.com/cnwk.1d/i/fd/c/gray_button.gif'
		}, options));
	},
	showSlide: function(slideIndex){
		this.buttons.each(function(button, index){
			$(button).getElement(this.options.bubbleButtonBGImgSelector).src = (index == slideIndex)?this.options.buttonOnGifSrc:this.options.buttonOffGifSrc;
		}, this);
		this.parent(slideIndex);
	}
});
var carousel = null;
window.addEvent('domready', function(){
	if($('Carousel')) {
		carousel = new CNETcarouselWithButtons($('Carousel'),{buttonsSelector:'.bubble', rotateAction:'mouseover'});
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/layout.widgets/carousel.js,v $
$Log: carousel.js,v $
Revision 1.8  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.7  2007/05/16 20:17:52  newtona
changing window.onDomReady to window.addEvent('domready'

Revision 1.6  2007/02/21 00:27:50  newtona
switched Class.create to Class.empty

Revision 1.5  2007/02/07 20:51:55  newtona
implemented Options class
implemented Events class

Revision 1.4  2007/01/26 05:51:42  newtona
syntax update for mootools 1.0
refactored to use Fx.Elements.js
docs update

Revision 1.3  2007/01/22 21:56:08  newtona
updated for mootools version 1.0

Revision 1.2  2007/01/19 01:22:54  newtona
changed event.ondomready > window.ondomready

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.3  2006/12/06 20:14:59  newtona
carousel - improved performance, changed some syntax, actually deployed into usage and tested
cnet.nav.accordion - improved css selectors for time
multiple accordion - fixed a typo
dbug.js - added load timers
element.cnet.js - changed syntax to utilize mootools more effectively
function.cnet.js - equated $set to $pick in preparation for mootools v1

Revision 1.2  2006/12/04 18:36:32  newtona
fixed a few syntax bugs, added subclass version with background images

Revision 1.1  2006/11/02 21:28:08  newtona
checking in for the first time.


*/
