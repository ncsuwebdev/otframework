/*	Script: carousel_non_gf.js
		This script handles carousels on the CNET Redball network; it is based on
		Prototype and moo.fx - NOT the new global framework.
		
		Dependencies:
		cnet.global.framework.js <cnet global framework>
		
		This class is for the standard cnet carousel for doors on our
		network. It requires a prescribed html layout documented below.
		Instantiate this carousel class, configured to your names and
		preferences, and you're done.
		
		Class: CNETcarousel
		The CNET Redball carousel class.
		
Arguments:
	options - optional, an object containing options.

Options:
			slidesSelector 		- the css selector for the slide elements
												default: "slide" 
												(note that this is a slightly different syntax than the GF version)
			bubblesSelector 	- the css selector for the buttons
												default: "bubble"
												(note that this is a slightly different syntax than the GF version)
			startIndex 				-  the first item to show
												default: 0
			carouselContainer -  the id of the parent element that contains
												the carousel (which is typically hidden in css
												with visibility: hidden)
												default: "Carousel" 
			carouselLinksId 	-  the id you want to give the div that will hold
												all the buttons that this class creates
												default: "CaroLinks"
			bubbleButtonBGImgSelector -  the selector for all the images for the
												buttons behind the story bubbles
												default: "bbg",
												(note that this is a slightly different syntax than the GF version)
			buttonOnGifSrc 		-  the source for the "on" state of the buttons - gif format
												default: "http - //i.i.com.com/cnwk.1d/i/fd/c/green_button.gif",
			buttonOffGifSrc 	-  the source for the "off" state of the buttons - gif format
												default: "http - //i.i.com.com/cnwk.1d/i/fd/c/gray_button.gif",
			rotateAction 			-  the action the user takes to rotate to the next button;
												options: mouseover, click, or none
												default: 'none'
			slideInterval 		-  the interval between slide rotations in the slideshow
												default: 4000
			autoplay 					-  turn the slideshow on on instantiation
												default: true
		
		Examples:
		>var testCrsl = null;
		>Event.onDOMReady({
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
		
		HTML layout:
		(start code)
			<div id="Carousel">
				<!-- SLIDE #1 -->
				<div class="slide dark">
					...slide stuff goes here...
				</div>
			
				<div class="bubble">
					<img class="bbg" src="button_background_off"/>
					... bubble text or whatever goes here...
				</div>
				
				repeate...
				
			</div>
		(end)
		

		Property: CNETcarousel.showSlide
		Shows a slide (and hides the others).
		
		Parameters:
		int - the slide index to show
		
		Example:
		>myCarousel.showSlide(0) //shows the first slide
		
		Property: CNETcarousel.autoplay
		Turns autoplay on.
		
		Property: CNETcarousel.stop
		Turns autoplay off.
		
		Property: CNETcarousel.rotate
		Progresses to the next slide.
		
		Property: CNETcarousel.show
		Shows the carousel component (visibility: visible);
		
		Property: CNETcarousel.hide
		Hides the carousel component (visibility: hidden);
 -- */

var CNETcarousel = Class.create();
CNETcarousel.prototype = {
	bubbles: [],
	slides: [],
	bubbleBGImgButtons: [],
	currentSlide: -1,
	nextSlide: 0,
	initialize: function (options) {
		this.options = Object.extend({
			slidesSelector: "slide",
			bubblesSelector: "bubble",
			slideInterval: 4000,
			transitionDuration: 700,
			startIndex: 0,
			carouselContainer: "Carousel",
			carouselLinksId: "CaroLinks",
			bubbleButtonBGImgSelector: "bbg",
			buttonOnGifSrc: "http://i.i.com.com/cnwk.1d/i/fd/c/green_button.gif",
			buttonOffGifSrc: "http://i.i.com.com/cnwk.1d/i/fd/c/gray_button.gif",
			rotateAction: 'none',
			autoplay: true
		}, options || {});
		var crsl = this;
		this.slides = this.setUpElementIds({idArray: this.slides, 
													elementSelector: this.options.slidesSelector, 
													containerSelector: this.options.carouselContainer, 
													idPrefix: this.options.carouselContainer+"_slide"});
		this.bubbles = this.setUpElementIds({idArray: this.bubbles, 
													elementSelector: this.options.bubblesSelector, 
													containerSelector: this.options.carouselContainer, 
													idPrefix: this.options.carouselContainer+"_bubble"});
		this.bubbleBGImgButtons = this.setUpElementIds({idArray: this.bubbleBGImgButtons, 
													elementSelector: this.options.bubbleButtonBGImgSelector, 
													containerSelector: this.options.carouselContainer, 
													idPrefix: this.options.carouselContainer+"_bblbuttons"});
		this.createBubbles();
		if(!document.all){
			this.options.buttonOnGifSrc = this.options.buttonOnGifSrc.replace(".gif", ".png");
			this.options.buttonOffGifSrc = this.options.buttonOffGifSrc.replace(".gif", ".png");
		}
		this.bubbleBGImgButtons.each(function(img){
			$(img).src = crsl.options.buttonOffGifSrc;
		});
		this.createFx();
		this.showSlide(this.options.startIndex);
		if(this.options.autoplay) this.autoplay();
		if(this.options.rotateAction != 'none') this.setupAction(this.options.rotateAction);
	},
	setupAction: function(action) {
		var crsl = this;
		this.bubbles.each(function(el, idx){
			Event.observe(el,action, function() {
				crsl.slideFx[idx].setOptions({duration: 100});
				crsl.stop();
				if(crsl.currentSlide != idx) crsl.showSlide(idx);
			});
		});
	},
	createBubbles: function(){
		var crsl = this;
		bubbleBoxes = document.createElement("div");
		bubbleBoxes.id = this.options.carouselLinksId;
		$(this.options.carouselContainer).appendChild(bubbleBoxes);
		var crsl = this;
		$A(this.bubbles).each(function(bub) {
			bubbleBoxes.appendChild($(bub));
		});
		this.bubbleBGImgButtons.each(function(img){
			img.src = crsl.options.buttonOnGifSrc;
		});
	},
	setUpElementIds: function(options){
		//idArray
		//elementSelector
		//containerSelector
		//idPrefix
		if (typeof options.idArray == "undefined" || options.idArray.length == 0){
			var elements = document.getElementsByClassName(options.elementSelector, options.containerSelector);
			elements.each(function(el,idx) {
				if(typeof el.id == "undefined" || el.id == "")
					el.id = options.idPrefix + idx;
				options.idArray.push(el.id);
			});
			return options.idArray;
		}
	},
	createFx: function(){
		this.slideFx = [];
		var crsl = this;
		this.slides.each(function(slide, idx){
			crsl.slideFx[idx] = new fx.Opacity(slide, {duration: crsl.options.transitionDuration});
			crsl.slideFx[idx].hide();
		});
	},
	showSlide: function(next){
		current = this.currentSlide;
		var crsl = this;
		this.slideFx.each(function(fx, idx) {
			if(idx != next && fx.now > 0) fx.custom(1,0);
			$(crsl.bubbles[idx]).removeClassName("on");
			$(crsl.bubbleBGImgButtons[idx]).src = crsl.options.buttonOffGifSrc;
		});
		if(this.slideFx[next].now < .999) this.slideFx[next].custom(0,1);	
		$(this.bubbles[next]).addClassName("on");
		$(this.bubbleBGImgButtons[next]).src = this.options.buttonOnGifSrc;
		this.currentSlide = next;
	},
	autoplay: function(){
		this.slideshowInt = setInterval(this.rotate.bind(this), this.options.slideInterval);
	},
	stop: function(){
		clearInterval(this.slideshowInt);
	},
	rotate: function(){
		current = this.currentSlide;
		next = (current+1 >= this.slides.length) ? 0 : current+1;
		this.showSlide(next);
	},
	show: function() {
		$('carouselContainer').visibility = "visible";
	},
	hide: function(){
		$('carouselContainer').visibility = "hidden";
	}
};
var testCrsl = null;
Event.onDOMReady(function(){
	testCrsl = new CNETcarousel({rotateAction:'mouseover'});


	/* panels */
	function ShowTab(x) {
		if (activePanel!=x) {
			$(tabName+x).className = "on";
			$(panelName+x).style.display = "block";
	
			if (activePanel!=null) {
				$(tabName+activePanel).className = "";
				$(panelName+activePanel).style.display = "none";
			}
			activePanel = x;
		}
	};

	function SetTabOnClick(what, where) {	
		what.onclick = function() {
			ShowTab(where);
			this.blur();
		};
	};	

	var tabSetName   = "tabSet";
	var tabName      = "tab";
	var panelSetName = "panelSet";
	var panelName    = "panel";
	var activePanel = null;		
	var tabs = $(tabSetName);

	if (typeof(tabs)!="undefined") {	
		var ts = tabs.getElementsByTagName("LI");
		var ps = document.getElementsByClassName(panelName, panelSetName);
	
		for(var x=0;x<ts.length;x++) {
			ts[x].id = tabName+x;
			ps[x].id = panelName+x;
			var l = ts[x].getElementsByTagName("A");
			SetTabOnClick(l[0],x);
		}
		
		try {
			if ( !(startUpPanel>=0 && startUpPanel<ts.length)) {
				startUpPanel = 0;
			}
		} catch(e) {
			startUpPanel = 0;
		};
		ShowTab(startUpPanel);
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/legacy/carousel_non_gf.js,v $
$Log: carousel_non_gf.js,v $
Revision 1.2  2006/11/02 21:34:40  newtona
added cvs footer


*/
