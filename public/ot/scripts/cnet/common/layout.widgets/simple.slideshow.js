/*	Script: simple.slideshow.js
		Makes a very, very simple slideshow gallery with a collection of dom elements and previous and next buttons.
		
		Author:
		Aaron Newton
		
		Dependencies:
		mootools - 	<Moo.js>, <Utility.js>, <Common.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>, <Fx.Base.js>, <Dom.js>, <Cookie.js>

		Class: SimpleSlideShow
		Makes a very, very simple slideshow gallery with a collection of dom elements and previous and next buttons.
		
		Arguments:
		options - an object with key/value settings.
		
		Options:
		startIndex - (integer) the first image to show
		slides - (array) a collection of elements already in the dom.
		currentSlideClass - (string; optional) class to assign the currently visible slide; defaults to "currentSlide"
		currentIndexContainer - (dom element or id) container to display the the currently shown slide index
			(i.e. "showing *2* of 3"); optional
		maxContainer - (dom element or id) container to display the maximum number of slides available; optional
		nextImg - (dom element or id) image to capture clicks to show the next image; optional, but if 
			not supplied you'll have to execute <cycleForward> yourself.
		prevImg - (dom element or id) image to capture clicks to show the next image; optional, but if 
			not supplied you'll have to execute <cycleBack> yourself.
		wrap - (boolean) when the user clicks next at the end of a set, go back to the start 
			(and if they click prev at the begining, go to the end); defaults to true
		disabledLinkClass - (string) class to add to next/prev links when there are no next or prev slides;
			defaults to "disabled"
		onNext - (function) callback for when the user clicks next; optional
		onPrev - (function) callback for when the user clicks prev; optional
		onSlideClick - (function) callback for when the user clicks a slide, this function will 
			be passed the slide clicked and the index of the slide. optional
		crossFadeOptions - (object) options object to be passed to the opacity effects.
		
		Example:
(start code)
new SimpleSlideShow({
  startIndex: 0,
	slides: $$('.slide'),
  currentIndexContainer: 'slideNow', //an element or it's id
  maxContainer: 'slideMax',
  nextLink: 'nextImg',
  prevLink: 'prevImg'
});
(end)
	*/
	
	var SimpleSlideShow = new Class({
		options: {
			startIndex: 0,
			slides: [],
			currentSlideClass: 'currentSlide',
			currentIndexContainer: false,
			maxContainer: false,
			nextLink: false,
			prevLink: false,
			wrap: true,
			disabledLinkClass: 'disabled',
			onNext: Class.empty,
			onPrev: Class.empty,
			onSlideClick: Class.empty,
			crossFadeOptions: {}
		},
		initialize: function(options){
			this.setOptions(options);
			this.slides = this.options.slides;
			this.makeSlides();
			this.setCounters();
			this.setUpNav();
			this.now = this.options.startIndex;
			if(this.slides.length > 0) this.showSlide(this.now);
		},
		setCounters: function(){
			if($(this.options.currentIndexContainer))$(this.options.currentIndexContainer).setHTML(this.now+1);
			if($(this.options.maxContainer))$(this.options.maxContainer).setHTML(this.slides.length);
		},
		makeSlides: function(){
			//hide them all
			this.slides.each(function(slide, index){
				if(index != this.now) slide.setStyle('display', 'none');
				else slide.setStyle('display', 'block');
				this.makeSlide(slide);
			}, this);
		},
		makeSlide: function(slide){
			slide.addEvent('click', function(){ this.fireEvent('onSlideClick'); }.bind(this));
		},
		setUpNav: function(){	
			if($(this.options.nextLink)) $(this.options.nextLink).addEvent('click', function(){
					this.cycleForward();
				}.bind(this));
			if($(this.options.prevLink)) $(this.options.prevLink).addEvent('click', function(){
					this.cycleBack();
				}.bind(this));
		},
/*	Property: cycleForward
		Shows the next slide.
	*/
		cycleForward: function(){
			if($type(this.now) && this.now < this.slides.length-1) this.showSlide(this.now+1);
			else if($type(this.now) && this.options.wrap) this.showSlide(0);
			else this.showSlide(this.options.startIndex);
			this.fireEvent('onNext');
			if(this.now == this.slides.length && !this.options.wrap && $(this.options.nextLink))
				$(this.options.nextLink).addClass(this.options.disabledLinkClass);
			else if ($(this.options.nextLink)) $(this.options.nextLink).removeClass(this.options.disabledLinkClass);
		},
/*	Property: cycleBack
		Shows the prev slide.
	*/
		cycleBack: function(){
			if(this.now > 0) {
				this.showSlide(this.now-1);
				this.fireEvent('onPrev');
			} else if(this.options.wrap && this.slides.length > 1) {
				this.showSlide(this.slides.length-1);
				this.fireEvent('onPrev');
			}
			if(this.now == 0 && !this.options.wrap && $(this.options.prevSlide))
				$(this.options.prevSlide).addClass(this.options.disabledLinkClass);
			else if ($(this.options.prevSlide)) 
				$(this.options.prevSlide).removeClass(this.options.disabledLinkClass);
		},
/*	Property: showSlide
		Shows a specific slide.
		
		Arguments:
		iToShow - (integer) index of the slide to show.
	*/
		showSlide: function(iToShow){
			var now = this.now;
			var s = this.slides[iToShow]; //saving bytes
			function fadeIn(s, resetOpacity){
				s.setStyle('display','block');
				if(s.fxOpacityOk()) {
					if(resetOpacity) s.setStyle('opacity', 0);
					s.effect('opacity', this.options.crossFadeOptions).start(1);
				}
			};
			if(s) {
				if($type(this.now) && this.now != iToShow){
					if(s.fxOpacityOk()) {
						this.slides[this.now].effect('opacity', this.options.crossFadeOptions).start(0).chain(function(){
							this.slides[now].setStyle('display','none');
							s.addClass(this.options.currentSlideClass);
							fadeIn.bind(this, [s, true])();
						}.bind(this));
					} else {
						this.slides[this.now].setStyle('display','none');
						fadeIn.bind(this, s)();
					}
				} else fadeIn.bind(this, s)();
				this.now = iToShow;
				this.setCounters();
			}
		},
		slideClick: function(){
			this.fireEvent('onSlideClick', [this.slides[this.now], this.now]);
		}
	});
	SimpleSlideShow.implement(new Events);
	SimpleSlideShow.implement(new Options);

/*	Class: SimpleImageSlideShow
		Extends <SimpleSlideShow> to make a slideshow of images.
		
		Arguments:
		options - a key/value options object; inherits options from <SimpleSlideShow>.
		
		Options:
		imgUrls - (array; optional) an array of image urls to add to the dom and to the slideshow
		imgClass - (string; optional) a class to add to the images that get created on the fly
		container - (element; optional) if you are adding images to the dom either using <addImg> or
			the imgUrls array above, then "container" is required to know where to put them.
	*/
	var SimpleImageSlideShow = SimpleSlideShow.extend({
		options: {
			imgUrls: [],
			imgClass: 'screenshot',
			container: false
		},
		initialize: function(options){
			this.parent(options);
			this.options.imgUrls.each(function(url){
				this.addImg(url);
			}, this);
			this.showSlide(this.options.startIndex);
		},
/*	Property: addImg
		Adds a new image to the group
	*/
		addImg: function(url){
			if($(this.options.container)) {
				var img = new Element('img').setProperties({
							'src': url,
							'id': this.options.imgClass+this.slides.length
							}).addClass(this.options.imgClass).setStyle(
							'display', 'none').injectInside($(this.options.container)).addEvent(
							'click', this.slideClick.bind(this));
				this.slides.push(img);
				this.makeSlide(img);
				this.setCounters();
			}
		}
	});

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/layout.widgets/simple.slideshow.js,v $
$Log: simple.slideshow.js,v $
Revision 1.10  2007/10/05 17:34:02  newtona
simple.slideshow: adding check in cycleBack to ensure there are more than one image.

Revision 1.9  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.8  2007/04/03 00:12:41  newtona
fixed a binding issue with slideshow

Revision 1.7  2007/03/29 22:37:54  newtona
simple slide show now only cross-fades in ie6 if the element has a bgcolor (see Element.fxOpacityOk)

Revision 1.6  2007/03/20 21:30:21  newtona
slideshow now checks to see if there are any slides before it attempts to show one.

Revision 1.5  2007/03/19 22:26:38  newtona
start slide is now shown on initialization

Revision 1.4  2007/03/08 23:29:59  newtona
strict javascript warnings cleaned up

Revision 1.3  2007/02/21 00:29:17  newtona
switched Class.create to Class.empty

Revision 1.2  2007/02/12 17:46:31  newtona
tweaking things, no significant functional changes

Revision 1.1  2007/02/09 20:23:19  newtona
moving simple.img.gallery.js to simple.slideshow.js
rewrote gallery to do dom elements or images


*/
