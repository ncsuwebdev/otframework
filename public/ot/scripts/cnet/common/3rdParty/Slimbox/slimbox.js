/*	Script: slimbox.js
		A lightbox clone for Mootools.
		
		Authors:
		Christophe Beyls (http://www.digitalia.be); MIT-style license.
		Inspired by the original Lightbox v2 by Lokesh Dhakar.
		Refactored by Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Class: Lightbox
		A lightbox for displaying images in an overlay.
		
		Arguments:
		options - (object) a set of key/value options
		anchors - (array; optional) a group of anchors to which to add lightbox functionality.
		
		Options:
		resizeDuration - (integer) duration in milliseconds for the resize effect (defaults to 400)
		resizeTransition - optional <Fx.Transitions> transition reference
		initialWidth - (integer) the initial width of the box - defaults to 250
		initialHeight - (integer) the height width of the box - defaults to 250
		zIndex - (integer) zindex for the overlay (defaults to 10);
		overlayStyles - (object) styles object to pass to <Element.setStyle> for the modal layer (so you can set it to be whatever color or opacity you like). Note that the default styles are located in the (external) css file.
		animateCaption - (boolean) slide the caption in smoothly (defaults to true)
		showCounter - (boolean) shows the number of images in the set (defaults to true)
		autoScanLinks - (boolean) scan the document for anchor tags with rel tags == the relString option
		relString - (string) the string value for the "rel" tag that will make the link use the lightbox 
		                     (defaults to "lightbox"; unused if the anchors argument is specified)
	  useDefaultCss - (boolean) injects the default css for the lightbox; defauls to true
		assetBaseUrl - (string) the url where the css and image assets are (a directory); defaults to
		                     "http://www.cnet.com/html/rb/assets/global/slimbox/"
		onImageShow - (function) optional callback fired when an image is displayed
		onDisplay - (function) optional callback fired when the lightbox first shows up (onImageShow 
													 is fired just after this for the first image displayed)
		onHide - (function) optional callback fired when the lightbox is closed.
		
Examples:
(start code)
new Lightbox(); //defaults; scans the document for rel="lightbox...
new Lightbox({
	autoScanLinks: false
}, $$('a.lightbox')); //use all anchor tags with class "lightbox" instead
(end)

		Note:
		A new Lightbox is created on domReady, so it is not required that you write any javascript at all.
		All you must do is add rel="lightbox" to your images (and rel="lightbox[setName]" for sets). If you want
		to create a Lightbox on the fly or with some other set of images, you can do that whenever you like.
*/
var Lightbox = new Class({
	options: {
		resizeDuration: 400,
		resizeTransition: false,	// default transition
		initialWidth: 250,
		initialHeight: 250,
		zIndex: 10,
		animateCaption: true,
		showCounter: true,
		autoScanLinks: true,
		relString: 'lightbox',
		useDefaultCss: true,
		assetBaseUrl: 'http://www.cnet.com/html/rb/assets/global/slimbox/',
		onImageShow: Class.empty,
		onDisplay: Class.empty,
		onHide: Class.empty,
		overlayStyles: {}
	},

	initialize: function(options, anchors){
		this.setOptions(options);
		this.anchors = anchors || [];
		if (this.options.autoScanLinks) {
			$$('a').each(function(el){
				if (el.getProperty('rel') && el.getProperty('rel').test("^"+this.options.relString,'i')){
					if(!el.getProperty('lightboxed')) this.anchors.push(el);
				}
			}, this);
		}
		if(!$$(this.anchors).length) return; //no links!
		if(this.options.useDefaultCss) this.addCss();
		$$(this.anchors).each(function(el){
			if(!el.getProperty('lightboxed')) {
				el.setProperty('lightboxed', true);
				el.addEvent('click', function(e){
					new Event(e).stop();
					this.click(el);
				}.bind(this));
			}
		}.bind(this));
		this.eventKeyDown = this.keyboardListener.bindAsEventListener(this);
		this.eventPosition = this.position.bind(this);
		window.addEvent('domready', this.addHtmlElements.bind(this));
	},

	addHtmlElements: function(){
		this.overlay = new Element('div', {
			'class': 'lbOverlay',
			styles: {
				'z-index':this.options.zIndex
			}
		}).injectInside(document.body).setStyles(this.options.overlayStyles);
		this.center = new Element('div', {
			styles: {	
				width: this.options.initialWidth+'px', 
				height: this.options.initialHeight+'px', 
				'margin-left': (-(this.options.initialWidth/2))+'px', 'display': 'none',
				'z-index':this.options.zIndex+1
			}
		}).injectInside(document.body).addClass('lbCenter');
		this.image = new Element('div', {
			'class': 'lbImage'
		}).injectInside(this.center);
		
		this.prevLink = new Element('a', {'class': 'lbPrevLink', 'href': 'javascript:void(0);', 'styles': {'display': 'none'}}).injectInside(this.image);
		this.nextLink = this.prevLink.clone().removeClass('lbPrevLink').addClass('lbNextLink').injectInside(this.image);
		this.prevLink.addEvent('click', this.previous.bind(this));
		this.nextLink.addEvent('click', this.next.bind(this));

		this.bottomContainer = new Element('div', {'class': 'lbBottomContainer', 'styles': {'display': 'none', 'z-index':this.options.zIndex+1}}).injectInside(document.body);
		this.bottom = new Element('div', {'class': 'lbBottom'}).injectInside(this.bottomContainer);
		new Element('a', {'class': 'lbCloseLink', 'href': '#'}).injectInside(this.bottom).onclick = this.overlay.onclick = this.close.bind(this);
		this.caption = new Element('div', {'class': 'lbCaption'}).injectInside(this.bottom);
		this.number = new Element('div', {'class': 'lbNumber'}).injectInside(this.bottom);
		new Element('div', {'styles': {'clear': 'both'}}).injectInside(this.bottom);

		var nextEffect = this.nextEffect.bind(this);
		this.fx = {
			overlay: this.overlay.effect('opacity', {duration: 500}).hide(),
			resize: this.center.effects($extend({duration: this.options.resizeDuration, onComplete: nextEffect}, this.options.resizeTransition ? {transition: this.options.resizeTransition} : {})),
			image: this.image.effect('opacity', {duration: 500, onComplete: nextEffect}),
			bottom: this.bottom.effect('margin-top', {duration: 400, onComplete: nextEffect})
		};

		this.preloadPrev = new Element('img');
		this.preloadNext = new Element('img');
	},
	
	addCss: function(){
		window.addEvent('domready', function(){
			if(!$('SlimboxCss')) new Asset.css(this.options.assetBaseUrl + 'slimbox.css', {id: 'SlimboxCss'});
		}.bind(this));
	},

	click: function(link){
		link = $(link);
		var rel = link.getProperty('rel')||this.options.relString;
		if (rel == this.options.relString) return this.show(link.href, link.title);

		var j, imageNum, images = [];
		this.anchors.each(function(el){
			if (el.getProperty('rel') == link.getProperty('rel')){
				for (j = 0; j < images.length; j++) if(images[j][0] == el.href) break;
				if (j == images.length){
					images.push([el.href, el.title]);
					if (el.href == link.href) imageNum = j;
				}
			}
		}, this);
		return this.open(images, imageNum);
	},

	show: function(url, title){
		return this.open([[url, title]], 0);
	},

	open: function(images, imageNum){
		this.fireEvent('onDisplay');
		this.images = images;
		this.position();
		this.setup(true);
		this.top = (window.getScrollTop() + (window.getHeight() / 15)).toInt();
		this.center.setStyles({
			top: this.top+'px', 
			display: ''
		});
		this.fx.overlay.start(0.8);
		return this.changeImage(imageNum);
	},

	position: function(){
		this.overlay.setStyles({
			'top': window.getScrollTop()+'px', 
			'height': window.getHeight()+'px'
		});
	},

	setup: function(open){
		var elements = $$('object, iframe');
		elements.extend($$(window.ie ? 'select' : 'embed'));
		elements.each(function(el){
			if (open) el.lbBackupStyle = el.getStyle('visibility');
			var vis = (open ? 'hidden' : el.lbBackupStyle);
			el.setStyle('visibility', vis);
		});
		var fn = open ? 'addEvent' : 'removeEvent';
		window[fn]('scroll', this.eventPosition)[fn]('resize', this.eventPosition);
		document[fn]('keydown', this.eventKeyDown);
		this.step = 0;
	},

	keyboardListener: function(event){
		switch (event.keyCode){
			case 27: case 88: case 67: this.close(); break;
			case 37: case 80: this.previous(); break;	
			case 39: case 78: this.next();
		}
	},

	previous: function(){
		return this.changeImage(this.activeImage-1);
	},

	next: function(){
		return this.changeImage(this.activeImage+1);
	},

	changeImage: function(imageNum){
		this.fireEvent('onImageShow', imageNum);
		if (this.step || (imageNum < 0) || (imageNum >= this.images.length)) return false;
		this.step = 1;
		this.activeImage = imageNum;

		this.center.setStyle('backgroundColor', '');
		this.bottomContainer.setStyle('display', 'none');
		this.prevLink.setStyle('display', 'none');
		this.nextLink.setStyle('display', 'none');
		this.fx.image.hide();
		this.center.addClass('lbLoading');

		this.preload = new Element('img').addEvent('load', this.nextEffect.bind(this)).setProperty('src', this.images[imageNum][0]);
		return false;
	},

	nextEffect: function(){
		switch (this.step++){
		case 1:
			this.image.setStyle('backgroundImage', 'url('+this.images[this.activeImage][0]+')');
			this.image.setStyle('width', this.preload.width+'px');
			this.bottom.setStyle('width',this.preload.width+'px');
			this.image.setStyle('height', this.preload.height+'px');
			this.prevLink.setStyle('height', this.preload.height+'px');
			this.nextLink.setStyle('height', this.preload.height+'px');

			this.caption.setHTML(this.images[this.activeImage][1] || '');
			this.number.setHTML((!this.options.showCounter || (this.images.length == 1)) ? '' : 'Image '+(this.activeImage+1)+' of '+this.images.length);

			if (this.activeImage) $(this.preloadPrev).setProperty('src', this.images[this.activeImage-1][0]);
			if (this.activeImage != (this.images.length - 1)) 
				$(this.preloadNext).setProperty('src',  this.images[this.activeImage+1][0]);
			if (this.center.clientHeight != this.image.offsetHeight){
				this.fx.resize.start({height: this.image.offsetHeight});
				break;
			}
			this.step++;
		case 2:
			if (this.center.clientWidth != this.image.offsetWidth){
				this.fx.resize.start({width: this.image.offsetWidth, marginLeft: -this.image.offsetWidth/2});
				break;
			}
			this.step++;
		case 3:
			this.bottomContainer.setStyles({
				top: (this.top + this.center.getSize().size.y)+'px', 
				height: '0px', 
				marginLeft: this.center.getStyle('margin-left'), 
				display: ''
			});
			this.fx.image.start(1);
			break;
		case 4:
			this.center.style.backgroundColor = '#000';
			if (this.options.animateCaption){
				this.fx.bottom.set(-this.bottom.offsetHeight);
				this.bottomContainer.setStyle('height', '');
				this.fx.bottom.start(0);
				break;
			}
			this.bottomContainer.style.height = '';
		case 5:
			if (this.activeImage) this.prevLink.setStyle('display', '');
			if (this.activeImage != (this.images.length - 1)) this.nextLink.setStyle('display', '');
			this.step = 0;
		}
	},

	close: function(){
		this.fireEvent('onHide');
		if (this.step < 0) return;
		this.step = -1;
		if (this.preload){
			this.preload.onload = Class.empty;
			this.preload = null;
		}
		for (var f in this.fx) this.fx[f].stop();
		this.center.setStyle('display', 'none');
		this.bottomContainer.setStyle('display', 'none');
		this.fx.overlay.chain(this.setup.pass(false, this)).start(0);
		return;
	}
});
Lightbox.implement(new Options);
Lightbox.implement(new Events);
window.addEvent('domready', function(){new Lightbox()});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/3rdParty/Slimbox/slimbox.js,v $
$Log: slimbox.js,v $
Revision 1.14  2007/10/09 22:39:24  newtona
documented CNETAPI.Category.Browser, ObjectBrowser
doc tweaks on other files
rebuilding docs to javascript.cnet.com/docs

Revision 1.13  2007/09/05 18:36:58  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.12  2007/08/15 01:03:30  newtona
Added more event info for Autocompleter.js
Slimbox no longer adds css to the page if there aren't any images found for the instance
Iframeshim now exits quietly if you try and position it before the dom is ready
jsonp now handles having more than one request open at a time
removed a console.log statement from window.cnet.js (shame on me for leaving it there)

Revision 1.11  2007/07/27 19:51:19  newtona
Fixed some issues in IE for Mootools 1.11. Should still work fine with 1.0.

Revision 1.10  2007/06/29 21:58:23  newtona
dangit. missing semi-colon.

Revision 1.9  2007/06/29 21:33:58  newtona
adding some docs about that style property

Revision 1.8  2007/06/29 21:32:25  newtona
more re-writes of this 3rd party script. I've tested this more thoroughly and it's stable now.
added an option to style the modal layer (overlayStyles)

Revision 1.7  2007/06/29 19:28:09  newtona
numerous fixes; mostly adding mootools standard conventions like .setStyle instead of .style.<name> =  and addEvent
changed id namespaces for dom elements to classes

Revision 1.6  2007/06/29 00:22:47  newtona
refactoring to work with Mootools 1.0 for backwards support

Revision 1.5  2007/06/18 18:42:36  newtona
the overlay div is not added to the DOM if there are no lightbox links

Revision 1.4  2007/06/17 19:57:35  newtona
fixed an issue with using the document.links collection; now uses $$('a')

Revision 1.3  2007/06/15 15:52:41  newtona
docs update

Revision 1.2  2007/06/14 01:09:33  newtona
added zindex option; fixed a bug with sets.

Revision 1.1  2007/06/07 20:22:24  newtona
*** empty log message ***


*/