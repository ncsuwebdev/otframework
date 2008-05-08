/*
Script: popupdetails.js
Handles popup detail templated elements.

Dependancies:
	 mootools - 	<Moo.js>, <Utility.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>
	 cnet libraries - <dbug.js>, <simple.template.parser.js>, <stickyWin.js>, <stickyWinFx.js>
	 optional - <stickyWin.Modal.js>, <stickyWinFx.Drag.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
	
Class: PopupDetail
A PopupDetail instance is a <StickyWin> that uses <simpleTemplateParser> to parse details into a template, all of which is related to a dom element. When the user mouses over (or clicks, depending on the options you specify) the dom element, a <StickyWin> will appear near it (again, you specify the offset and whatnot). The <StickyWin> will then disappear if the user mouses out of the element, unless they move their mouse over the <StickyWin>, in which case it will hang around until they mouse out of that (this is yet another option).

Arguments:
html - html contents for the popup; can be a template (see <simpleTemplateParser>)
options - an object with key/value options

Options (optional unless noted otherwise):
	observer - (required) the dom element that this PopupDetail relates to
	observerAction - either "mouseover" or "click"; the action the user must perform 
		on the obsever to make the <StickyWin> appear; defaults to "mouseover"
	closeOnMouseOut - close the <StickyWin> when the user mouses out of the dom element
	  or the <StickyWin>; defaults to true
	linkPopup - make the whole <StickyWin> popup clickable; true will take the user to
	  the .href value of the observer element, a (string) url will use that instead, and
		false makes the <StickyWin> not clickable. Note that if you want the user to be
		able to interact with content in the <StickyWin> (even to select it), this must be
		false; defaults to false
	data - optional a key/value object of data to parse into the html of the popup; see <simpleTemplateParser>
	templateOptions - options for the <simpleTemplateParser>
	useAjax - get the data from an ajax request; defaults to false
	ajaxOptions - optional key/value object for use with the <Ajax> call; see that object for option details
	ajaxLink - url to use to get json values for data; defaults to the observer.href
	ajaxCache - (object) an object where the keys are urls to ajax content. If the PopupDetail object can find
		a match for its url (if it's using ajax) in the cache, it will use that content rather than hitting the
		server. Used in <PopupDetailCollection> but you can use it elsewhere if you like.
	htmlResponse - (boolean) if true, the response is expected to be the entire html of the content. 
		if false (the default) then the template schema is used and the ajax is expected to return json data.
	delayOn - (integer) time to wait after the user interacts with the observer before showing the popup; defaults to 0
	delayOff - (integer) time to wait after the user mouses out (if that is in effect) 
		the observer before hiding the popup; defaults to 0
	stickyWinOptions - the options object to pass to the instance of <stickWin>
	stickyWinToUse: - the StickyWin Class to use; either <StickyWin>, <StickyWinFx>, <StickyWinModal> or <StickyWinFxModal>; note, this value is *not* in quotes. It is not a string, it is a variable pointing to the class.
	showNow - show the PopupDetail on instantiation
	
*/

var PopupDetail = new Class({
	visible: false,
	observed: false,
	hasData: false,
	options: {
		observer: false, //or element
		observerAction: 'mouseenter', //or click
		closeOnMouseOut: true,
		linkPopup: false, //or true to use observer href, or url
		data: {}, //key/value parse to parse in to html
		templateOptions: {}, //see simple template parser
		useAjax: false,
		ajaxOptions:{
			method: 'get'
		},
		ajaxLink: false, //defaults to use observer.src
		ajaxCache: {},
		delayOn: 100,
		delayOff: 100,
		stickyWinOptions:{},
		stickyWinToUse: StickyWinFx,
		showNow: false,
		htmlResponse: false
	},
	initialize: function(html, options){
		this.setOptions(options);
		this.html = ($(html))?$(html).innerHTML:html||'';
		if(this.options.showNow) this.show.delay(this.options.delayOn, this);
		this.setUpObservers();
	},
	setUpObservers: function(){
		var opt = this.options; //saving bytes here
		if($(opt.observer) && opt.observerAction) {
			$(opt.observer).addEvent(opt.observerAction, function(){
				this.observed = true;
				this.show.delay(opt.delayOn, this);
			}.bind(this));
			if((opt.observerAction == "mouseenter" || opt.observerAction == "mouseover") && this.options.closeOnMouseOut){
				$(opt.observer).addEvent("mouseleave", function(){
					this.observed = false;
					this.hide.delay(opt.delayOff, this);
				}.bind(this));
			}
		}
		return this;
	},
	makePopup: function(){
		if(!this.stickyWin){
			var opt = this.options;//saving bytes
			if (opt.htmlResponse) this.content = this.data;
			else this.content = this.parseTemplate(this.html, opt.data);
			this.stickyWin = new opt.stickyWinToUse($merge(opt.stickyWinOptions, {
				relativeTo: opt.observer || document.body,
				showNow: false,
				content: this.content,
				allowMultipleByClass: true
			}));
			if($(opt.linkPopup) || $type(opt.linkPopup)=='string') {
				this.stickyWin.win.setStyle('cursor','pointer').addEvent('click', function(){
					window.location.href = ($type(url)=='string')?url:url.src;
				});
			}
			this.stickyWin.win.addEvent('mouseenter', function(){
				this.observed = true;
			}.bind(this));
			this.stickyWin.win.addEvent('mouseleave', function(){
				this.observed = false;
				if(opt.closeOnMouseOut) this.hide.delay(opt.delayOff, this);
			}.bind(this));
		}
		return this;
	},
	getContent: function(){
		try {
			new Ajax((this.options.ajaxLink || this.options.observer.href), $merge(this.options.ajaxOptions, {
					onComplete: this.show.bind(this)
				})
			).request();
		} catch(e) {
			dbug.log('ajax error on PopupDetail: %s', e);
		}
	},
/*	Property: show
		Makes the PopupDetail visible.
		
		Arguments:
		data - (optional) data to parse into the contents of the popup
		
		Note: 
		The data is really meant to be passed in for ajax requests. This is internal
		to the class; you should just call .show() with no arguments.
	*/
	show: function(data){
		var opt = this.options;
		if(data) this.data = data;
		if(this.observed && !this.visible) {
			if(opt.useAjax && !this.data) {
				var cachedVal = opt.ajaxCache[this.options.ajaxLink] || opt.ajaxCache[this.options.observer.href];
				if (cachedVal) return this.show(cachedVal);
				this.cursorStyle = $(opt.observer).getStyle('cursor');
				$(opt.observer).setStyle('cursor', 'wait');
				this.getContent();
				return false;
			} else {
				if(this.cursorStyle) $(opt.observer).setStyle('cursor', this.cursorStyle);
				if(opt.useAjax && !opt.htmlResponse) opt.data = Json.evaluate(this.data);
				this.makePopup();
				this.stickyWin.show();
				this.visible = true;
				return this;
			}
		}
		return this;
	},
/*	Property: hide
		Hides the popup.
	*/
	hide: function(){
		if(!this.observed){
			if(this.stickyWin)this.stickyWin.hide();
			this.visible = false;
		}
		return this;
	}
});
PopupDetail.implement(simpleTemplateParser);
PopupDetail.implement(new Options);
//legacy namespace
var popupDetail = PopupDetail;
/*
Class: PopupDetailCollection
Creates a collection of <PopupDetail> objects with the arrays of dom elements and data objects you specify, using a common template.

Arguments:
	options - an object containing options.

Options:
	details - (array, required) an array of objects containing key/value data for each popup (see below)
	observers - (array, required) the items you want the user to interact with to show the popup
	links - (array, optional) an array of links or of anchor tags to link the whole popup to; defaults to
		observer.href
	ajaxLinks - (array, optional) if in popupDetailOptions you specify useAjax = true, you must also pass
	  it a url; ajaxLinks is an array of links, one for each PopupDetail, to retrieve the data for the
		popup from the server
	useCache - (boolean) if true (default) will cache the ajax responses for all the <PopupDetail> objects. If
		multiple instances use the same url they will share the same response and the server will only be hit
		once per request.
	template - (string or dom element, required) the html template or an id of a DOM element 
		(or a DOM element reference) that contains it. This template will be parsed with the data 
		of each item (see <simpleTemplateParser>) and then displayed
	popupDetailOptions - (optional) key/value options object to be passed to each instance of
		<PopupDetail> that is created for each observer. Note that this class overrides this 
		options object with the data, observer, template, and link using the template specified 
		in the options object and the corresponding values in the details, observers, and links
		arrays you pass in.

	
Example:
(start code)
var fruitDetails = [
	{name: 'apple',
	 color: 'red'
	},
	{name: 'lemon',
	 color: 'yellow'
	}
];

<div id="popupDetailHTML">
	<dl>
		<dt>%name%</dt>
		<dd>%color%</dd>
	</dl>
</div>

<a href="http://all.about.apples.com">apples</a>
<a href="http://all.about.lemons.com">lemons</a>

window.onDomReady(function(){ //wait for the DOM to be ready
	new PopupDetailCollection({
		details: fruitDetails,
		observers: $$('a'), //all the links
		template: 'popupDetailHTML',

		//the rest here is entirely optional
		popupDetailOptions: {	//configure the PopupDetail object
			linkPopup: true,
			delayOn: 100,
			delayOff: 200,
			stickyWinOptions: {
				zIndex: 999,
				className: 'fruitStickyWin',
				position: 'upperRight',
				offset: {x: 100, y: 200},
				//limit the dimensions of the iframe shim to the first dl object
				//in the popup
				iframeShimSelector: 'dl' 
			}
		}
	});
});
(end)

Now when the user mouses over the link for 100ms, a popup will appear 100px to the right and 200px below the upper right corner of the link with the appropriate content.
 */
var PopupDetailCollection = new Class({
	options: {
			details: [],
			observers: [],
			links: [],
			ajaxLinks: [],
			useCache: true,
			template: '',
			popupDetailOptions: {}
	},
	initialize: function(options) {
		this.popupDetailObjs = [];
		this.cache = {};
		this.setOptions(options);
		var ln = this.options.ajaxLinks.length;
		if(ln <= 0) ln = this.options.details.length;
		if (this.options.observers.length != ln) 
			dbug.log("warning: observers and details are out of sync.");
		this.makePopupDetails();
	},
	makePopupDetails: function(){
		this.popupDetailObjs = this.options.observers.map(function(observer, index){
			var opt = this.options.popupDetailOptions;//saving bytes
			var pd = new PopupDetail(this.options.template, $merge(opt, {
				data: $pick(this.options.details[index], {}),
				observer: this.options.observers[index],
				linkItem: $pick(this.options.links[index], $pick(opt.linkItem, false)),
				ajaxLink: $pick(this.options.ajaxLinks[index], false),
				ajaxCache: (this.options.useCache)?this.cache:{}
			}));
			return pd;
		}, this);
	}
});
PopupDetailCollection.implement(new Options);

//legacy names
/*	Class: popupDetails (deprecated)
Fades in a DHTML popup with html formatted details.

IMPORTANT:
*This is deprecated; use <popupDetailCollection>.*

Arguments:
	options - an object containing options.

Options:
	details - the object of details (see below)
	observers - the items you want the user to interact with to show the popup
	links - an array of links or of anchor tags to link the whole popup to
	observerAction - either "mouseover" or "click" to determine what the user
	            has to do to show the item. Note: if you choose click, you must
							set your own overserver to hide the item. Defaults to mouseover.
	listName - (deprecated)
	template - the html template or an id of a DOM element that contains it. This template
							will be parsed with the data of each item and then displayed.
	offsety - the vertical offset of the item from the observer
	offsetx - the horizontal offset
	effectDurationOn - (deprecated)
	effectDurationOff - (deprecated)
	effectDelayOn - for mouseover, how long to wait after the user mousesover before you show the element
	effectDelayOff - for mouseout, how long to wait after the user mousesout before you show the element
	iframeShimSelector - the css selector *within your template* that should have 
											 an iframe shim under it to obscure select lists and the like.
	linkItems - boolean; make the whole popup clickable (defaults to true)
	
Example:
(start code)
	var titleDetails = [
		{name: 'apple',
		 color: 'red'
		},
		{name: 'lemon',
		 color: 'yellow'
		}
	];
	
	<div id="popupDetailHTML">
		<dl>
			<dt>%name%</dt>
			<dd>%color%</dd>
		</dl>
	</div>


	var dlListingPopups = null;
  window.onDomReady(function(){
		//instantiate our object
		dlListingPopups = new popDetailsList({
			details: titleDetails, 
			observers: $$("table#dl-tbl-list th.titleCell a.prod"), 
			links: prodLinks, 
			observeCorner: "upperRight", 
			observerAction: "mouseover", 
			listName: "dlListingPopups", 
			template: "popupDetailHTML", 
			offsety: -145, 
			offsetx: 30, 
			effectDurationOn: 250, 
			effectDurationOff: 150, 
			effectDelayOn: 500, 
			effectDelayOff: 1000, 
			iframeShimSelector: "div.popupDetails" 
		});
	}); 
(end)	*/
var popupDetails = new Class({
	initialize: function(options){
		var pdcOptions = Object.extend(options,{
				popupDetailOptions: {
					stickyWinOptions: {
						position: $pick(options.observeCorner, 'upperLeft'),
						offset: {
							x: options.offsetx || 0,
							y: options.offsety || 0
						},
						useIframeShim: (options.iframeShimSelector)?true:false
					}
				},
				delayOn: $pick(options.effectDelayOn, 0),
				delayOff: $pick(options.effectDelayOff, 0)
			});
		var pdc = new popupDetailCollection(pdcOptions);
		return pdc;
	}
});
var popDetailsList = popupDetails;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/popupdetails.js,v $
$Log: popupdetails.js,v $
Revision 1.17  2007/09/24 22:10:19  newtona
fixed a bug in popupdetails;
StickyWin*.Ajax.update() now returns the instance (return this);

Revision 1.16  2007/09/24 21:07:45  newtona
hey, semi-colons!

Revision 1.15  2007/09/24 20:55:49  newtona
new file: StickyWin.Ajax - adds ajax support to all stickywin classes (creates new classes, just append .Ajax to any of the existing ones)
updated redball common full to include StickyWin.Ajax
date.picker, product.picker - updated syntax to use Element.empty
form.validator - now passes along the event object to the onFormValidate event so that the form submit event can be stopped if you like
popupdetails - added html response support; you can now return the html you wish to display rather than a json object; only applies to ajax. Also added a cache so that multiple requests are not made for the same url.
stickyWinHTML - ractored so that options are now, you know, *optional*
MooScroller - added support for width option for horizontal scrolling

Revision 1.14  2007/09/07 22:19:28  newtona
popupdetails: updating options handling methodology
stickyWinFx: fixed a bug where, if you were fast enough, you could introduce a flicker bug - this is hard to produce so most people probably hadn't seen it

Revision 1.13  2007/08/31 01:07:55  newtona
updating popupdetails to use mouseenter instead of mouseover for default behavior

Revision 1.12  2007/03/08 23:29:31  newtona
date picker: strict javascript warnings cleaned up
popup details strict javascript warnings cleaned up
product.picker: strict javascript warnings cleaned up, updating input now fires onchange event
confirmer: new file

Revision 1.11  2007/03/08 02:42:32  newtona
removed $copy; old and deprecated function

Revision 1.10  2007/02/08 01:29:36  newtona
fixed syntax error

Revision 1.9  2007/02/07 20:51:41  newtona
implemented Options class
implemented Events class
StickyWin now uses Element.position

Revision 1.8  2007/01/29 23:51:10  newtona
using $copy now

Revision 1.7  2007/01/26 05:48:45  newtona
syntax update for mootools 1.0

Revision 1.6  2007/01/23 20:54:43  newtona
numerous bug fixes. tested and seems stable now.

Revision 1.5  2007/01/22 22:00:15  newtona
numerous bug fixes to modalizer, stickywin, and popupdetails
updated for mootools 1.0
fixed date validation in form.validator

Revision 1.4  2007/01/19 01:22:21  newtona
fixed a few syntax errors

Revision 1.3  2007/01/11 22:31:23  newtona
doc changes

Revision 1.2  2007/01/11 20:55:23  newtona
changed the way options are set, split up stickywin into 4 files, refactored popupdetails to use stickywin and modalizer

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.5  2007/01/09 01:25:24  newtona
changed $S to $$

Revision 1.4  2007/01/05 18:08:19  newtona
swapped Event.onDomReady for Window.onDomReady,
removed template parsing into new class (SimpleTemplateParser) and integrated
removed references to Fx.Opacity
fixed bug with setStyle command

Revision 1.3  2006/11/04 00:53:25  newtona
added better options handling, documentation

Revision 1.2  2006/11/03 18:56:06  newtona
added some documentation

Revision 1.1  2006/11/02 21:28:08  newtona
checking in for the first time.


*/
