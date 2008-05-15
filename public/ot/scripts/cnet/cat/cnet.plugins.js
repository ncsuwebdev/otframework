/*	Script: randomValueCookieMaker.js
		This script assigns a user a cookie with a random value within a specified range; useful for a/b testing.
		
		Dependancies:
			 mootools - 	<Moo.js>, <String.js>, <Cookie.js>, <Common.js>, <Utilities.js>, <Function.js>
	
		Author:
			Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
		
		Class: RandomValueCookieMaker
		Assigns a user a cookie with a random value within a specified range; useful for a/b testing
		
		Arguments:
		options - the options object of key/value options

		Options:
		cookieName - (string, required) a unique name for the cookie.
		limit - (integer) the highest random number to generate; defaults to 10.
		days - (integer) how long to store the cookie; defaults to 999.
		domain - (string) the domain to assign to the cookie; optional.		
		
		Property:
		val - the value of the random cookie
		
		Example:
(start code)
var myRndTest = new RandomValueCookieMaker({
	cookieName: 'myRandomCookie', //a unique name for this cookie.
	limit: 99, //give me 0 through 99
	days: 1, //let's only save it for a day
	domain: 'cnet.com' //let's set it to cnet.com 
										 //so subdomains can get the cookie
});

if(myRndTest.val > 90) //only do this for 10% of users...
(end)
	*/

	var RandomValueCookieMaker = new Class({
		options: {
			cookieName: false,
			limit: 10,
			days: 999,
			domain: false
		},
		initialize: function(options) {
			this.setOptions(options);
			if(this.options.cookieName) this.verify();
			else {
				dbug.log('you must specify a cookie name.');
				return;
			}
		},
		verify: function() {
			this.val = Cookie.get(this.options.cookieName);
			if (!$chk(parseInt(this.val))) {
				this.val = this.makeRand();
				this.saveVal();
			}
		},
/*	Property: setVal
		Sets the cookie to a specified value.
		
		Arguments:
		val - (integer) the value to set the cookie to	*/
		saveVal: function(val) {
			this.val = $pick(val, this.val);
			if (this.options.domain) Cookie.set(this.options.cookieName, this.val, {duration:this.options.days, domain:this.options.domain});
			else Cookie.set(this.options.cookieName, this.val, this.options.days);
		},
		makeSeed: function() {
	     return ((new Date().getTime()*9301+49297) % 233280)/(233280.0);
		},
/*	Property: makeRand
		Returns a random number between 0 and the limit set in the options.	*/
		makeRand: function() {
	     return Math.ceil(this.makeSeed()*this.options.limit);
		}
	});
	RandomValueCookieMaker.implement(new Options);
	/*	legacy namespace	*/
	var randomValueCookieMaker = RandomValueCookieMaker;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/utilities/randomValueCookieMaker.js,v $
$Log: randomValueCookieMaker.js,v $
Revision 1.8  2007/04/13 19:06:11  newtona
dependency update in the docs

Revision 1.7  2007/03/28 18:09:03  newtona
removing $type.isNumber dependencies

Revision 1.6  2007/03/20 19:23:25  newtona
fixing javascript strict warnings

Revision 1.5  2007/03/08 23:31:22  newtona
strict javascript warnings cleaned up
removed deprecated dbug loadtimers
dbug enables on debug.cookie()

Revision 1.4  2007/01/26 05:56:03  newtona
syntax update for mootools 1.0
docs update

Revision 1.3  2007/01/22 22:50:25  newtona
updated cookie.set syntax

Revision 1.2  2007/01/22 21:54:46  newtona
updated docs to require cookieName

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.4  2006/11/14 02:06:23  newtona
fixed some syntax bugs

Revision 1.3  2006/11/13 23:53:04  newtona
added cvs footer


*/
/*	Script: simple.template.parser.js
		Provides functionality for very simple template parsing; for more complex template parsing, use TrimPath's excellent Javascript Templates (JST): http://trimpath.com/project/wiki/JavaScriptTemplates.

		Dependencies:
		Moo - <Moo.js>, <Utility.js>, <Function.js>, <String.js>
	
		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)
		
		Object: simpleTemplateParser
		This object provides functionality for very simple template parsing; for more complex template parsing, use TrimPath's excellent Javascript Templates (JST): http://trimpath.com/project/wiki/JavaScriptTemplates. It can be used on its own or implemented into a class.
	*/

var simpleTemplateParser = {
		STP: {},
/*	Property: parseTemplate
		Parses a template with the values of an object, substituting those values for all instances of the keys in the object found within the template.

		Arguments: 
		template - a string to parse
		object - the object with your key/value pairs
		regexOptions - the options for the regex replace; defaults to 'ig' (ignore case, global replace)
		wrappers - an object with the before and after strings that are on either side of your keys (see example);
			defaults to {before: "%", after: "%"}

		Example:
(start code)
<textarea id="myTemplate">
	<p>This is some html that lets me subsitute things.</p>
	<ul>
		<li>%firstThing%</li>
		<li>%secondThing%</li>
		<li>%thirdThing%</li>
	</ul>
</textarea>
<script>
	var myTemplate = $('myTemplate').innerHTML;
	var myObject = {
		firstThing: 'hi there',
		secondThing: 'howzit goin?',
		thirdThing: 'really? me too!'
	}
	var parsed = simpleTemplateParser.parseTemplate(myTemplate, myObject);
</script>(end)
	*/
		parseTemplate: function(template, object, regexOptions, wrappers) {
			var STP = this.STP;
			STP.template = template;
			STP.object = object;
			STP.regexOptions = $pick(regexOptions, 'ig');
			STP.wrappers = $pick(wrappers, {before:'%', after:'%'});
			return STP.result = this.runParser(STP.object, STP.template, STP.regexOptions);
		},
		runParser: function(object, string, regexOptions){
			for(value in object){
				switch($type(object[value])){
					case 'string':
						string = this.tmplSubst(value, object[value], string, regexOptions);
						break;
					case 'number':
						string = this.tmplSubst(value, object[value], string, regexOptions);
						break;
					case 'object':
						string = this.runParser(object[value]);
						break;
					case 'array':
						string = this.tmplSubst(value, object[value].toString(), string, regexOptions);
						break;
				}
			}
			return string;
		},
		tmplSubst: function(key, value, string, regexOptions){
			return string.replace(new RegExp(this.STP.wrappers.before+key+this.STP.wrappers.after, 'gi'), value);
		}
	};
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/utilities/simple.template.parser.js,v $
$Log: simple.template.parser.js,v $
Revision 1.4  2007/06/07 18:43:37  newtona
added CSS to autocompleter.js
removed string.cnet.js dependencies from template parser and stickyWin.default.layout.js

Revision 1.3  2007/03/02 01:32:52  newtona
swapped out string.replace with string.replaceAll

Revision 1.2  2007/01/26 05:56:03  newtona
syntax update for mootools 1.0
docs update

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.2  2007/01/09 01:25:47  newtona
docs syntax fix

Revision 1.1  2007/01/05 18:55:02  newtona
first check in


*/
/*
Script: fixpng.js

Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>
	
Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>

		Function: fixPNG
		this will make transparent pngs show up correctly in IE. This function 
		is based almost entirely on the function found here: 
		<http://homepage.ntlworld.com/bobosola/pnginfo.htm>
		
		Arguments:
		el - the image element (or id) or dom element with a background image (or id) to fix
		
		Note: 
		there is an instances of this already set to fire onDOMReady that
		will fix any png files with the class "fixPNG". This means any producer
		can just give the class "fixPNG" to any img tag and they are set BUT, the
		ping will look wrong until the DOM loads, which may or may not be noticeable.
		
		The alternative is to embed the call right after the image like so:
		
		><img src="png1.png" width="50" height="50" id="png1">
		><img src="png2.png" width="50" height="50" id="png2">
		><script>
		>	$$('#png1', '#png2').each(function(png) {fixPNG(png);});
		>	//OR
		>	fixPNG('png1');
		>	fixPNG('png2');
		></script>
*/

function fixPNG(el) {
	try {
		if (window.ie6){
			el = $(el);
			if (!el) return el;
			if (el.getTag() == "img" && el.getProperty('src').test(".png")) {
				var vis = el.isVisible();
				try { //safari sometimes crashes here, so catch it
					dim = el.getSize();
				}catch(e){}
				if(!vis){
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
					dim = el.getSize(); //works now, because the display isn't none
					this.setStyles(before); //put it back where it was
					el.hide();
				}
				var replacement = new Element('span', {
					id:(el.id)?el.id:'',
					'class':(el.className)?el.className:'',
					title:(el.title)?el.title:(el.alt)?el.alt:'',
					styles: {
						display: vis?'inline-block':'none',
						width: dim.size.x+'px',
						height: dim.size.y+'px',
						filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader (src='" 
							+ el.src + "', sizingMethod='scale');"
					},
					src: el.src
				});
				if(el.style.cssText) {
					try {
						var styles = {};
						var s = el.style.cssText.split(';');
						s.each(function(style){
							var n = style.split(':');
							styles[n[0]] = n[1];
						});
						replacement.setStyle(styles);
					} catch(e){ dbug.log('fixPNG1: ', e)}
				}
				if(replacement.cloneEvents) replacement.cloneEvents(el);
				el.replaceWith(replacement);
			} else if (el.getTag() != "img") {
			 	var imgURL = el.getStyle('background-image');
			 	if (imgURL.test(/\((.+)\)/)){
			 		el.setStyles({
			 			background: '',
			 			filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled='true', sizingMethod='crop', src='" + imgURL.match(/\((.+)\)/)[1] + "')"
			 		});
			 	};
			}
		}
	} catch(e) {dbug.log('fixPNG2: ', e)}
};
if(window.ie6) window.addEvent('domready', function(){$$('img.fixPNG').each(fixPNG)});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/browser.fixes/fixpng.js,v $
$Log: fixpng.js,v $
Revision 1.10  2007/10/30 19:01:48  newtona
doc update

Revision 1.9  2007/10/30 18:59:55  newtona
fixpng.js now supports background png images
doc typo in setAssetHref.js

Revision 1.8  2007/08/25 00:05:33  newtona
moved ProductToolbar to global implementations
handled ie6 slightly differently in fixPNG, added some dbug lines for when it failes
updated commerce global cat file for new location of ProductToolbar
rebuilt redball.common.full

Revision 1.7  2007/08/03 22:01:14  newtona
refactored fixPng; the big change is that it now clones events from the old element to the new one.

Revision 1.6  2007/07/27 19:55:36  newtona
removing dependency on Element.shortcuts.js

Revision 1.5  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.4  2007/05/16 20:17:52  newtona
changing window.onDomReady to window.addEvent('domready'

Revision 1.3  2007/01/26 05:46:32  newtona
syntax update for mootools 1.0

Revision 1.2  2007/01/19 01:21:47  newtona
changed event.ondomready > window.ondomready

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.3  2007/01/09 01:26:38  newtona
changed $S to $$

Revision 1.2  2006/11/02 21:26:42  newtona
checking in commerce release version of global framework.

notable changes here:
cnet.functions.js is the only file really modified, the rest are just getting cvs footers (again).

cnet.functions adds numerous new classes:

$type.isNumber
$type.isSet
$set

*//*
Script: IframeShim.js
Iframe shim class for hiding elements below a floating DOM element.

Dependancies:
	 mootools - <Moo.js>, <Utility.js>, <Common.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>


Class: IframeShim
		There are two types of elements that (sometimes) prohibit you from 
		positioning a DOM element over them: some form elements and some
		flash elements. The two options you have are:
			- to hide these elements when your dom is going to be over them; 
			this works if you know your DOM element is going to completely
			obscure that element
			
			- an iframe shim - where you put an iframe below your element but
			ABOVE the form/flash element. more details here:
			http://www.macridesweb.com/oltest/IframeShim.html
			
		The IframeShim class handles a lot of the dirty work for you.

			Arguments:
			element -  (required; DOM element or its id) the element you want to put this shim under
			display -  (boolean; optional) display the shim on instantiation; defaults to false
			name -  (string; optional) the id you want to give the new DOM element of the iframe shim; gets "_shim" added to it
			zindex -  (integer; optional) the index of the shim; optional, default is 1 less than the element
			margin -  (integer; optional) make the iframe smaller than the element to give a buffer (for 
							things like shadows)
			offset -  (object: {x:#, y:#}; optional) move the iframe up/down, left/right relative to 
							the element
			className - (string; optional) className for the shim; defaults to "iframeShim"
			browsers - (boolean; optional) allows you to specify the browsers that the iframe should show up for;
							defaults to ie6 or gecko on a mac (window.ie6 || (window.gecko && navigator.userAgent.test('mac', 'i'))). 
							Example usage: *browsers: window.ie6 || window.khtml* //will show for safari, konqueror, and ie6

			Events:
			onInject - (callback) function executed when the iframe is added to the DOM (which waits until window.onload)
		
		then, when you make your floating DOM show up you just execute .hide() or .show()
		to make the shim do its magic. You can also call .position() if the element the
		shim is supposed to be under happens to move.
		
		example:
		
		> <div id="myFloatingDiv">stuff</div>
		> <script>
		> 	var myFloatingDivShim = new IframeShim({
		> 		element: 'myFloatingDiv',
		> 		display: false,
		> 		name: 'myFloatingDivShimId'
		> 	});
		> 	function showMyFloatingDiv(){
		> 		$('myFloatingDiv').show();
		> 		myFloatingDivShim.show();
		> 	}
		> </script>
		
		See also <hide>, <show>, <position>
	*/
	
var IframeShim = new Class({
	options: {
		element: false,
		name: '',
		className:'iframeShim',
		display:false,
		name: '',
		zindex: false,
		margin: 0,
		offset: {
			x: 0,
			y: 0
		},
		browsers: (window.ie6 || (window.gecko && navigator.userAgent.test('mac', 'i')))
	},
	initialize: function (options){
		this.setOptions(options);
		//legacy
		if(this.options.offset && this.options.offset.top) this.options.offset.y = this.options.offset.top;
		if(this.options.offset && this.options.offset.left) this.options.offset.x = this.options.offset.left;
		this.element = $(this.options.element);
		if(!this.element) return;
		else this.makeShim();
		return;
	},
	makeShim: function(){
		this.shim = new Element('iframe');
		this.id = (this.options.name || new Date().getTime()) + "_shim";
		if(this.element.getStyle('z-Index').toInt()<1 || isNaN(this.element.getStyle('z-Index').toInt()))
			this.element.setStyle('z-Index',5);
		var z = this.element.getStyle('z-Index')-1;
		
		if($chk(this.options.zindex) && 
			 this.element.getStyle('z-Index').toInt() > this.options.zindex)
			 z = this.options.zindex;
			
 		this.shim.setStyles({
			'position': 'absolute',
			'zIndex': z,
			'border': 'none',
			'filter': 'progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)'
		}).setProperties({
			'src':'javascript:void(0);',
			'frameborder':'0',
			'scrolling':'no',
			'id':this.id
		}).addClass(this.options.className);
		
		var inject = function(){
			this.shim.injectInside(document.body);
			if(this.options.display) this.show();
			else this.hide();
			this.fireEvent('onInject');
		};
		if(this.options.browsers){
			if(window.ie && !IframeShim.ready) {
				window.addEvent('load', inject.bind(this));
			} else {
				inject.bind(this)();
			}
		}
	},

/*	
		Property: position
		This will reposition the iframe element. Call this when you move or resize
		the iframe element.
	*/
	position: function(shim){
		if(!this.options.browsers || !IframeShim.ready) return;
		var wasVis = this.element.getStyle('display')!='none';
		if(!wasVis) this.element.setStyle('display','block');
		var size = this.element.getSize().size;
		var pos = this.element.getPosition();
		if(! wasVis) this.element.setStyle('display','none');
		if($type(this.options.margin)){
			size.x = size.x-(this.options.margin*2);
			size.y = size.y-(this.options.margin*2);
			this.options.offset.x += this.options.margin; 
			this.options.offset.y += this.options.margin;
		}
		//offset.x+=100;// ******* This is my change ********
 		this.shim.setStyles({
			'width': size.x + 'px',
			'height': size.y + 'px'
		}).setPosition({
			relativeTo: this.element,
			offset: this.options.offset
		});
	},
/*	
		Property: hide
		This will hide the IframeShim object. If you don't call this when you
		hide the element that's over the flash or select list, then that thing
		will still be hidden.
	*/
	hide: function(){
		if(!this.options.browsers) return;
		this.shim.setStyle('display','none');
	},

/*	
		Property: show
		This will obscure any form elements or flash elements below the iframe
		shim element. Call this when you show your floating element.
	*/
	show: function(){
		if(!this.options.browsers) return;
		this.shim.setStyle('display','block');
		this.position();
	},
/*	
		Property: remove
		This will remove the iframe from the DOM.
	*/
	remove: function(){
		if(!this.options.browsers) return;
		this.shim.remove();
	}
});
IframeShim.implement(new Options);
IframeShim.implement(new Events);
//legacy namespace
var iframeShim = IframeShim;
window.addEvent('load', function(){
	IframeShim.ready = true;
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/browser.fixes/IframeShim.js,v $
$Log: IframeShim.js,v $
Revision 1.22  2007/08/20 18:11:49  newtona
Iframeshim: better handling for methods when the shim isn't ready
stickyWin: fixed a bug for the cssClass option in stickyWinHTML
html.table: push now returns the dom elements it creates
jsonp: fixed a bug; request no longer requires a url argument (my bad)
Fx.SmoothMove: just tidying up some syntax.
element.dimensions: updated getDimensions method for computing size of elements that are hidden; no longer clones element

Revision 1.21  2007/08/15 01:03:32  newtona
Added more event info for Autocompleter.js
Slimbox no longer adds css to the page if there aren't any images found for the instance
Iframeshim now exits quietly if you try and position it before the dom is ready
jsonp now handles having more than one request open at a time
removed a console.log statement from window.cnet.js (shame on me for leaving it there)

Revision 1.20  2007/08/08 18:22:06  newtona
fixed a bug with Element.getDimensions (which affected Fx.SmoothMove, Fx.SmoothShow, Element.setPosition, and the bazillion other things that use it). would only show up under certain CSS layout situations.

Revision 1.19  2007/05/03 18:24:24  newtona
iframeshim: removed a dbug line
modalizer: only hide select lists for browsers that need it
product picker: added a try/catch, updated cnet api link/code

Revision 1.18  2007/04/12 19:21:11  newtona
iframeshim now defaults to only activate for ie6 AND Firefox on a mac

Revision 1.17  2007/04/12 17:09:32  newtona
*** empty log message ***

Revision 1.16  2007/04/12 17:03:28  newtona
iframeshim now defaults to only activate for ie6

Revision 1.15  2007/04/11 23:11:51  newtona
because IframeShim appends the iframe to the document.body, in IE iframe now waits for window.onload

Revision 1.14  2007/04/09 19:04:18  newtona
fixed a binding problem

Revision 1.13  2007/04/05 00:13:12  newtona
local.vars.js: removing $type.isNumber dependency
login.status.js: no change; fixed typo in docs
search.functions.js: removing $type.isNumber dependency
stickyWinDefaultLayout: infinite buttons!
iframeShim.js: fixed an ie bug that caused it to abort the page

Revision 1.12  2007/03/26 18:30:10  newtona
iframeShim: fixed reference to options (should be this.options)
element.cnet: removed some dbug lines

Revision 1.11  2007/03/23 21:18:42  newtona
fixed reference to options (should be this.options)

Revision 1.10  2007/03/23 20:19:48  newtona
Iframeshim: added className; updated docs
StickyWin: added edge support (see Element.setPosition)

Revision 1.9  2007/03/23 17:17:37  newtona
iframe in iframeshim now gets it's id set (again)

Revision 1.8  2007/03/20 21:02:51  newtona
docs update

Revision 1.7  2007/03/20 19:22:50  newtona
continued refactoring; fixing some IE inconsistencies.

Revision 1.6  2007/03/16 05:24:36  newtona
refactored and cleaned up.

Revision 1.5  2007/02/23 20:02:51  newtona
adjusted z-index logic so that the iframe is always a positive number

Revision 1.4  2007/02/23 18:47:29  newtona
iframe target now gets $() around it.

Revision 1.3  2007/02/07 20:49:21  newtona
implemented Options class

Revision 1.2  2007/01/22 21:09:24  newtona
updated docs for namespace change (IframeShim.js)

Revision 1.1  2007/01/22 21:08:30  newtona
renamed from iframeshim.js

Revision 1.3  2007/01/22 19:54:59  newtona
removed browser.sniffer - this stuff is in mootools 1.0
renamed iframeShim to IframeShim

Revision 1.2  2007/01/11 20:45:49  newtona
fixed syntax error with setProperties

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.3  2007/01/05 19:30:37  newtona
removed any dependencies on cnet libraries; now only depends on mootools.

Revision 1.2  2006/11/02 21:26:42  newtona
checking in commerce release version of global framework.

notable changes here:
cnet.functions.js is the only file really modified, the rest are just getting cvs footers (again).

cnet.functions adds numerous new classes:

$type.isNumber
$type.isSet
$set

*//*	
	Script: form.validator.js
	A css-class based form validation system.
	
	Dependencies:
	Mootools - <Moo.js>, <Utility.js>, <Common.js>, <Element.js>, <Function.js>, <Event.js>, <String.js>, <Fx.Base.js>, 
			<Window.Base.js>, <Fx.Style.js>, <Fx.Styles.js>, <Dom.js>
			
	CNET - optional: <Fx.SmoothShow.js>
			
	Authors:
		Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
		Based on validation.js by Andrew Tetlaw (http://tetlaw.id.au/view/blog/really-easy-field-validation-with-prototype)

	Class: InputValidator
	This class contains functionality to test a field for various criteria and also to generate 
	an error message when that test fails.
	
	Arguments:
	className - a className that this field will be related to (see example below);
	options - an object with name/value pairs.
	
	Options:
	errorMsg - a message to display; see section below for details.
	test - a function that returns true or false
	
	errorMsg:
	The errorMsg option can be any of the following
	
		string - the message to display if the field fails validation
		boolean false - do not display a message at all
		function - a function to evaluate that returns either a string or false.
			This function will be passed two parameters: the field being evaluated and
			any properties defined for the validator as a className (see examples below)
	
	test:
	The test option is a function that will be passed the field being evaluated and
	any properties defined for the validator as a className (see example below); this
	function must return true or false.

	Examples:
(start code)
//html code
<input type="text" name="firstName" class="required" id="firstName">
//simple validator
var isEmpty = new InputValidator('required', {
	errorMsg: 'This field is required.',
	test: function(field){
		return ((element.getValue() == null) || (element.getValue().length == 0));
	}
});
isEmpty.test($("firstName")); //true if empty
isEmpty.getError($("firstName")) //returns "This field is required."

//two complex validators
<input type="text" name="username" class="minLength maxLength" validatorProps="{minLength:10, maxLength:100}" id="username">

var minLength = new InputValidator ('minLength', {
	errorMsg: function(element, props){
		//props is {minLength:10, maxLength:100}
		if($type(props.minLength))
			return 'Please enter at least ' + props.minLength + ' characters (you entered ' + element.value.length + ' characters).';
		else return '';
	}, 
	test: function(element, props) {
		//if the value is >= than the minLength value, element passes test
		return (element.value.length >= $pick(props.minLength, 0));
		else return false;
	}
});

minLength.test($('username'));

var maxLength = new InputValidator ('maxLength', {
	errorMsg: function(element, props){
		//props is {minLength:10, maxLength:100}
		if($type(props.maxLength))
			return 'Please enter no more than ' + props.maxLength + ' characters (you entered ' + element.value.length + ' characters).';
		else return '';
	}, 
	test: function(element, props) {
		//if the value is <= than the maxLength value, element passes test
		return (element.value.length <= $pick(props.maxLength, 10000));
	}
});(end)
	*/

var InputValidator = new Class({
	initialize: function(className, options){
		this.setOptions({
			errorMsg: 'Validation failed.',
			test: function(field){return true}
		}, options);
		this.className = className;
	},
/*	Property: test
		Tests a field against the validator's rule(s).
		
		Arguments:
		field - the form input to test
		
		Returns:
		true - the field passes the test
		false - it does not pass the test
	*/
	test: function(field){
		if($(field)) return this.options.test($(field), this.getProps(field));
		else return false;
	},
/*	Property: getError
		Retrieves the error message for the validator.
		
		Arguments:
		field - the form input to test
		
		Returns:
		The error message or the boolean false if no message is meant to be returned.
	*/
	getError: function(field){
		var err = this.options.errorMsg;
		if($type(err) == "function") err = err($(field), this.getProps(field));
		return err;
	},
	getProps: function(field){
		if($(field) && $(field).getProperty('validatorProps')){
			try {
				return Json.evaluate($(field).getProperty('validatorProps'));
			}catch(e){ return {}}
		} else {
			return {}
		}
	}
});
InputValidator.implement(new Options);


/*	Class: FormValidator
		Evalutes an entire form against all the validators that are set up, displaying messages
		and returning a true/false response for the evaluation of the entire form.
		
		An instance of the FormValidator class will test each field and then behave according to
		the options passed in.
		
		Arguments:
		form - the form to evaluate
		options - an object with name/value pairs
		
		Options:
		fieldSelectors - the selector for fields to include in the validation;
				defaults to: "input, select, textarea"
		useTitles - use the titles of inputs for the error message; overrides
				the messages defined in the InputValidators (see <InputValidator>); defaults to false
		evaluateOnSubmit - validate the form when the user submits it; defaults to true
		evaluateFieldsOnBlur - validate the fields when the blur event fires; defaults to true
		evaluateFieldsOnChange - validate the fields when the change event fires; defaults to true
		serial - (boolean) if one field fails validation, do not validate other fields unless 
					their contents actually change (instead of on blur); defaults to true
		warningPrefix - (string) prefix to be added to every warning; defaults to "Warning: "
		errorPrefix - (string) prefix to be added to every error; defaults to "Error: "
		onFormValidate - function to execute when the form validation completes; this function
			is passed three arguments: a boolean (true if the form passed validation), the form element, 
			and the onsubmit event object if there was one (else, passed undefined)
		onElementValidate - function to execute when an input element is tested; this function
			is passed two arguments: a boolean (true if the form passed validation) and the input element
		
		Example:
(start code)var myFormValidator = new FormValidator($('myForm'), {
	onFormValidate: myFormHandler,
	useTitles: true
});(end)

		Note: 
		FormValidator must be configured with <Validator> objects; see below for details as well as a list of built-in validators. Each <Validator> will be applied to any input that matches its className within the elements of the form that match the fieldSelectors option.

		Using Warnings:
		Each <Validator> can also be used to generate warnings. Warnings still show error messages, but do not prevent the form from being submitted. Warnings can be applied in two ways.
		warn per validator - You can specify any validator as a warning by prefixing "warn-" to the class name. So, for example, if you have a validator called "validate-numbers" you can add the class "warn-validate-numbers" and a warning will be offered rather than an error. The validator will not prevent the form from submitting.
		warn per field - You can also ignore all the validators for a given field. You can add the class "warnOnly" to set all it's validators to present warnings only or you can add the class "ignoreValidation" to the field to turn all the validators off. Note that the FormValidator class has methods do this for you: see <FormValidator.ignoreField> and <FormValidator.enforceField>.
	*/
var FormValidator = new Class({
	options: {
		fieldSelectors:"input, select, textarea",
		useTitles:false,
		evaluateOnSubmit:true,
		evaluateFieldsOnBlur: true,
		evaluateFieldsOnChange: true,
		serial: true,
		warningPrefix: "Warning: ",
		errorPrefix: "Error: ",
		onFormValidate: function(isValid, form){},
		onElementValidate: function(isValid, field){}
	},
	initialize: function(form, options){
		this.setOptions(options);
		try {
			this.form = $(form);
			if(this.options.evaluateOnSubmit) this.form.addEvent('submit', this.onSubmit.bind(this));
			if(this.options.evaluateFieldsOnBlur) this.watchFields();
		}catch(e){//console.log('error: %s', e);
		}
	},
	getFields: function(){
		return this.fields = this.form.getElementsBySelector(this.options.fieldSelectors)
	},
	watchFields: function(){
		try{
			this.getFields().each(function(el){
					el.addEvent('blur', this.validateField.pass([el, false], this));
				if(this.options.evaluateFieldsOnChange)
					el.addEvent('change', this.validateField.pass([el, true], this));
			}, this);
		}catch(e){//console.log('error: %s', e);
		}
	},
	onSubmit: function(event){
		if(!this.validate(event)) new Event(event).stop();
		else {
			this.stop();
			this.reset();
		}
	},
/*	Property: reset
		Removes all the error messages from the form.
	*/
	reset: function() {
		this.getFields().each(this.resetField, this);
	}, 
/*	Property: validate
		Validates all the inputs in the form; note that this function is called on submit unless
		you specify otherwise in the options.
		
		Arguments:
		event - (optional) the submit event
	*/
	validate : function(event) {
		var result = this.getFields().map(function(field) { return this.validateField(field, true); }, this);
		result = result.every(function(val){
			return val;
		});
		this.fireEvent('onFormValidate', [result, this.form, event]);
		return result;
	},
/*	Property: validateField
		Validates the value of a field against all the validators.
		
		Arguments:
		field - the input element to evaluate
		force - (boolean; optional) if false (or undefined) and options.serial==true, the validation does not occur
	*/
	validateField: function(field, force){
		if(this.paused) return true;
		field = $(field);
		var result = true;
		var failed = this.form.getElement('.validation-failed');
		var warned = this.form.getElement('.warning');
		//if the field is defined
		//if there aren't any failed
		//or if there are failed and it's not serial
		//or force
		//then validate
		if(field && (!failed || force || field == failed || (failed && !this.options.serial))){
			var validators = field.className.split(" ").some(function(cn){
				return this.getValidator(cn);
			}, this);
			result = field.className.split(" ").map(function(className){
				return this.test(className,field);
			}, this);
			result = result.every(function(val){
				return val;
			});
			if (validators && !field.hasClass('warnOnly')){
				if(result) field.addClass('validation-passed').removeClass('validation-failed');
				else field.addClass('validation-failed').removeClass('validation-passed');
			}
			if(!warned || force || (warned && !this.options.serial)) {
				var warnings = field.className.split(" ").some(function(cn){
					if(cn.test('^warn-') || field.hasClass('warnOnly')) return this.getValidator(cn.replace(/^warn-/,""));
					return null;
				}, this);
				field.removeClass('warning');
				var warnResult = field.className.split(" ").map(function(cn){
					if(cn.test('^warn-') || field.hasClass('warnOnly')) return this.test(cn.replace(/^warn-/,""), field, true);
					return null;
				}, this);
			}
		}
		return result;
	},
	getPropName: function(className){
		return '__advice'+className;
	},
/*	Property: test
		Tests a field against a specific validator.
		
		Arguments:
		className - the className associated with the validator
		field - the input element
		warn - (boolean; optional) if set to true, test will add a warning advice message if 
				the validator fails, but will always return valid regardless of the input.
	*/
	test: function(className, field, warn){
		if(field.hasClass('ignoreValidation')) return true;
		warn = $pick(warn, false);
		if(field.hasClass('warnOnly')) warn = true;
		field = $(field);
		var isValid = true;
		if(field) {
			var validator = this.getValidator(className);
			if(validator && this.isVisible(field)) {
				isValid = validator.test(field);
				//if the element is visible and it failes to validate
				if(!isValid && validator.getError(field)){
					if(warn) field.addClass('warning');
					var advice = this.makeAdvice(className, field, validator.getError(field), warn);
					this.insertAdvice(advice, field);
					this.showAdvice(className, field);
				} else this.hideAdvice(className, field);
				this.fireEvent('onElementValidate', [isValid, field]);
			}
		}
		if(warn) return true;
		return isValid;
	},
	showAdvice: function(className, field){
		var advice = this.getAdvice(className, field);
		if(advice && !field[this.getPropName(className)] && (advice.getStyle('display') == "none" || advice.getStyle('visiblity') == "hidden" || advice.getStyle('opacity')==0)){
			field[this.getPropName(className)] = true;
			//if element.cnet.js is present, transition the advice in
			if(advice.smoothShow) advice.smoothShow();
			else advice.setStyle('display','block');
		}
	},
	hideAdvice: function(className, field){
		var advice = this.getAdvice(className, field);
		if(advice && field[this.getPropName(className)]) {
			field[this.getPropName(className)] = false;
			//if element.cnet.js is present, transition the advice out
			if(advice.smoothHide) advice.smoothHide();
			else advice.setStyle('display','none');
		}
	},
	isVisible : function(field) {
		while(field.tagName != 'BODY') {
			if($(field).getStyle('display') == "none") return false;
			field = field.getParent();
		}
		return true;
	},
	getAdvice: function(className, field) {
		return $('advice-' + className + '-' + this.getFieldId(field))
	},
	makeAdvice: function(className, field, error, warn){
		var errorMsg = (warn)?this.options.warningPrefix:this.options.errorPrefix;
				errorMsg += (this.options.useTitles) ? $pick(field.title, error):error;
		var advice = this.getAdvice(className, field);
		if(!advice){
			var cssClass = (warn)?'warning-advice':'validation-advice';
			advice = new Element('div').addClass(cssClass).setProperty(
				'id','advice-'+className+'-'+this.getFieldId(field)).setStyle('display','none').appendText(errorMsg);
		} else{
			advice.setHTML(errorMsg);
		}
		return advice;
	},
	insertAdvice: function(advice, field){
		switch (field.type.toLowerCase()) {
			case 'radio':
				var p = $(field.parentNode);
				if(p) {
					p.adopt(advice);
					break;
				}
			default: advice.injectAfter($(field));
	  };
	},
	getFieldId : function(field) {
		return field.id ? field.id : field.id = "input_"+field.name;
	},
/*	Property: resetField
		Removes all the error messages for a specific field.
		
		Arguments:
		field - the field to reset.
	*/
	resetField: function(field) {
		field = $(field);
		if(field) {
			var cn = field.className.split(" ");
			cn.each(function(className) {
				if(className.test('^warn-')) className = className.replace(/^warn-/,"");
				var prop = this.getPropName(className);
				if(field[prop]) this.hideAdvice(className, field);
				field.removeClass('validation-failed');
				field.removeClass('warning');
				field.removeClass('validation-passed');
			}, this);
		}
	},
/*	Property: stop
		Stops validating the form; form will submit even if there are values that do not pass validation;
	*/
	stop: function(){
		this.paused = true;
	},
/*	Property: start
		Resumes validating the form.
	*/
	start: function(){
		this.paused = false;
	},
/*	Property: ignoreField
		Stops validating a particular field.
		
		Arguments:
		field - the field to ignore
		warn - (boolean, optional) don't require the validator to pass, but do produce a warning.
	*/
	ignoreField: function(field, warn){
		field = $(field);
		if(field){
			this.enforceField(field);
			if(warn) field.addClass('warnOnly');
			else field.addClass('ignoreValidation');
		}
	},
/*	Property: enforceField
		Resumes validating a particular field
		
		Arguments:
		field - the field to resume validating
	*/
	enforceField: function(field){
		field = $(field);
		if(field){
			field.removeClass('warnOnly');
			field.removeClass('ignoreValidation');
		}
	}
});
FormValidator.implement(new Options);
FormValidator.implement(new Events);

FormValidator.adders = {
/*	Property: validators
		An array of <Validator> objects.
	*/
	validators:{},
/*	Property: add
		Adds a new form validator to the FormValidator object. 
		
		Arguments:
		className - the className associated with the validator
		options - the <Validator> options (errorMsg and test)


		Note:
		This method is a property of every instance of FormValidator as well as the 
		FormValidator object itself. That is to say that you can add validators to
		the FormValidator object or to an instance of it. Adding validators to an instance
		of FormValidator will make those validators apply only to that instance, while
		adding them to the Class will make them available to all instances.
		
		Examples:
(start code)
//add a validator for ALL instances
FormValidator.add('isEmpty', {
	errorMsg: 'This field is required',
	test: function(element){
		if(element.value.length ==0) return false;
		else return true;
	}
});

//this validator is only available to this single instance
var myFormValidatorInstance = new FormValidator('myform');
myFormValidatorInstance.add('doesNotContainTheLetterQ', {
	errorMsg: 'This field cannot contain the letter Q!',
	test: function(element){
		return !element.getValue().test('q','i');
	}
});

//Extend FormValidator, add a global validator for all instances of that version
var NewFormValidator = FormValidator.extend({
	//...some code
});
NewFormValidator.add('doesNotContainTheLetterZ', {
	errorMsg: 'This field cannot contain the letter Z!',
	test: function(element){
		return !element.getValue().test('z','i');
	}
});
(end)

	*/
	add : function(className, options) {
		this.validators[className] = new InputValidator(className, options);
		//if this is a class
		//extend these validators into it
		if(!this.initialize){
			this.implement({
				validators: this.validators
			});
		}
	},
/*	Property: addAllThese
		An array of InputValidator configurations (see <FormValidator.add> above).
		
		Example:
(start code)
FormValidator.addAllThese([
	['className1', {errorMsg: ..., test: ...}],
	['className2', {errorMsg: ..., test: ...}],
	['className3', {errorMsg: ..., test: ...}],
]);
(end)
	*/
	addAllThese : function(validators) {
		$A(validators).each(function(validator) {
			this.add(validator[0], validator[1]);
		}, this);
	},
	getValidator: function(className){
		return this.validators[className];
	}
};
Object.extend(FormValidator, FormValidator.adders);
FormValidator.implement(FormValidator.adders);

/*	Section: Included InputValidators
		Here are the validators that are included in this libary. Add the className to
		any input and then create a new <FormValidator> and these will automatically be
		applied. See <FormValidator.add> on how to add your own.

		Property: IsEmpty
		Evalutes if the input is empty; this is a utility validator, see <FormValidator.required>.
		
		Error Msg - returns false (no message)
			*/
FormValidator.add('IsEmpty', {
	errorMsg: false,
	test: function(element) { 
		if(element.type == "select-one"||element.type == "select")
			return !(element.selectedIndex >= 0 && element.options[element.selectedIndex].value != "");
		else
			return ((element.getValue() == null) || (element.getValue().length == 0));
	}
});


FormValidator.addAllThese([
/*	Property: required
		Displays an error if the field is empty.
		
		Error Msg - "This field is required"			
	*/
	['required', {
		errorMsg: function(element){return 'This field is required.'}, 
		test: function(element) { 
			return !FormValidator.getValidator('IsEmpty').test(element); 
		}
	}],
/*	Property: minLength
		Displays a message if the input value is less than the supplied length.
		
		Error Msg - Please enter at least [defined minLength] characters (you entered [input length] characters)
		
		Note:
		You must add this className AND properties for it to your input.
	
		Example:
		><input type="text" name="username" class="minLength props{minLength:10}" id="username">
	*/
	['minLength', {
		errorMsg: function(element, props){
			if($type(props.minLength))
				return 'Please enter at least ' + props.minLength + ' characters (you entered ' + element.getValue().length + ' characters).';
			else return '';
		}, 
		test: function(element, props) {
			if($type(props.minLength)) return (element.getValue().length >= $pick(props.minLength, 0));
			else return true;
		}
	}],
/*	Property: maxLength
		Displays a message if the input value is less than the supplied length.
		
		Error Msg - Please enter no more than [defined maxLength] characters (you entered [input length] characters)
		
		Note:
		You must add this className AND properties for it to your input.
		
		Example:
		><input type="text" name="username" class="maxLength props{maxLength:100}" id="username">
	*/
	['maxLength', {
		errorMsg: function(element, props){
			//props is {maxLength:10}
			if($type(props.maxLength))
				return 'Please enter no more than ' + props.maxLength + ' characters (you entered ' + element.getValue().length + ' characters).';
			else return '';
		}, 
		test: function(element, props) {
			//if the value is <= than the maxLength value, element passes test
			return (element.getValue().length <= $pick(props.maxLength, 10000));
		}
	}],
/*	Property: validate-number
		Validates that the entry is a number.
		
		Error Msg - 'Please enter a valid number in this field.'
	*/	
	['validate-number', {
		errorMsg: 'Please enter a valid number in this field.',
		test: function(element) {
				return FormValidator.getValidator('IsEmpty').test(element) || !/[^\d+$]/.test(element.getValue());
		}
	}],
/*	Property: validate-digits
		Validates that the entry contains only numbers

		Error Msg - 'Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.'
	*/
	['validate-digits', {
		errorMsg: 'Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.', 
		test: function(element) {
			return FormValidator.getValidator('IsEmpty').test(element) || 
				(/[^a-zA-Z]/.test(element.getValue()) && /[\d]/.test(element.getValue()));
		}
	}],
/*	Property: validate-alpha
		Validates that the entry contains only letters 

		Error Msg - 'Please use letters only (a-z) in this field.'
	*/
	['validate-alpha', {
		errorMsg: 'Please use letters only (a-z) in this field.', 
		test: function (element) {
			return FormValidator.getValidator('IsEmpty').test(element) ||  /^[a-zA-Z]+$/.test(element.getValue())
		}
	}],
/*	Property: validate-alphanum
		Validates that the entry is letters and numbers only

		Error Msg - 'Please use only letters (a-z) or numbers (0-9) only in this field. No spaces or other characters are allowed.'
	*/
	['validate-alphanum', {
		errorMsg: 'Please use only letters (a-z) or numbers (0-9) only in this field. No spaces or other characters are allowed.', 
		test: function(element) {
			return FormValidator.getValidator('IsEmpty').test(element) || !/\W/.test(element.getValue())
		}
	}],
/*	Property: validate-date
		Validates that the entry parses to a date.

		Error Msg - 'Please use this date format: mm/dd/yyyy. For example 03/17/2006 for the 17th of March, 2006.'
	*/
	['validate-date', {
		errorMsg: 'Please use this date format: mm/dd/yyyy. For example 03/17/2006 for the 17th of March, 2006.',
		test: function(element) {
			if(FormValidator.getValidator('IsEmpty').test(element)) return true;
	    var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
	    if(!regex.test(element.getValue())) return false;
	    var d = new Date(element.getValue().replace(regex, '$1/$2/$3'));
	    return (parseInt(RegExp.$1, 10) == (1+d.getMonth())) && 
        (parseInt(RegExp.$2, 10) == d.getDate()) && 
        (parseInt(RegExp.$3, 10) == d.getFullYear() );
		}
	}],
/*	Property: validate-email
		Validates that the entry is a valid email address.

		Error Msg - 'Please enter a valid email address. For example fred@domain.com .'
	*/
	['validate-email', {
		errorMsg: 'Please enter a valid email address. For example fred@domain.com .', 
		test: function (element) {
			return FormValidator.getValidator('IsEmpty').test(element) || /\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/.test(element.getValue());
		}
	}],
/*	Property: validate-url
		Validates that the entry is a valid url

		Error Msg - 'Please enter a valid URL.'
	*/
	['validate-url', {
		errorMsg: 'Please enter a valid URL.', 
		test: function (element) {
			return FormValidator.getValidator('IsEmpty').test(element) || /^(http|https|ftp|rmtp|mms):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i.test(element.getValue());
		}
	}],
/*	Property: validate-date-au
		Validates that the entry matches dd/mm/yyyy.

		Error Msg - 'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.'
	*/
	

	['validate-date-au', {
		errorMsg: 'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.',
		test: function(element) {
			if(FormValidator.getValidator('IsEmpty').test(element)) return true;
	    var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
	    if(!regex.test(element.getValue())) return false;
	    var d = new Date(element.getValue().replace(regex, '$2/$1/$3'));
	    return (parseInt(RegExp.$2, 10) == (1+d.getMonth())) && 
        (parseInt(RegExp.$1, 10) == d.getDate()) && 
        (parseInt(RegExp.$3, 10) == d.getFullYear() );
		}
	}],
/*	Property: validate-currency-dollar
		Validates that the entry matches any of the following:
			- [$]1[##][,###]+[.##]
			- [$]1###+[.##]
			- [$]0.##
			- [$].##
		
		Error Msg - 'Please enter a valid $ amount. For example $100.00 .'
	*/
	['validate-currency-dollar', {
		errorMsg: 'Please enter a valid $ amount. For example $100.00 .', 
		test: function(element) {
			// [$]1[##][,###]+[.##]
			// [$]1###+[.##]
			// [$]0.##
			// [$].##
			return FormValidator.getValidator('IsEmpty').test(element) ||  /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(element.getValue());
		}
	}],
/*	Property: validate-one-required
		Validates that all the entries within the same node are not empty.

		Error Msg - 'Please enter something for at least one of the above options.'
		
		Note:
		This validator will get the parent element for the input and then check all its children.
		To use this validator, enclose all the inputs you want to group in another element (doesn't
		matter which); you only need apply this class to *one* of the elements.
		
		Example:
(start code)
<div>
	<input ....>
	<input ....>
	<input .... className="validate-one-required">
</div>(end)
	*/
	['validate-one-required', {
		errorMsg: 'Please enter something for at least one of the above options.', 
		test: function (element) {
			var p = element.parentNode;
			var options = p.getElements('input');
			return $A(options).some(function(el) {
				return el.getValue();
			});
		}
	}]
]);

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/form.validator.js,v $
$Log: form.validator.js,v $
Revision 1.24  2007/11/29 19:00:57  newtona
- fixed an issue with StickyWin that could create inheritance issues because of a defect in $merge. this fixed an actual issue with DatePicker, specifically, having more than one on a page
- fixed an issue with the basehref in simple.error.popup

Revision 1.23  2007/11/02 18:15:40  newtona
fixing an issue with the image path in setAssetHref for the date picker
adding mms to url validator in form validator

Revision 1.22  2007/10/27 00:06:19  newtona
adding rtmp support in the form validator that validates urls

Revision 1.21  2007/10/02 18:50:51  newtona
doc fix

Revision 1.20  2007/10/02 00:58:14  newtona
form.validator: fixed a bug where validation errors weren't removed under certain situations
tabswapper: .recall() no longer fails if no cookie name is set
jsonp: adding a 'return this'

Revision 1.19  2007/09/24 20:55:49  newtona
new file: StickyWin.Ajax - adds ajax support to all stickywin classes (creates new classes, just append .Ajax to any of the existing ones)
updated redball common full to include StickyWin.Ajax
date.picker, product.picker - updated syntax to use Element.empty
form.validator - now passes along the event object to the onFormValidate event so that the form submit event can be stopped if you like
popupdetails - added html response support; you can now return the html you wish to display rather than a json object; only applies to ajax. Also added a cache so that multiple requests are not made for the same url.
stickyWinHTML - ractored so that options are now, you know, *optional*
MooScroller - added support for width option for horizontal scrolling

Revision 1.18  2007/09/15 00:07:08  newtona
collission in last check in; merged and re-doing.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
fixed a bug with validate-digits in form validator
new class: TagMaker
implemented TagMaker into simple.editor
updated caption (which is the drag handle for stickyWinHTML) to be the entire width of the caption area
html.table: docs update
tabswapper: docs update

Revision 1.17  2007/09/05 20:17:34  newtona
frakking semi-colons

Revision 1.16  2007/09/05 18:37:03  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.15  2007/06/02 01:35:56  newtona
*** empty log message ***

Revision 1.14  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.13  2007/04/13 00:22:57  newtona
fixed a typo in FormValidator.hideAdvice (display: none instead of display: block)

Revision 1.12  2007/04/06 00:43:51  newtona
slight syntax update

Revision 1.11  2007/04/06 00:37:40  newtona
tweaked the way serial works

Revision 1.10  2007/04/05 23:48:55  newtona
FormValidator now has numerous new features: instance-level validators, .stop, .start, .ignoreField, .enforceField, and warnings

Revision 1.9  2007/04/05 23:01:26  newtona
FormValidator now has numerous new features: instance-level validators, .stop, .start, .ignoreField, .enforceField, and warnings

Revision 1.8  2007/03/02 00:28:37  newtona
advice is now inserted into the DOM in it's own method so it can be easily overriden
makeAdvice no longer inserts the advice.

Revision 1.7  2007/02/22 18:18:42  newtona
typo in the docs

Revision 1.6  2007/02/07 20:51:41  newtona
implemented Options class
implemented Events class
StickyWin now uses Element.position

Revision 1.5  2007/02/06 18:10:36  newtona
updated the error displays to use the new element.smoothshow function

Revision 1.4  2007/02/03 01:36:17  newtona
added multi-select support
shortened validate-number
updated validate-date essage and fixed a bug in it

Revision 1.3  2007/01/26 05:48:03  newtona
docs update

Revision 1.2  2007/01/22 22:00:15  newtona
numerous bug fixes to modalizer, stickywin, and popupdetails
updated for mootools 1.0
fixed date validation in form.validator

Revision 1.1  2007/01/19 01:22:05  newtona
*** empty log message ***


*/
/*	Script: IconMenu.js
		A simple icon (img) based menu.
		
		Author: 
		Aaron Newton
		
		Dependencies:
		Mootools 1.11 - <Core.js>, <Class.js>, <Class.Extras.js>, <Array.js>, <Element.js>, <String.js>, <Function.js>, <Number.js>, <Element.Event.js>, <Element.Selectors.js>, <Element.Dimensions.js>, <Fx.Base.js>, <Fx.Css.js>, <Fx.Style.js>, <Fx.Styles.js>, <Fx.Elements.js>, <Fx.Slide.js>, <Color.js>
		CNET - <element.pin.js>, <element.dimensions.js>, <fixpng.js>
		
		Class: IconMenu
		A simple icon (img) based menu.
		
		Arguments:
		options - a key/value set of options.
		
		Options:
		container - (DOM element or id) the container of the menu
		images - (string) the class given to all the image in the menu (default ".iconImgs")
		captions - (string) the class given to all the captions (default ".iconCaptions")
		removeLinks - (string or collection) a selector or a collection; all the links to add remove actions to
		clearLinks - (string or collection) a selector or a collection; all the links to add clear actions to
		useAxis - (string) the zooming axis to use; 'x', 'y', or 'both' (default 'x')
		scrollFxOptions - (object) options for the <Fx.Style> that scrolls the icons left and right
		
		Events:
		onFocus - (function) callback executed when the user mouses over an icon (default function(){})
		onFocusDelay - (integer) amount of time to delay the onFocus event; if the user mouses out from 
							the icon before the duration of the delay, the event is not fired; defaults to zero
		initialFocusDelay - (integer) amount of time to delay before the first onFocus event is fired
		onBlur - (function) callback executed when the user mouses over an icon (default function(){})
		onBlurDelay - (integer) amount of time to delay the onBlur event; if the user puts their mouse
							back over the icon before the duration of the delay, the event is not fired; defaults to zero
		onEmpty - (function) callback executed when the toolbar is emptied of icons
		onRemoveItem - (function) callback executed when an item is removed; passed the container 
				of the image and caption just before they are removed
		onRemoveItems - (function) callback executed after items are removed using <IconMenu.removeItems>; passed an array of
				the removed items
		onSelect - (function) callback executed when an icon is selected; passed the index of the icon and
				the image in the icon
		onDeSelect - (function) same as onSelect, only it's fired when something is deselected
		onItemsAdded - (function) callback executed when all the icons are loaded on initialize
		onAdd - (function) callback executed when an individual item is added; passed the image and caption

		Instance Variables:
		container - the element that holds all the icons
		images - an array of all the images
		captions - an array of all captions (associated to each image in imgs)
		selected - an array of icons that are in the selected state
		side - 'left' or 'top' state (where the bar is and its alignment); based on options.useAxis
		scrollerFx - effect used for scrolling left and right
		currentOffset - the currently scrolled-to index
		inFocus - the icon currently hovered over
		prevFocus - the icon previously hovered over
		
		Definitions/Conventions:
		"img" or "image" - generally is a refrence to an actual image in the icon menu
		"caption" - generally is a reference to an optional DOM element that may accompany an image
		"icon" - generally is a reference to the DOM element containing an image and (possibly) a caption
			*/
var IconMenu = new Class({
	options: {
			container: document,
			images: ".iconImgs",
			captions: ".iconCaptions",
			removeLinks: false,
			clearLinks: false,
			useAxis: 'x',
			onFocus: Class.empty, //mouseover of target area
			onFocusDelay: 0,
			initialFocusDelay: 250,
			onBlur: Class.empty, //mouseout of target area
			onEmpty: Class.empty,
			onBlurDelay: 0,
			onRemoveItem: Class.empty,
			onRemoveItems: Class.empty,
			length: 'auto',
			iconPadding: 1,
			scrollFxOptions: {
				duration: 1800,
				transition: Fx.Transitions.Cubic.easeInOut
			},
			onScroll: Class.empty,
			onPageForward: Class.empty,
			onPageBack: Class.empty,
			backScrollButtons: '#scrollLeft',
			forwardScrollButtons: '#scrollRight',
			onSelect: function(index, img){
				//set up the border effect
				if(!this.borderFx[img.getProperty('id')])	this.borderFx[img.getProperty('id')] = img.effects({duration: 800});
				//and fade the border to blue
				this.borderFx[img.getProperty('id')].start({
						'border-top-color': '#00A0C6',
						'border-left-color': '#00A0C6',
						'border-right-color': '#00A0C6',
						'border-bottom-color': '#00A0C6'
				});
			},
			onDeSelect: function(index, img){
				//set up the border effect
				if(!this.borderFx[img.getProperty('id')])	this.borderFx[img.getProperty('id')] = img.effects({duration: 800});
				//and fade the border back to grey
				this.borderFx[img.getProperty('id')].start({
						'border-top-color': '#555',
						'border-left-color': '#555',
						'border-right-color': '#555',
						'border-bottom-color': '#555'
				});
			}
	},

	initialize: function(options) {
		//set the options
		this.setOptions(options);
		//save a reference to the container
		this.container = $(this.options.container);
		//this.setRange(this.options.visibleOffset, this.options.maxVisible);
		//containers for the images, captions, and the selected items
		this.imgs = [];
		this.captions = [];
		this.selected = [];
		//get the captions from the options
		var captions = ($type(this.options.captions) == "string")?
			this.container.getElements(this.options.captions):
			this.options.captions;
		//get the images from the options
		var imgs = ($type(this.options.images) == "string")?
			this.container.getElements(this.options.images):
			this.options.images;
		//loop through each one
		imgs.each(function(img, index) {
			//add it to the menu
			this.addItem(img, captions[index], null);
		}, this);
		
		this.fireEvent('onItemsAdded', this.imgs, 50);
		this.side = (this.options.useAxis == 'x')?'left':'top';
		this.container.setStyle(this.side, this.container.getStyle(this.side).toInt()||0);
		this.onFocusDelay = this.options.initialFocusDelay;
		//set up the events
		this.setupEvents();
	},
/*	Section: Public Methods

		Property: scrollTo
		Scrolls the icons in the bar to the specified index.
		
		Arguments:
		index - (integer) the index of the icon you want to scroll to
		useFx - (boolean; optional) use transition or not; defautls to true
		 */
	scrollTo: function(index, useFx){
		//set useFx default to true
		useFx = $pick(useFx, true);
		//get the current range in view
		var currentRange = this.calculateRange();
		//if we're there, exit
		if(index == currentRange.start) return;
		//get the range for the new position
		var newRange = this.calculateRange(index);
		//if this returns no items, exit
		if(!newRange.elements.length) return; //no next page! >> Ajax here
		//make sure the container has a position set
		if(this.container.getStyle('position') == 'static') this.container.setStyle('position', 'relative');
		//create the scroll effects if not present already
		if(!this.scrollerFx) this.scrollerFx = this.container.effect(this.side, $merge(this.options.scrollFxOptions, {wait: false}));
		//scroll to the new location
		if(useFx) {
			this.scrollerFx.start(-newRange.elements[0].offset).chain(function(){
			//set the index to be this new location
				this.fireEvent('onScroll', [index, newRange]);
			}.bind(this));
		} else {
			//we're not using effects, so just jump to the location
			this.scrollerFx.set(-newRange.elements[0].offset);
			this.fireEvent('onScroll', [index, newRange]);
		}
		this.currentOffset = index;
	},
/*	Property: pageForward
		Pages the icon set one visible set forward; a set is defined as the number of icons in range.
		
		Arguments:
		howMany - (integer) optional; you can define a set as a fixed number rather than on the visible amount
	*/
	pageForward: function(howMany){
		var range = this.calculateRange();
		this.scrollTo(($type(howMany) == "number")?range.start+howMany:range.end);
	},
/*	Property: pageBack
		Pages the icon set one visible set backward; a set is defined as the number of icons in range.
		
		Arguments:
		howMany - (integer) optional; you can define a set as a fixed number rather than on the visible amount
	*/
	pageBack: function(howMany) {
		this.scrollTo(($type(howMany) == "number")?this.currentOffset-howMany:this.calculateRange(this.currentOffset, true).start);
	},
/*	Property: addItem
		Adds an item to the icon bar.
		
		Arguments:
		img - DOM element for the icon; typically an image
		caption - DOM element for the caption related to the image, optional
		where - (integer) index where to put it; defaults to the end of the icon set
*/
	addItem: function(img, caption, where) {
		//figure out where to put it
		where = ($defined(where))?where:this.imgs.length;
		//if we've already got this image in there, remove it before putting it in the right place
		if(this.imgs.contains(img)) this.removeItems([img], true);
		//insert the image and caption into the array of these things
		this.imgs.splice(where, 0, $(img));
		this.captions.splice(where, 0, $(caption));

		//if the image doesn't have an id, then lets make one
		var src = img.getProperty('src');
		if(!img.getProperty('id')) img.setProperty('id', src.substring(src.lastIndexOf('/')+1, src.lastIndexOf('.')));

		//fix the image if it's png
		if(img.getProperty("src").test("$png") && window.ie && !img.hasClass('fixPNG')) fixPNG(img);
		//set up the events for the element
		this.setupIconEvents(img, caption);
		this.fireEvent('onAdd', [img, caption]);
	},
/*	Property: removeItems
		Removes a list of items from the icon menu.
		
		Arguments:
		imgs - (array) an array of images to remove
		useFx - (boolean) transition the images away (true; default), or remove them instantly (false)
	*/
	removeItems: function(imgs, useFx){
		var range = this.calculateRange();
		if(!imgs.length) return;
		//create a copy; this is because
		//IconMenu.empty passes *this.selected*
		//which we modify in the process of removing things
		//so we must work on a copy of that so we don't change
		//the list as we iterate over it
		imgs = imgs.copy();
		//set the fx default
		useFx = $pick(useFx, true);
		//placeholder for the items we're removing; the effect will
		//only be applied to the dom element that contains the image and the caption
		var fadeItems = [];
		//the effect we'll use
		var effect = {
				width: 0,
				'border-width':0
		};
		//an object to store all the copies of the effect; one for each item to be passed
		//to Fx.Elements
		var fadeEffects = {};
		//for items that aren't in the current view, we're not going to use a transition
		var itemsToQuietlyRemove = {
			before: [],
			after: []
		};
				
		//a list of all the icons by index
		var indexes = [];
		//for each image in the set to be removed
		imgs.each(function(image){
			var index = this.imgs.indexOf(image);
			//if the image is visible
			if(index >= range.end) {
				itemsToQuietlyRemove.after.push(image.getParent());
			} else if(index < range.start) {
				itemsToQuietlyRemove.before.push(image.getParent());
			} else {
				//store the parent of the image
				fadeItems.push(image.getParent());
				//copy the effect value for this item
				fadeEffects[fadeItems.length-1] = $merge(effect);
			}
			//remove the reference in the selected array
			//because when it's gone, it won't be selected anymore
			this.selected.remove(image);
			//store the index of where this image was in the menu
			indexes.push(index);
		}, this);
		//loop through the captions and remove the captions that match 
		//the images that were removed
		this.captions = this.captions.filter(function(caption, index){
			return !indexes.contains(index);
		});
		//do the same for the list of images in the menu
		//we didn't do this earlier so we could avoid changing
		//the array while we were working on it
		this.imgs = this.imgs.filter(function(img, index){
			return !indexes.contains(index);
		});
		var removed = [];
		//items page left, remove them, but then we have to update the scroll offset to account
		//for their departure
		if(itemsToQuietlyRemove.before.length) {
			var scrollTo = this.imgs.indexOf(range.elements[0].image);
			itemsToQuietlyRemove.before.each(function(el){
				this.fireEvent('onRemoveItem', [el]);
				var img = el.getElement('img');
				removed.push(img.id);
				try {
					el.remove();
					//scroll to the current offset again quickly
				}catch(e){ dbug.log('before: error removing element %o, %o', el, e); }
			}, this);
			this.scrollTo(scrollTo, false);
		}
		//for items page right, just remove them quickly and quietly
		itemsToQuietlyRemove.after.each(function(el){
			this.fireEvent('onRemoveItem', [el]);
			removed.push(el.getElement('img').id);
			try {
				el.remove(); 
			}catch(e){ dbug.log('after: error removing element %o, %o', el, e); }
		});

		//define a function that removes all the items from the dom
		function clean(range, additionalItems){
			var items = [];
			//then fade out the items that are currently visible
			fadeItems.each(function(el){
				this.fireEvent('onRemoveItem', [el]);
				items.push(el.getElement('img').id);
				try {
					el.remove(); 
				}catch(e){ dbug.log('fade: error removing element %o, %o', el, e); }
			}, this);
			items.merge(additionalItems);
			this.fireEvent('onRemoveItems', [items]);
			range = this.calculateRange();
			if(range.elements == 0 && range.start > 0) this.pageBack();
			//if there aren't any items left, fire the onEmpty event
			if(!this.imgs.length) this.fireEvent('onEmpty');
		}
		//if we're using effects, do the transition then call clean()
		if(useFx) new Fx.Elements(fadeItems).start(fadeEffects).chain(clean.bind(this, [range, removed]));
		//else just clean
		else clean.apply(this, [range, removed]);
	},
/*	Property: removeSelected
		Removes the icons that the user selected (see IconMenu.selectItem) from the menu.
	*/
	removeSelected: function(useFx){
		this.removeItems(this.selected, useFx);
	},
/*	Property: empty
		Empties the menu entirely.
		
		Arguments:
		suppressEvent - (boolean) prevents the onEmpty event from firing.
	*/
	empty: function(suppressEvent){
		//placeholder for the effects and items
		var effect = {};
		var items = [];
		//loop through all the images in the icon menu
		this.imgs.each(function(img, index){
			//add the icon container to the list of items to remove
			items.push(img.getParent());
			//create a reference for each one to pass to Fx.Elements
			effect[index] = {opacity: 0};
		});
		//create an instance of Fx.Elements and fade them all out
		new Fx.Elements(items).start(effect).chain(function(){
			//then remove them all instantly
			this.removeItems(this.imgs, false);
			//and fire the onEmpty event
			if(!suppressEvent) this.fireEvent('onEmpty');
		}.bind(this));
	},
/*	Property: selectItem
		Designates an item at the specified index as being selected.
		
		Arguments:
		index - (integer) the location of the icon to select
		select - (boolean) true: select the item; false: deselect; optional. If not specified it toggles
		
	*/
	selectItem: function(index, select){
		//place holder for border effects
		if(!this.borderFx) this.borderFx = {};
		//get the image to select
		var img = this.imgs[index];
		//...and its id
		var imgId = img.getProperty('id');
		//add or remove the "selected" class
		if($defined(select)) {
			if(select) img.addClass('selected');
			else img.removeClass('selected');
		} else {
			img.toggleClass('selected');
		}
		//if it has the class, then fade the border blue
		if(img.hasClass('selected')){
			//store this image in the index of selected images
			this.selected.push(img);
			this.fireEvent('onSelect', [index, img]);
		} else {
			//else we're deselecting; remove the image from the index of selected images
			this.selected.remove(img);
			this.fireEvent('onDeSelect', [index, img]);
		}
	},
/* 	Section: Private Methods

		Property: getDefaultWidth
		Internal; calculates the width of the container; used for scrolling.
 */
	getDefaultWidth: function(){
		//if the user specified a width, just return it
		if($type(this.options.length) == "number") return this.options.length;
		//if, on the other hand, they specified another element than the container
		//to calculate the width, use it
		var container = $(this.options.length);
		//otherwise, use the container
		if(!container) container = this.container.getParent();
		//return the width or height of that element, depending on the axis chosen in the options
		return container.getSize().size[this.options.useAxis];
	},
/*	Property: getIconPositions
		Internal; returns an array for each item containign data used for scrolling.
 */
	getIconPositions: function(){
		var offsets = [];
		var cumulative = 0;
		var prev;
		//loop through all the items
		this.imgs.each(function(img, index){
			//we're measuring the element that contains the image
			var parent = img.getParent();
			//get the width or height of that parent using the appropriate axis
			cumulative += (prev)?img.offsetLeft - prev.offsetLeft:0;
			prev = img;
			//var size = parent.getSize().size[this.options.useAxis]
			//store the data
			offsets.push({
				image: img,
				size: parent.getSize().size[this.options.useAxis],
				offset: cumulative,
				container: parent
			});
		}, this);
		return offsets;
	},
/*	Property: calculateRange
		Internal; calculates the icons that can be viewed in the container starting at a given index.
		
		Arguments:
		index - (integer) the index of icons where to start
		fromEnd - (boolean) if true, will figure out the result in reverse (used for scrolling back)
			and index now correspondes the endpoint, not the offset
 */
	calculateRange: function(index, fromEnd){
		if(!this.imgs.length) return {start: 0, end: 0, elements: []};
		index = $pick(index, this.currentOffset||0);
		if(index < 0) index = 0;
		//dbug.trace();
		//get the width of space that icons are visible
		var length = this.getDefaultWidth();
		//get the positions of all the icons
		var positions = this.getIconPositions();
		var referencePoint;
		//if we're paginating forward the reference point is the left edge 
		//of the range is the left edge of the first icon
		//but if we're going backwards, the referencePoint is the left edge of the first icon currently in range
		//the problem is if the user removes the entire last page of icons, then this
		//item no longer exists, so...
		if(positions[index]) {
			//if the item exists, use it
			referencePoint = positions[index].offset;
		} else {
			//else the right edge of the last icon is the reference point
			//the last icon is the container of the last image
			var lastIcon = this.imgs.getLast().getParent();
			var coords = lastIcon.getCoordinates();
			//and the reference point is that icon's width plus it's left offset minus the offset 
			//of the parent (which gets offset negatively and positively for scrolling
			referencePoint = coords.width + coords.left - lastIcon.getParent().getPosition().x;
		}
		//figure out which ones are in range
		var range = positions.filter(function(position, i){
			//if the index supplied is the endpoint
			//then it's in range if the index of the icon is less than the index,
			//and the left side is less than that of the one at the end point
			//and if the left side is greater than or equal to the end point's position minus the length
			if(fromEnd) return i < index && 
												 position.offset < referencePoint &&
												 position.offset >= referencePoint-length;
			
			//else we go forward...
			//if the item is after the index start and the posision is 
			//less than the max width defined, include it
			else return i >= index && position.offset+position.size < length+positions[index].offset;
		});
		//return the data
		return (fromEnd)?{start: index-range.length, end: index, elements: range}
					 :{start: index, end: range.length+index, elements: range};
	},
/*	Property: inRange
		Internal; given an index of an item, determine if it is in visible range
		
		Arguments:
		index - (integer) the index of the icon to check
 */
	inRange: function(index) {
		//calculate the range
		var range = this.calculateRange();
		//return the result
		return index < range.end && index >= range.start;
	},
/*	Property: setupEvents
		Internal; sets up the mouseleave event for the container to make sure that we 
		remove the focus attribute if the user removes their mouse from the bar; also
		sets up the clear and remove links.
 */
	setupEvents: function(){
		$(this.options.container).addEvents({
			"mouseleave": function() {
				if(this.inFocus) this.inFocus = null;
				this.imgOut(null, true);
			}.bind(this)
		});
		
		$$(this.options.backScrollButtons).each(function(el){
			el.addEvent('click', this.pageBack.bind(this));
		}, this);
		$$(this.options.forwardScrollButtons).each(function(el){
			el.addEvent('click', this.pageForward.bind(this));
		}, this);
		
		$$(this.options.clearLinks).each(function(el){
			el.addEvent('click', this.empty.bind(this));
		}, this);
		$$(this.options.removeLinks).each(function(el){
			el.addEvent('click', this.removeSelected.bind(this));
		}, this);
	},
/*	Property: imgOver
		Internal; function executed when the user mouses over an icon; this is the container
		holding the image and the caption, not the image itself, though the image is what
		is passed in as an argument
		
		
		Arguments:
		img - (DOM element) the image (contained with in the array this.imgs) that has been hovered over
 */
	imgOver: function(img){
		//set the value of what's in focus to be this image
		this.inFocus = img;
		//clear the overTimeout
		$clear(this.overTimeout);
		//delay for the duration of the onFocusDelay option
		this.overTimeout = (function(){
			this.onFocusDelay = this.options.onFocusDelay;
			//if the user is still focused on the image, fire the onFocus event
			if (this.inFocus == img) this.fireEvent("onFocus", [img, this.imgs.indexOf(img)]);
		}).delay(this.onFocusDelay, this);
	},
/*	Property: imgOut
		Internal; similar to <imgOver> above, except it's fired when the mouse leaves.
 */
	imgOut: function(img, force){
		if(!$defined(img) && force) img = this.prevFocus||this.imgs[0];
		//if the focused image is this one
		if(this.inFocus == img && img) {
			//set it to null
			this.inFocus = null;
			//clear the delay timeout
			$clear(this.outTimeout);
			//wait the duration of the onBlurDelay
			this.outTimeout = (function(){
				this.prevFocus = img;
				//if we're not still focused on this image, fire onBlur
				if (this.inFocus != img || (img == null && force)) this.fireEvent("onBlur", [img, this.imgs.indexOf(img)]);
				if (!this.inFocus) this.onFocusDelay = this.options.initialFocusDelay;
			}).delay(this.options.onBlurDelay, this);
		}
	},
/*	Property: setupIconEvents
		Internal; sets up events for an icon
 */
	setupIconEvents: function(img, caption){
		//add the click event
		img.addEvents({
			click: function(e){
				e = new Event(e);
				//if the user is holding down control, select the image
				if(e.control) {
					this.selectItem(this.imgs.indexOf(img));
					e.stop();
				}
			}.bind(this)
		});
		//set up the other events on the container of this image
		img.getParent().addEvents({
			mouseover: this.imgOver.bind(this, img),
			mouseout: this.imgOut.bind(this, img)
		});
	}
});
IconMenu.implement(new Events); 
IconMenu.implement(new Options);

/* do not edit below this line */	 
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/IconMenu.js,v $
$Log: IconMenu.js,v $
Revision 1.10  2007/09/05 18:37:03  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.9  2007/09/04 17:35:10  newtona
Fixed a bug in IconMenu where, if removeLinks and clearLinks were not defined, the behavior was applied to all dom elements (doh!)
Rearranged ProductToolbar's references to these options so that, rather than setting up these behaviors itself it passes them on to IconMenu to do it.

Revision 1.8	2007/08/30 23:59:30	newtona
fixed chaining in Fx.Marquee; added to redball.common.full
tweaked docs in IconMenu

Revision 1.7	2007/08/30 21:11:33	newtona
added some new options

Revision 1.6	2007/08/28 23:26:57	newtona
fixing syntax errors - damned semi-colons.

Revision 1.5	2007/08/28 23:16:07	newtona
IconMenu now handles deleteing items off screen more effectively
reverted some logic in element.forms; the new stuff was a little buggy

Revision 1.4	2007/08/28 19:01:50	newtona
added onSelect and onDeselect events and moved the default logic into the options
fixed a doc typo
added wait: false to the scroller, double clicks now don't corrupt the state (you'll just scroll twice as far)
moved the currentOffset instance variable assignation outside of the effect change; this prevents issues with paginating faster than the effect
optimized range calculation calls
now, when deleting items, if you delete everything in the current page set, the bar will scroll one page to the left (if there is one)

Revision 1.3	2007/08/27 19:05:30	mikem
removes stray semicolon

Revision 1.2	2007/08/25 00:52:06	newtona
got lazy with my semi-colons...

Revision 1.1	2007/08/20 21:20:44	newtona
first big check in for RTSS History


*//*	Script: modalizer.js
		Provides functionality to overlay the window contents with a semi-transparent layer that prevents interaction with page content until it is removed.
		
		Dependencies:
		Mootools - <Moo.js>, <Array.js>, <String.js>, <Function.js>, <Utility.js>, <Dom.js>, <Element.js>, <Window.Size.js>, <Event.js>, <Window.Base.js>
		
		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)

		Class: Modalizer
		Provides functionality to overlay the window contents with a semi-transparent layer that prevents interaction with page content until it is removed. This class is intended to be implemented into other classes to provide them access to this functionality.
	*/
var Modalizer = new Class({
	defaultModalStyle: {
		display:'block',
		position:'fixed',
		top:'0px',
		left:'0px',	
		'z-index':5000,
		'background-color':'#333',
		opacity:0.8
	},
/*	Property: setOptions
		Sets the options for the modal overlay.
		
		Arguments:
		options - an object with name/value definitions
		
		See <modalShow> for options list.
	*/
	setModalOptions: function(options){
		this.modalOptions = $merge({
			width:(window.getScrollWidth()+300)+'px',
			height:(window.getScrollHeight()+300)+'px',
			elementsToHide: 'select',
			onModalHide: Class.empty,
			onModalShow: Class.empty,
			hideOnClick: true,
			modalStyle: {},
			updateOnResize: true
		}, this.modalOptions, options || {});
	},
	resize: function(){
		if($('modalOverlay')) {
			$('modalOverlay').setStyles({
				width:(window.getScrollWidth()+300)+'px',
				height:(window.getScrollHeight()+300)+'px'
			});
		}
	},
/*	Property: setModalStyle
		Sets the style of the modal overlay to those in the object passed in.
		
		Arguments:
		styleObject - object with key/value css properties
		
		Default styleObject:
(start code){
	'display':'block',
	'position':'fixed',
	'top':'0px',
	'left':'0px',	
	'width':'100%',
	'height':'100%',
	'z-index':this.modalOptions.zIndex,
	'background-color':this.modalOptions.color,
	'opacity':this.modalOptions.opacity
}(end)

	The object you pass in can contain any portion of this object, and the options you specify will overwrite the defaults; any option you do not specify will remain.		
	*/
	setModalStyle: function (styleObject){
		this.modalOptions.modalStyle = styleObject;
		this.modalStyle = $merge(this.defaultModalStyle, {
			width:this.modalOptions.width,
			height:this.modalOptions.height
		}, styleObject);
		if($('modalOverlay'))$('modalOverlay').setStyles(this.modalStyle);
		return(this.modalStyle);
	},
/*	Property: modalShow
		Shows the modal window.
		
		Arguments:
		options - key/value options object
		
		Options:
		elementsToHide - comma seperated string of selectors to hide when the overlay is applied;
			example: 'select, input, img.someClass'; defaults to 'select'
		modalHide - the funciton that hides the modal window; defaults to 
			"function(){if($('modalOverlay'))$('modalOverlay').hide();}"
		modalShow - the function that shows the modal window; defaults to
			"function(){$('modalOverlay').setStyle('display','block');}"
		onModalHide - function to execute when the modal window is removed
		onModalShow - function to execute when the modal window appears
		hideOnClick - allow the user to click anywhere on the modal layer to close it; defaults to true.
		modalStyle - a css style object to apply to the modal overlay. See <setModalStyle>.
		updateOnResize - (boolean) will update the size of the modal layer to fit the window if the user resizes; defaults to true.
	*/
	modalShow: function(options){
		this.setModalOptions(options||{});
		var overlay = null;
		if($('modalOverlay')) overlay = $('modalOverlay');
		if(!overlay) overlay = new Element('div').setProperty('id','modalOverlay').injectInside(document.body);
		overlay.setStyles(this.setModalStyle(this.modalOptions.modalStyle));
		if(window.ie6) overlay.setStyle('position','absolute');
		$('modalOverlay').removeEvents('click').addEvent('click', function(){
			this.modalHide(this.modalOptions.hideOnClick);
		}.bind(this));
		this.bound = this.bound||{};
		if(!this.bound.resize && this.modalOptions.updateOnResize) {
			this.bound.resize = this.resize.bind(this);
			window.addEvent('resize', this.bound.resize);
		}
		this.modalOptions.onModalShow();
		this.togglePopThroughElements(0);
		overlay.setStyle('display','block');
		return this;
	},
/*	Property: modalHide
		Hides the modal layer.
	*/
	modalHide: function(override){
		if(override === false) return false; //this is internal, you don't need to pass in an argument
		this.togglePopThroughElements(1);
		this.modalOptions.onModalHide();
		if($('modalOverlay'))$('modalOverlay').setStyle('display','none');
		if(this.modalOptions.updateOnResize) {
			this.bound = this.bound||{};
			if(!this.bound.resize) this.bound.resize = this.resize.bind(this);
			window.removeEvent('resize', this.bound.resize);
		}

		return this;
	},
	togglePopThroughElements: function(opacity){
		if((window.ie6 || (window.gecko && navigator.userAgent.test('mac', 'i')))) {
			$$(this.modalOptions.elementsToHide).each(function(sel){
				sel.setStyle('opacity', opacity);
			});
		}
	}
});
//legacy namespace
var modalizer = Modalizer;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/modalizer.js,v $
$Log: modalizer.js,v $
Revision 1.21  2007/09/17 17:27:41  newtona
fixed a bug in modalizer; reference to this.bound.resize wasn't yet defined in certain circumstances.

Revision 1.20  2007/09/13 22:19:44  newtona
modalizer now fixes it's overlay if hte window is resized
mooscroller refactors and faster; no more dependencies on Drag or Scroller
window.cnet - updating the query string method and returning a consistent value (an object)

Revision 1.19  2007/09/05 18:37:03  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.18  2007/08/30 17:09:27  newtona
stickyWinFx, modalizer: updated syntax a litle
jlogger: updated docs
fixed a bug in Fx.Sort w/ IE6

Revision 1.17  2007/06/29 21:54:27  newtona
fixed a bug with the hideOnClick option

Revision 1.16  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.15  2007/05/03 18:24:24  newtona
iframeshim: removed a dbug line
modalizer: only hide select lists for browsers that need it
product picker: added a try/catch, updated cnet api link/code

Revision 1.14  2007/04/13 19:06:11  newtona
dependency update in the docs

Revision 1.13  2007/03/27 16:05:40  newtona
fixed bug where select elements were not being hidden on modal show

Revision 1.12  2007/03/20 20:59:54  newtona
fixed a problem where the modal stickywin didn't close when the modal layer was clicked.

Revision 1.11  2007/03/19 17:34:38  newtona
fixed a bug; modal overlay width/height wasn't being set.

Revision 1.10  2007/03/05 23:33:38  newtona
moved width declarations to setModalOptions function; fixed a bug in opera

Revision 1.9  2007/03/05 19:36:00  newtona
now setModalOptions is always called, even if options are not supplied (so the defaults will be used)

Revision 1.8  2007/02/27 21:46:43  newtona
docs update; fixing references

Revision 1.7  2007/02/21 00:27:28  newtona
better option handling

Revision 1.6  2007/02/07 20:51:41  newtona
implemented Options class
implemented Events class
StickyWin now uses Element.position

Revision 1.5  2007/02/06 18:11:29  newtona
refactored to re-use the overlay div because IE hogs memory for each one. god I hate IE.

Revision 1.4  2007/01/26 05:48:22  newtona
docs update

Revision 1.3  2007/01/22 22:00:15  newtona
numerous bug fixes to modalizer, stickywin, and popupdetails
updated for mootools 1.0
fixed date validation in form.validator

Revision 1.2  2007/01/11 20:55:23  newtona
changed the way options are set, split up stickywin into 4 files, refactored popupdetails to use stickywin and modalizer

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.3  2007/01/09 01:25:05  newtona
numerous improvements; ability to set individual css styles, some other tweaks

Revision 1.2  2007/01/05 18:31:51  newtona
fixed documentation syntax
added cvs footer


*/
/*	Script: ObjectBrowser.js
		Creates a tree view of any javascript object.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools 1.11 - Core.js, Class.js, Class.Extras.js, Array.js, Function.js, String.js, Number.js, Element.js, Hash.js
		
		Class: ObjectBrowser
		Creates a tree view of any javascript object.
		
		Arguments:
		container - (DOM element or id) the container where to build the html elements for the browser.
		options - (object) a key/value set of options
		
		Options:
		data - (object) the object to explore
		initPath - (string) the path in the object where the tree display starts; defaults to "", which is the top of the options data object
		buildOnInit - (boolean) if true, builds the interface with the data in the options; defaults to true
		excludeKeys - (array of strings) list of key names that should not be displayed in the object tree;
									defaults to none (an empty array)
		includeKeys - (array of strings) list of key names to explicitly include in the object tree;
									defaults to none (an empty array)
		
		Events:
		onLeafClick - (function) function called when a leaf (a node with no children) is clicked; see data section below for arguments passed to event.
		onBranchClick - (function) function called when a branch (a node with children) is clicked; see data section below for arguments passed to event.
		
		Data passed to events:
		li - (element) the dom element that was clicked
		key - (string) the key of the value in the tree
		value - (mixed) the corresponding value of the item clicked in the tree
		path - (string) the path to the parent node of the item clicked; the path + "." + key will give you the full path to the value.
		nodePath - (string) the path to the node in the tree; computed as path + "." + key + "NODE"; used to find the injection parent for the new list items.
		event - (event) the event object for the clicked so it can be manipulated; it has already been stopped.		
	*/
var ObjectBrowser = new Class({
	options: {
		onLeafClick: Class.empty,
		onBranchClick: function(data){
			this.showLevel(data.path?data.path+'.'+data.key:data.key, data.nodePath);
		},
		initPath: '',
		buildOnInit: true,
		data: {},
		excludeKeys: [],
		includeKeys: []
	},
	initialize: function(container, options){
		this.container = $(container);
		this.setOptions(options);
		this.data = $H(this.options.data);
		this.levels = {};
		this.elements = {};
		if(this.options.buildOnInit) this.showLevel(this.options.initPath, this.container);
	},
	//gets a member of the object by path; eg "fruits.apples.green" will return the value at that path.
	//path - the string path
	//parent - (boolean) if true, will return the parent of the item found ( in the example above, fruits.apples)
	getMemberByPath: function(path, parent){
		if (path === "" || path == "top") return this.data.obj;
		var member = this.data.obj;
		var steps = path.split(".");
		if (parent) steps.pop();
		steps.each(function(p){
			if (p === "") return;
			if (member[p]) member = member[p];
			else if ($chk(Number(p)) && member[Number(p)]) member = member[Number(p)];
			else member = this.data.obj;
		}, this);
		return (member == this.data)?false:member;
	},
	//replaceMemberByPath will set the location at the path to the value passed in
	replaceMemberByPath: function(path, value){
		if (path === "" || path == "top") return this.data = $H(value);
		var parentObj = this.getMemberByPath( path, true );
		parentObj[path.split(".").pop()] = value;
		return this.data;
	},
	//gets the path for a given dom node.
	getPathByNode: function(el) {
		var elements = $H(this.elements);
		return elements.keys()[elements.values.indexOf(el)];
	},
	//validates that a key is a valid node value
	//against options.includeKeys and options.excludeKeys
	validLevel: function(key){
		return (!this.options.excludeKeys.contains(key) && 
			 (!this.options.includeKeys.length || this.options.includeKeys.contains(key)));
	},
	//builds a level into the interface given a path
	buildLevel:function(path) {
		//if the path ends in a dot, remove it
		if (path.test(".$")) path = path.substring(0, path.length);
		//get the corresponding level for the path
		var level = this.getMemberByPath(path);
		//if the path already has been built, return
		if (this.levels[path]) return this.levels[path];
		//create the section
		var section = new Element('ul');
		switch($type(level)) {
			case "function":
					this.buildNode(level, "function()", section, path, true);
				break;
			case "string": case "number":
					this.buildNode(level, null, section, path, true);
				break;
			case "array":
				level.each(function(node, index){
					this.buildNode(node, index, section, path, ["string", "function"].contains($type(node)));
				}.bind(this));
				break;
			default:
				$H(level).each(function(value, key){
					var db = false;
					if (key == "element_dimensions") db = true;
					if (db) dbug.log(key);
					if (this.validLevel(key)) {
						if (db) dbug.log('is valid level');
						var isLeaf;
						if ($type(value) == "object") {
							isLeaf = false;
							$each(value, function(v, k){
								if (this.validLevel(k)) {
									if (db) dbug.log('not a leaf!');
									isLeaf = false;
								} else {
									isLeaf = true;
								}
							}, this);
							if (isLeaf) value = false;
						}
						if (db) dbug.log(value, key, section, path, $chk(isLeaf)?isLeaf:null);
						this.buildNode(value, key, section, path, $chk(isLeaf)?isLeaf:null);
					}
				}, this);
		}
		//set the resulting DOM element to the levels map
		this.levels[path] = section;
		//return the section
		return section;
	},
	//gets the parent node for an element
	getParentFromPath: function(path){
		return this.elements[(path || "top")+'NODE'];
	},
	//displays a level given a path
	//if the level hasn't been built yet,
	//the level is built and then injected
	//into the target using the given method
	//example:
	//showLevel("fruit.apples", "fruit", "injectInside");
	//note that target and method are set to the parent path and injectInside by default
	showLevel: function(path, target, method){
		target = target || path;
		if (! this.elements[path]) 
			this.elements[path] = this.buildLevel(path)[method||"injectInside"](this.elements[target]||this.container);
		else this.elements[path].toggle();
		this.elements[path].getParent().toggleClass('collapsed');
	},
	//builds a node given the arguments:
	//value - the value of the node
	//key - the key of the node
	//section - the container where this node goes; typically a section generated by buildLevel
	//path - the path to this node
	//leaf - boolean; true if this is a leaf node
	//note: if the key or the value is an empty string, leaf will be set to true.
	buildNode: function(value, key, section, path, leaf){
		if (key==="" || value==="") leaf = true;
		if(!this.validLevel(key)) return null;
		var nodePath = (path?path+'.'+key:key)+'NODE';
		var lnk = this.buildLink((leaf)?value||key:$chk(key)?key:value, leaf);
		var li = new Element('li').addClass((leaf)?'leaf':'branch collapsed').adopt(lnk).injectInside(section);
		lnk.addEvent('click', function(e){
			new Event(e).stopPropagation();
			if (leaf) {
				this.fireEvent('onLeafClick', {
					li: li, 
					key: key, 
					value: value, 
					path: path,
					nodePath: nodePath,
					event: e
				});
			} else {
				this.fireEvent('onBranchClick', {
					li: li, 
					key: key, 
					value: value, 
					path: path,
					nodePath: nodePath,
					event: e
				});
			}							
		}.bind(this));
		this.elements[nodePath] = li;
		return li;
	},
	//builds a link for a given key
	buildLink: function(key) {
		if($type(key) == "function") {
			key = key.toString();
			key = key.substring(0, key.indexOf("{")+1)+"...";
		}
		return new Element('a', {
			href: "javascript: void(0);"
		}).setHTML(key);
	}
});
ObjectBrowser.implement(new Options, new Events);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/ObjectBrowser.js,v $
$Log: ObjectBrowser.js,v $
Revision 1.5  2007/11/29 19:13:36  newtona
missed a semi-colon in ObjectBrowser...

Revision 1.4  2007/11/19 23:23:05  newtona
CNETAPI: added method "getMany" to all the CNETAPI.Utils.* classes so that you can get numerous items in one request.
ObjectBrowser: improved exclusion handling for child elements
jlogger.js, element.position.js: docs update
Fx.Sort: cleaning up tabbing
string.cnet: just reformatting the logic a little.

Revision 1.3  2007/10/15 22:10:24  newtona
adding number as an object type in objectbrowser

Revision 1.2  2007/10/09 22:39:26  newtona
documented CNETAPI.Category.Browser, ObjectBrowser
doc tweaks on other files
rebuilding docs to javascript.cnet.com/docs


*/
/*	Script: stickyWin.default.layout.js
		Creates an html holder for in-page popups using a default style.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		mootools - <Moo.js>, <Utility.js>, <Common.js>, <Function.js>, <Element.js>, <Array.js>, <String.js>
		cnet - <simple.template.parser.js>
		
		Function: stickyWinHTML
		Returns a DOM element for in-page popups (<stickyWin>) with a default style.
		
		Arguments:
		caption - (string) the caption for the popup window
		body - (string or DOM element) content for the popup
		options - a key/value set of options
		
		Options:
		width - (string) width for the box; defaults to 300px.
		css - (string) override for the css styles for the default html
		cssClass - (string; optional) adds a css class in addition to "DefaultStickyWin" to the container
		baseHref: (string) url to the path where the images in the default style are located.
							defaults to http://www.cnet.com/html/rb/assets/global/stickyWinHTML/.
		buttons - (array) array of key/value set of button options (see below)
		
		Buttons:
		text - (string) the text of the button
		onClick - (function) function to execute on click
		properties - (object) a name/value set of properties applied to the element using <Element.setProperties>
		properties.class - (string) a css class name for the button; defaults to "closeSticky" which closes the popup. You can give a different class name, and the button won't close the sticky when clicked. You can also give it an additional class name (className: 'closeSticky button') so that it will have your additional class but will still close the popup.
		
		Example:
(start code)
stickyWinHTML('the caption', 'this is the body', {
  width: '400px',
	buttons: [
		{
			text: 'close', 
			onClick: function(){alert('closed!')}
		},
		{
			text: 'okey-dokey', 
			onClick: function(){alert('ok!')},
			properties: {class: 'ok'} //don't close though
		},
		{
			text: 'blah', 
			onClick: function(){alert('blah!')},
			properties: {
				class: 'closeSticky blah', //still closes
				style: 'width: 100px, border: 1px solid red',
				title: 'blah! I say!'
			}
		}
	]
});
(end)
		
		Resulting HTML:
		The HTML generated by this function looks like this:
(start code)
<div class="DefaultStickyWin">
	<div class="top">
		<div class="top_ul"></div>
		<div class="top_ur">
			<div class="closeButton closeSticky"></div>
			<h1 style="width: 335px;" class="caption">the caption</h1>
		</div>
	</div>
	<div class="middle">
		<div class="body">this is the body</div>
	</div>
	<div class="closeBody">
		<div class="closeButtons">
			<a class="closeSticky button">close</a>
			<a class="ok button">okey-dokey</a>
			<a class="closeSticky blah button" title="blah! I say!" style="width: 100px, border; 1px solid red">blah</a>
		</div>
	</div>
	<div class="bottom">
		<div class="bottom_ll"></div>
		<div class="bottom_lr"></div>
	</div>
</div>
(end)
	*/
function stickyWinHTML (caption, body, options){
	options = $merge({
		width: '300px',
		css: "div.DefaultStickyWin div.body{font-family:verdana; font-size:11px; line-height: 13px;}"+
			"div.DefaultStickyWin div.top_ul{background:url(%baseHref%full.png) top left no-repeat; height:30px; width:15px; float:left}"+
			"div.DefaultStickyWin div.top_ur{position:relative; left:0px !important; left:-4px; background:url(%baseHref%full.png) top right !important; height:30px; margin:0px 0px 0px 15px !important; margin-right:-4px; padding:0px}"+
			"div.DefaultStickyWin h1.caption{margin:0px 5px 0px 0px; overflow: hidden; padding:0; font-weight:bold; color:#555; font-size:14px; position:relative; top:8px; left:5px; float: left; height: 22px;}"+
			"div.DefaultStickyWin div.middle, div.DefaultStickyWin div.closeBody {background:url(%baseHref%body.png) top left repeat-y; margin:0px 20px 0px 0px !important;	margin-bottom: -3px; position: relative;	top: 0px !important; top: -3px;}"+
			"div.DefaultStickyWin div.body{background:url(%baseHref%body.png) top right repeat-y; padding:8px 30px 8px 0px; margin-left:5px; position:relative; right:-20px}"+
			"div.DefaultStickyWin div.bottom{clear:both}"+
			"div.DefaultStickyWin div.bottom_ll{background:url(%baseHref%full.png) bottom left no-repeat; width:15px; height:15px; float:left}"+
			"div.DefaultStickyWin div.bottom_lr{background:url(%baseHref%full.png) bottom right; position:relative; left:0px !important; left:-4px; margin:0px 0px 0px 15px !important; margin-right:-4px; height:15px}"+
			"div.DefaultStickyWin div.closeButtons{text-align: center; background:url(%baseHref%body.png) top right repeat-y; padding: 0px 30px 8px 0px; margin-left:5px; position:relative; right:-20px}" +
			"div.DefaultStickyWin a.button:hover{background:url(%baseHref%big_button_over.gif) repeat-x}"+
			"div.DefaultStickyWin a.button {background:url(%baseHref%big_button.gif) repeat-x; margin: 2px 8px 2px 8px; padding: 2px 12px; cursor:pointer; border: 1px solid #999 !important; text-decoration:none; color: #000 !important;}"+
			"div.DefaultStickyWin div.closeButton{width:13px; height:13px; background:url(%baseHref%closebtn.gif) no-repeat; position: absolute; right: 0px; margin:10px 15px 0px 0px !important; cursor:pointer}",
		cssClass: '',
		baseHref: 'http://www.cnet.com/html/rb/assets/global/stickyWinHTML/',
		buttons: []
/*	These options are deprecated:		
		closeTxt: false,
		onClose: Class.empty,
		confirmTxt: false,
		onConfirm: Class.empty	*/
	}, options);
	//legacy support
	if(options.confirmTxt) options.buttons.push({text: options.confirmTxt, onClick: options.onConfirm || Class.empty});
	if(options.closeTxt) options.buttons.push({text: options.closeTxt, onClick: options.onClose || Class.empty});

	window.addEvent('domready', function(){
		try {
			if(!$('defaultStickyWinStyle')) {
				var css = simpleTemplateParser.parseTemplate(options.css, options);
				if(window.ie) css = css.replace(new RegExp('png', 'gi'),'gif');
				var styler = new Element('style').setProperty('id','defaultStickyWinStyle').injectInside($$('head')[0]);
				if (!styler.setText.attempt(css, styler)) styler.appendText(css);
			}
		}catch(e){dbug.log('error: %s',e);}
	}.bind(this));

	caption = $pick(caption, '%caption%');
	body = $pick(body, '%body');
	var container = new Element('div').setStyle('width', options.width).addClass('DefaultStickyWin');
	if(options.cssClass) container.addClass(options.cssClass);
	//header
	var h1Caption = new Element('h1').addClass('caption').setStyle('width', (options.width.toInt()-60)+'px');

	if($(caption)) h1Caption.adopt(caption);
	else h1Caption.setHTML(caption);
	
	var bodyDiv = new Element('div').addClass('body');
	if($(body)) bodyDiv.adopt(body);
	else bodyDiv.setHTML(body);
	
	
	container.adopt(
		new Element('div').addClass('top').adopt(
				new Element('div').addClass('top_ul')
			).adopt(
				new Element('div').addClass('top_ur').adopt(
						new Element('div').addClass('closeButton').addClass('closeSticky')
					).adopt(h1Caption)
			)
	);
	//body
	container.adopt(new Element('div').addClass('middle').adopt(bodyDiv));
	//close buttons
	if(options.buttons.length > 0){
		var closeButtons = new Element('div').addClass('closeButtons');
		options.buttons.each(function(button){
			if(button.properties && button.properties.className){
				button.properties['class'] = button.properties.className;
				delete button.properties.className;
			}
			var properties = $merge({'class': 'closeSticky'}, button.properties);
			new Element('a').addEvent('click',
				button.onClick || Class.empty).appendText(
				button.text).injectInside(closeButtons).setProperties(properties).addClass('button');
		});
		container.adopt(new Element('div').addClass('closeBody').adopt(closeButtons));
	}
	//footer
	container.adopt(
		new Element('div').addClass('bottom').adopt(
				new Element('div').addClass('bottom_ll')
			).adopt(
				new Element('div').addClass('bottom_lr')
		)
	);
	return container;
};

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/stickyWin.default.layout.js,v $
$Log: stickyWin.default.layout.js,v $
Revision 1.29  2007/10/23 23:10:26  newtona
added mootools debugger to cvs
new file: setAssetHref.js; enables you to quickly set the location of image assets contained in the framework so you don't use CNET's versions, which can sometimes be slow.

Revision 1.28  2007/09/24 20:55:49  newtona
new file: StickyWin.Ajax - adds ajax support to all stickywin classes (creates new classes, just append .Ajax to any of the existing ones)
updated redball common full to include StickyWin.Ajax
date.picker, product.picker - updated syntax to use Element.empty
form.validator - now passes along the event object to the onFormValidate event so that the form submit event can be stopped if you like
popupdetails - added html response support; you can now return the html you wish to display rather than a json object; only applies to ajax. Also added a cache so that multiple requests are not made for the same url.
stickyWinHTML - ractored so that options are now, you know, *optional*
MooScroller - added support for width option for horizontal scrolling

Revision 1.27  2007/09/15 00:07:08  newtona
collission in last check in; merged and re-doing.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
fixed a bug with validate-digits in form validator
new class: TagMaker
implemented TagMaker into simple.editor
updated caption (which is the drag handle for stickyWinHTML) to be the entire width of the caption area
html.table: docs update
tabswapper: docs update

Revision 1.26  2007/08/20 18:11:49  newtona
Iframeshim: better handling for methods when the shim isn't ready
stickyWin: fixed a bug for the cssClass option in stickyWinHTML
html.table: push now returns the dom elements it creates
jsonp: fixed a bug; request no longer requires a url argument (my bad)
Fx.SmoothMove: just tidying up some syntax.
element.dimensions: updated getDimensions method for computing size of elements that are hidden; no longer clones element

Revision 1.25  2007/07/18 16:23:35  newtona
a little style tweaking with stickywin default html

Revision 1.24  2007/07/18 16:15:21  newtona
forgot to bind the style objects in the setText.attempt method...

Revision 1.23  2007/07/16 21:00:21  newtona
using Element.setText for all style injection methods (fixes IE6 problems)
moving Element.setText to element.legacy.js; this function is in Mootools 1.11, but if your environment is running 1.0 you'll need this.

Revision 1.22  2007/07/07 01:26:44  newtona
stickyWinHTML's caption no longer has it's width defined by the width option; it's all css. this means we can resize the box or set the width to 100% or whatever.

Revision 1.21  2007/06/28 23:24:58  newtona
adding some css important rules to stickyWinHTML

Revision 1.20  2007/06/28 23:15:22  newtona
working around a bug in Mootools 1.11: Element.setText

Revision 1.19  2007/06/21 20:08:44  newtona
IE7 ignored the css definition; implemented Element.setText for anyone using Mootools < 1.11 and use that to set the css properties

Revision 1.18  2007/06/07 18:43:37  newtona
added CSS to autocompleter.js
removed string.cnet.js dependencies from template parser and stickyWin.default.layout.js

Revision 1.17  2007/05/17 19:45:43  newtona
product picker: hide() now hides tooltips; onPick passes in a 3rd argument that is the picker
stickyWinHTML: fixed a bug with className options for buttons
html.table: fixed a bug with className options for buttons

Revision 1.16  2007/05/16 20:09:41  newtona
adding new js files to redball.common.full
product.picker.js now has no picklets; these are in the implementations/picklets directory
ProductPicker now detects if there is no doctyp and, if not, sets the position of the picker to be fixed (no IE6 support)
small docs update in element.cnet.js
added new picklet: CNETProductPicker_PricePath
added new picklet: NewsStoryPicker_Path
new file: clipboard.js (allows you to insert text into the OS clipboard)
new file: html.table.js (automates building html tables)
new file: element.forms.js (for managing text inputs - get selected text information, insert content around selection, etc.)

Revision 1.15  2007/05/11 00:10:48  newtona
syntax fix

Revision 1.14  2007/05/07 21:37:12  newtona
docs update

Revision 1.13  2007/05/05 01:01:24  newtona
stickywinHTML: tweaked the options for buttons
element.cnet: tweaked smoothshow/hide css handling

Revision 1.12  2007/05/04 00:32:39  newtona
stickwinHTML: added the ability for buttons to not close the sticky (className option)
stickyWin: added .pin (see Element.pin.js)

Revision 1.11  2007/04/05 00:13:12  newtona
local.vars.js: removing $type.isNumber dependency
login.status.js: no change; fixed typo in docs
search.functions.js: removing $type.isNumber dependency
stickyWinDefaultLayout: infinite buttons!
iframeShim.js: fixed an ie bug that caused it to abort the page

Revision 1.10  2007/03/29 23:12:00  newtona
confirmer now checks for a bg color in IE6 to use crossfading (see Element.fxOpacityOk)
fixed an IE7 css layout issue in stickyDefaultHTML
StickyWin now uses Element.flush
StickyWinFx.Drag now temporarily shows the sticky win (with opacity 0) to execute makeDraggable and makeResizable to prevent a Safari bug

Revision 1.9  2007/03/13 19:09:29  newtona
fixed a typy - added event "close" instead of "click". duh.

Revision 1.8  2007/03/13 18:57:17  newtona
syntax fix

Revision 1.7  2007/03/13 18:49:56  newtona
added onClose action

Revision 1.6  2007/03/08 02:38:50  newtona
added close and confirm buttons

Revision 1.5  2007/03/02 01:31:53  newtona
fixed some css bugs in IE
fixed a bug where all blocks inherited the width of the first created

Revision 1.4  2007/02/24 00:21:56  newtona
fixed  a css bug

Revision 1.3  2007/02/22 21:27:43  newtona
moved product picker from utilities dir
fixed missing ; in stickywin html

Revision 1.2  2007/02/22 18:19:48  newtona
fixed a bug with the style writer
added a missing bind()

Revision 1.1  2007/02/21 00:41:48  newtona
*** empty log message ***


*//*	Script: stickyWin.js
		Creates a div within the page with the specified contents at the location relative to the element you specify; basically an in-page popup maker.

		Dependencies:
		Moo - <Moo.js>, <Common.js>, <Utility.js>, <Element.js>, <Function.js>, <Dom.js>, <Array.js>, <Window.Base.js>, <Window.Size.js>, <Events.js>
		CNET - <element.shortcuts.js>, <element.dimensions.js>, <element.position.js>, <element.pin.js>, <IframeShim.js>
		
		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)
		
		Class: StickyWin
		Creates a div within the page with the specified contents at the location relative to the element you specify; basically an in-page popup maker.

		Arguments:
		options - an object with key/value options
		
		Options:
			onDisplay - function to execute when the popup is shown
			onClose - function to execute when the popup is closed
			closeClassName - class name of the element(s) in your popup content that, 
					when clicked, should close the window; defaults to "closeSticky"
			pinClassName - class name of the elements(s) in your popup content that,
					when clicked, should pin the sticky in place; defaults to "pinSticky"
			content - the content of your popup; this should be layout html and your message or a dom element
			zIndex - the zIndex of the popup; defaults to 10000
			id - the id of the wrapper div that gets created that will contain your content; 
					defaults to 'StickyWin' + the date (so it's unique)
			className - optional class name for the wrapper dive that gets created that will
					contain your content
			position - "center", "upperRight", "bottomRight", "upperLeft", "bottomLeft"; the point in the popup that is positioned;
					defaults to 'center'
			edge - same options as position (center, upperRight, etc.) but specifies the edge of the stickyWin to position
				to the point specified in position. see <Element.setPosition> for details. Optional; defaults to the
				<Element.setPosition> default state.
			offset - object containing {x: # and y: #} (integers) the top and left offset from the element in the 
					page that the popup is relative to; this offset is applied to the center of the popup 
					or the corner, depending on  the value you specify in the 'position' option.
			relativeTo - a dom element to position the popup relative to; defaults to document.body (i.e. the window)
			width - an optional width for the wrapper div for your popup
			height - an optional height for the wrapper div for your popup
			timeout - (integer) an optional timeout interval to hide the popup after a specified time
			allowMultiple - (boolean) allow multiple instance of StickyWin on the page; defaults to true
			allowMultipleByClass - (boolean) allow multiple popups that share the same className as specified in 
				the className option; defaults to false
			showNow - display the popup on instantiation; defaults to true
			useIframeShim - use an <IframeShim> to mask content below the element; defaults to true.
			iframeShimSelector - the css selector to find the element within your popup under which 
				the iframe shim should be placed to obscure select lists and the like (see <IframeShim>)
			
	Example:
(start code)
var myWin = new StickyWin({
	content: '<div id="myWin">hi there!<br><a href="javascript:void(0);" class="closeSticky">close</a></div>'
});
//popups up a box in the middle of the window with "hi there" and a close link(end)
	*/
var StickyWin = new Class({
	options: {
		onDisplay: Class.empty,
		onClose: Class.empty,
		closeClassName: 'closeSticky',
		pinClassName: 'pinSticky',
		content: '',
		zIndex: 10000,
		className: '',
		//id: ... set above in initialize function
		edge: false, //see Element.setPosition in element.cnet.js
		position: 'center', //center, corner == upperLeft, upperRight, bottomLeft, bottomRight
		offset: {x:0,y:0},
		relativeTo: document.body, 
		width: false,
		height: false,
		timeout: -1,
		allowMultipleByClass: false,
		allowMultiple: true,
		showNow: true,
		useIframeShim: true,
		iframeShimSelector: ''
	},
	css: '.SWclearfix:after {content: "."; display: block; height: 0; clear: both; visibility: hidden;}'+
			 '.SWclearfix {display: inline-table;}'+
			 '* html .SWclearfix {height: 1%;}'+
			 '.SWclearfix {display: block;}',
	initialize: function(options){
		this.setOptions(options);
		this.id = this.options.id || 'StickyWin_'+new Date().getTime();
		this.makeWindow();
		if(this.options.content) this.setContent(this.options.content);
		if(this.options.showNow) this.show();
		//add css for clearfix
		window.addEvent('domready', function(){
			try {
				if(!$('StickyWinClearfix')) {
					var style = new Element('style').setProperty('id','StickyWinClearfix').injectInside($$('head')[0]);
					if (!style.setText.attempt(this.css, style)) style.appendText(this.css);
				}
			}catch(e){dbug.log('error: %s',e);}
		}.bind(this));
	},
	makeWindow: function(){
		this.destroyOthers();
		if(!$(this.id)) {
			this.win = new Element('div').setProperty('id',			this.id).addClass(this.options.className).addClass('StickyWinInstance').addClass('SWclearfix').setStyles({
				 	'display':'none',
					'position':'absolute',
					'zIndex':this.options.zIndex
				}).injectInside(document.body);
		} else this.win = $(this.id);
		if(this.options.width && $type(this.options.width.toInt())=="number") this.win.setStyle('width', this.options.width.toInt() + 'px');
		if(this.options.height && $type(this.options.height.toInt())=="number") this.win.setStyle('height', this.options.height.toInt() + 'px');
		return this;
	},
/*	Property: show
		Shows the popup.
	*/
	show: function(){
		this.fireEvent('onDisplay');
		if(!this.positioned) this.position();
		this.showWin();
		if(this.options.useIframeShim) this.showIframeShim();
		this.visible = true;
		return this;
	},
	showWin: function(){
		this.win.setStyle('display','block');
	},
/*	Property: hide
		Hides the popup.
	*/
	hide: function(){
		this.fireEvent('onClose');
		this.hideWin();
		if(this.options.useIframeShim) this.hideIframeShim();
		this.visible = false;
		return this;
	},
	hideWin: function(){
		this.win.setStyle('display','none');
	},
	destroyOthers: function() {
		if(!this.options.allowMultipleByClass || !this.options.allowMultiple) {
			$$('div.StickyWinInstance').each(function(sw) {
				if(!this.options.allowMultiple || (!this.options.allowMultipleByClass && sw.hasClass(this.options.className))) 
					sw.remove();
			}, this);
		}
	},
/*	Property: setContent
		Replaces the content of the popup with the content passed in.
		
		Arguments:
		html - the new content
	*/
	setContent: function(html) {
		if(this.win.getChildren().length>0) this.win.empty();
		if($type(html) == "string") this.win.setHTML(html);
		else if ($(html)) this.win.adopt(html);
		this.win.getElements('.'+this.options.closeClassName).each(function(el){
			el.addEvent('click', this.hide.bind(this));
		}, this);
		this.win.getElements('.'+this.options.pinClassName).each(function(el){
			el.addEvent('click', this.togglepin.bind(this));
		}, this);
		return this;
	},
	
	position: function(){
		this.positioned = true;
		this.win.setPosition({
			relativeTo: this.options.relativeTo,
			position: this.options.position,
			offset: this.options.offset,
			edge: this.options.edge
		});
		if(this.shim) this.shim.position();
		return this;
	},
/*	Property: pin
		Affixes the stickywin to a fixed position, even if the window is scrolled. See <Element.pin>.
	*/
	pin: function(pin) {
		if(!this.win.pin) {
			dbug.log('you must include element.pin.js!');
			return false;
		}
		this.pinned = $pick(pin, true);
		return this.win.pin(pin);
	},
/*	Property: unpin
		Affixes the stickywin to a fixed position, even if the window is scrolled. See <Element.unpin>.
	*/
	unpin: function(){
		this.pin(false);
	},
/*	Property: togglepin
		Toggle the pinned state of the Sticky;
	*/
	togglepin: function(){
		this.pin(!this.pinned);
	},
	makeIframeShim: function(){
		if(!this.shim){
			this.shim = new IframeShim({
				element: (this.options.iframeShimSelector)?this.win.getElement(this.options.iframeShimSelector) : $('StickyWinOverlay') || this.win,
				display: false,
				name: 'StickyWinShim'
			});
		}
	},
	showIframeShim: function(){
		if(this.options.useIframeShim) {
			this.makeIframeShim();
			this.shim.show();
		}
	},
	hideIframeShim: function(){
		if(this.options.useIframeShim)
			this.shim.hide();
	},
/*	Property: destroy
		Removes the popup element.
	*/
	destroy: function(){
		this.win.remove();
		if(this.options.useIframeShim) this.shim.remove();
		if($('StickyWinOverlay'))$('StickyWinOverlay').remove();
	}
});
StickyWin.implement(new Options);
StickyWin.implement(new Events);

var stickyWin = StickyWin;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/stickyWin.js,v $
$Log: stickyWin.js,v $
Revision 1.27  2007/11/29 19:00:57  newtona
- fixed an issue with StickyWin that could create inheritance issues because of a defect in $merge. this fixed an actual issue with DatePicker, specifically, having more than one on a page
- fixed an issue with the basehref in simple.error.popup

Revision 1.26  2007/07/18 16:15:21  newtona
forgot to bind the style objects in the setText.attempt method...

Revision 1.25  2007/07/16 21:00:21  newtona
using Element.setText for all style injection methods (fixes IE6 problems)
moving Element.setText to element.legacy.js; this function is in Mootools 1.11, but if your environment is running 1.0 you'll need this.

Revision 1.24  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.23  2007/05/11 00:10:48  newtona
syntax fix

Revision 1.22  2007/05/11 00:05:29  newtona
adding clearfix css to all sticky win instances

Revision 1.21  2007/05/04 01:22:47  newtona
added togglepin

Revision 1.19  2007/05/04 00:32:39  newtona
stickwinHTML: added the ability for buttons to not close the sticky (className option)
stickyWin: added .pin (see Element.pin.js)

Revision 1.18  2007/04/13 19:06:11  newtona
dependency update in the docs

Revision 1.17  2007/03/30 19:32:20  newtona
changing .flush to .empty

Revision 1.16  2007/03/29 23:12:00  newtona
confirmer now checks for a bg color in IE6 to use crossfading (see Element.fxOpacityOk)
fixed an IE7 css layout issue in stickyDefaultHTML
StickyWin now uses Element.flush
StickyWinFx.Drag now temporarily shows the sticky win (with opacity 0) to execute makeDraggable and makeResizable to prevent a Safari bug

Revision 1.15  2007/03/23 23:01:16  newtona
stickywin: implemented current options options method (no functional change)
stickywinFx: removed an old function that was empty; should have been gone a long time ago (getDefaultOptions)

Revision 1.14  2007/03/23 20:19:48  newtona
Iframeshim: added className; updated docs
StickyWin: added edge support (see Element.setPosition)

Revision 1.13  2007/03/08 00:06:13  newtona
stickyWin now empties it's content before setContent adds new stuff

Revision 1.12  2007/02/21 00:24:56  newtona
no change, but cvs says it's different. maybe a space?

Revision 1.11  2007/02/08 22:54:00  newtona
removed a comment

Revision 1.10  2007/02/08 20:50:54  newtona
fixed a bug where .show repositioned the window every time

Revision 1.9  2007/02/08 01:29:51  newtona
fixed syntax error

Revision 1.8  2007/02/07 20:51:41  newtona
implemented Options class
implemented Events class
StickyWin now uses Element.position

Revision 1.7  2007/01/26 18:24:41  newtona
docs update

Revision 1.6  2007/01/26 05:49:10  newtona
syntax update for mootools 1.0

Revision 1.5  2007/01/23 20:54:24  newtona
a little better position handling

Revision 1.4  2007/01/22 22:00:15  newtona
numerous bug fixes to modalizer, stickywin, and popupdetails
updated for mootools 1.0
fixed date validation in form.validator

Revision 1.3  2007/01/19 01:22:32  newtona
fixed a few syntax errors

Revision 1.2  2007/01/11 20:55:23  newtona
changed the way options are set, split up stickywin into 4 files, refactored popupdetails to use stickywin and modalizer

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.2  2007/01/09 01:26:04  newtona
docs syntax fix

Revision 1.1  2007/01/05 19:29:30  newtona
first check in


*/

/*	Script: stickyWinFx.js
		Extends <StickyWin> to create popups that fade in and out and can be dragged and resized (requires <stickyWinFx.Drag.js>).
	
		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)

		Dependencies:
		mootools - <Fx.Base.js>
		cnet - <stickyWin.js> and all its dependencies.
		optional - <stickyWinFx.Drag.js> and <Drag.Base.js>

		Class: StickyWinFx
		Creates a <StickyWin> that optionally fades in and out, is draggable, and is resizable (requires <stickyWinFx.Drag.js>).
		
		Arguments:
		options - an object with key/value options
		
		Options:
		see <StickyWin>; inherits all those options in addition to the following.
		
			fade - (boolean) fade in and out; defaults to true
			fadeDuration - (integer) the duration of the fade effect; defaults to 150
			fadeTransition - an <Fx.Transitions> to use for the fade effect; defaults to <Fx.Transitions.sineInOut>
		
		Additional Options:
		These options depend on <stickyWinFx.Drag.js> and <Drag.Base.js>; so they don't do anything if those
		files are not included in your environment.
		
			draggable - (boolean) make the popup draggable, defaults to false
			dragOptions - (object) optional options to pass to the drag effect
			dragHandleSelector - optional css selector to select the element(s) within in 
				your popup to use as a drag handle.
			resizable - (boolean) make the popup resizable or not; defaults to false
			resizeOptions - (object) optional options to pass to the resize effect
			resizeHandleSelector - optional css selector to select the element(s) within in 
				your popup to use as a resize handle.
		
		Example:
(start code)
var myWin = new StickyWinFx({
	content: '<div id="myWin">hi there!<br><a href="javascript:void(0);" class="closeSticky">close</a></div>',
	fadeDuration: 500,  //slow it down
	draggable: true, //make it draggable
	dragHandleSelector: 'img.handle' //get the img with the class "handle" for the handle
});
//fades in a box in the middle of the window with "hi there" and a close link(end)
//window is draggable using the image(s) with the class "handle"
(end)		
	*/
var StickyWinFx = StickyWin.extend({
	options: {
		fade: true,
		fadeDuration: 150,
		fadeTransition: Fx.Transitions.sineInOut,
		draggable: false,
		dragOptions: {},
		dragHandleSelector: 'h1.caption',
		resizable: false,
		resizeOptions: {},
		resizeHandleSelector: ''
	},
	setContent: function(html){
		this.parent(html);
		if(this.options.draggable) this.makeDraggable();
		if(this.options.resizable) this.makeResizable();
		return this;
	},	
	hideWin: function(){
		if(this.options.fade) this.fade(0);
		else this.win.hide();
	},
	showWin: function(){
		if(this.options.fade) this.fade(1);
		else this.win.show();
	},
	fade: function(to){
		if(!this.fadeFx) {
			this.win.setStyles({
				opacity: 0,
				display: 'block'
			});
			this.fadeFx = this.win.effect('opacity', {
				duration: this.options.fadeDuration,
				transition: this.options.fadeTransition
			});
		}
		if (to > 0) this.win.setStyle('display','block');
		this.fadeFx.clearChain();
		this.fadeFx.start(to).chain(function (){
			if(to == 0) this.win.setStyle('display', 'none');
		}.bind(this));
		return this;
	},
	makeDraggable: function(){
		dbug.log('you must include Drag.js, cannot make draggable');
	},
	makeResizable: function(){
		dbug.log('you must include Drag.js, cannot make resizable');
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/stickyWinFx.js,v $
$Log: stickyWinFx.js,v $
Revision 1.15  2007/09/07 22:19:28  newtona
popupdetails: updating options handling methodology
stickyWinFx: fixed a bug where, if you were fast enough, you could introduce a flicker bug - this is hard to produce so most people probably hadn't seen it

Revision 1.14  2007/08/30 17:09:27  newtona
stickyWinFx, modalizer: updated syntax a litle
jlogger: updated docs
fixed a bug in Fx.Sort w/ IE6

Revision 1.13  2007/03/29 23:12:00  newtona
confirmer now checks for a bg color in IE6 to use crossfading (see Element.fxOpacityOk)
fixed an IE7 css layout issue in stickyDefaultHTML
StickyWin now uses Element.flush
StickyWinFx.Drag now temporarily shows the sticky win (with opacity 0) to execute makeDraggable and makeResizable to prevent a Safari bug

Revision 1.12  2007/03/23 23:01:16  newtona
stickywin: implemented current options options method (no functional change)
stickywinFx: removed an old function that was empty; should have been gone a long time ago (getDefaultOptions)

Revision 1.11  2007/03/08 02:42:15  newtona
fixed an error where the handle was being assigned before the content

Revision 1.10  2007/02/27 21:46:43  newtona
docs update; fixing references

Revision 1.9  2007/02/22 18:20:18  newtona
fixed bug where the element faded out, but wasn't set to display-none

Revision 1.8  2007/02/21 00:26:52  newtona
added a default drag handle

Revision 1.7  2007/02/08 01:30:07  newtona
fixed syntax error

Revision 1.6  2007/02/07 20:51:41  newtona
implemented Options class
implemented Events class
StickyWin now uses Element.position

Revision 1.5  2007/01/26 05:50:58  newtona
added footer cvs tags


*/
/*	Script: stickyWinFx.Drag.js
		Implements drag and resize functionaity into <StickyWinFx>. See <StickyWinFx> for the options.
		
		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)

		Dependencies:
		<stickyWin.js>, <stickyWinFx.js>, and all their dependencies plus <Drag.Base.js>.
*/
if(typeof Drag != "undefined"){
	StickyWinFx.implement({
		makeDraggable: function(){
			var toggled = this.toggleVisible(true);
			if(this.options.useIframeShim) {
				this.makeIframeShim();
				var dragComplete = this.options.dragOptions.onComplete || Class.empty;
				this.options.dragOptions.onComplete = function(){
					dragComplete();
					this.shim.position();
				}.bind(this);
			}
			if(this.options.dragHandleSelector) {
				var handle = this.win.getElement(this.options.dragHandleSelector);
				if (handle) {
					handle.setStyle('cursor','move');
					this.options.dragOptions.handle = handle;
				}
			}
			this.win.makeDraggable(this.options.dragOptions);
			if (toggled) this.toggleVisible(false);
		}, 
		makeResizable: function(){
			var toggled = this.toggleVisible(true);
			if(this.options.useIframeShim) {
				this.makeIframeShim();
				var resizeComplete = this.options.resizeOptions.onComplete || Class.empty;
				this.options.resizeOptions.onComplete = function(){
					resizeComplete();
					this.shim.position();
				}.bind(this);
			}
			if(this.options.resizeHandleSelector) {
				var handle = this.win.getElement(this.options.resizeHandleSelector);
				if(handle) this.options.resizeOptions.handle = this.win.getElement(this.options.resizeHandleSelector);
			}
			this.win.makeResizable(this.options.resizeOptions);
			if (toggled) this.toggleVisible(false);
		},
		toggleVisible: function(show){
			if(!this.visible && window.khtml && $pick(show, true)) {
				this.win.setStyles({
					display: 'block',
					opacity: 0
				});
				return true;
			} else if(!$pick(show, false)){
				this.win.setStyles({
					display: 'none',
					opacity: 1
				});
				return false;
			}
			return false;
		}
	});
}
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/stickyWinFx.Drag.js,v $
$Log: stickyWinFx.Drag.js,v $
Revision 1.5  2007/03/29 23:12:00  newtona
confirmer now checks for a bg color in IE6 to use crossfading (see Element.fxOpacityOk)
fixed an IE7 css layout issue in stickyDefaultHTML
StickyWin now uses Element.flush
StickyWinFx.Drag now temporarily shows the sticky win (with opacity 0) to execute makeDraggable and makeResizable to prevent a Safari bug

Revision 1.4  2007/02/21 00:26:33  newtona
drag handle now gets cursor: move

Revision 1.3  2007/01/26 05:49:42  newtona
added docs

Revision 1.2  2007/01/22 22:00:15  newtona
numerous bug fixes to modalizer, stickywin, and popupdetails
updated for mootools 1.0
fixed date validation in form.validator

Revision 1.1  2007/01/11 20:55:23  newtona
changed the way options are set, split up stickywin into 4 files, refactored popupdetails to use stickywin and modalizer


*/
/*	Script: stickyWin.Modal.js
		This script extends <StickyWin> and <StickyWinFx> classes to add <Modalizer> functionality.

		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)

		Dependencies:
		cnet - <stickyWin.js>, and all their dependencies plus <modalizer.js>.
		optional - <stickyWinFx.js> 
	*/
var modalWinBase = {
	initialize: function(options){
		options = options||{};
		this.setModalOptions($merge(options.modalOptions||{}, {
			onModalHide: function(){
					this.hide(false);
				}.bind(this)
			}));
		this.parent(options);
	},
	show: function(showModal){
		if($pick(showModal, true))this.modalShow();
		this.parent();
	},
	hide: function(hideModal){
		if($pick(hideModal, true))this.modalHide();
		this.parent();
	}
};

/*	Class: StickyWinModal
		Creates a <StickyWin> that uses the functionality in <Modalizer> to overlay the document.
		
		Argument:
		options - an object with key/value options defined in <StickyWin> and <Modalizer>
	
		Options:
		inherits all the options of <StickyWin>
		modalOptions - options object for <Modalizer>
	*/
var StickyWinModal = StickyWin.extend(modalWinBase);
StickyWinModal.implement(new Modalizer);

/*	Class: StickyWinFxModal
		Creates a <StickyWinFx> that uses the functionality in <Modalizer> to overlay the document.
		
		Argument:
		options - an object with key/value options defined in <StickyWin>, <StickyWinFx> and <Modalizer>
		
		Argument:
		options - an object with key/value options defined in <StickyWin> and <Modalizer>
	
		Options:
		inherits all the options of <StickyWinFx>
		modalOptions - options object for <Modalizer>

	*/
var StickyWinFxModal = (typeof StickyWinFx != "undefined")?StickyWinFx.extend(modalWinBase):Class.empty;
try { StickyWinFxModal.implement(new Modalizer()); }catch(e){}
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/stickyWin.Modal.js,v $
$Log: stickyWin.Modal.js,v $
Revision 1.7  2007/02/21 00:25:29  newtona
updated options handling

Revision 1.6  2007/02/06 18:11:53  newtona
fixed a bug where the iframeshim was being left behind.

Revision 1.5  2007/01/27 08:40:21  newtona
that last fix didn't work. now it handles no options passed in.

Revision 1.4  2007/01/27 08:37:03  newtona
handle the posibility that no options are applied

Revision 1.3  2007/01/26 05:49:26  newtona
added docs

Revision 1.2  2007/01/22 22:00:15  newtona
numerous bug fixes to modalizer, stickywin, and popupdetails
updated for mootools 1.0
fixed date validation in form.validator

Revision 1.1  2007/01/11 20:55:23  newtona
changed the way options are set, split up stickywin into 4 files, refactored popupdetails to use stickywin and modalizer


*/
/*	Script: stickyWin.Ajax.js
		Adds ajax functionality to all the StickyWin classes.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		CNET - <StickyWin.js> and all its dependencies.
		Optional - <StickyWinFx.js>, <StickyWinFx.Drag.js>, <StickyWin.Modal.js>
		
		
		Class: StickyWin.Ajax
		Adds ajax functionality to the StickyWin class. See also <StickyWinFx.Ajax>, <StickyWinModal.Ajax>, and <StickyWinFxModal.Ajax>.
		
		Arguments:
		options - All the options in the relevant <StickyWin> class (<StickyWin>, <StickyWinFx>, etc) plus those listed below.
		
		Options:
		url - (string) the default url for the instance to hit for its content. See <update>
		XHRoptions - (object) options passed on in the ajax request; defaults to {method:'get'}.
		wrapWithStickyWinDefaultHTML - (boolean) if true, wraps the response in <stickyWinHTML>. defaults to false.
		caption - (string) if wrapping with <stickyWinHTML>, this caption will be used; defaults to empty string.
		stickyWinHTMLOptions - (object) if wrapping with <stickyWinHTML>, these options will be passed 
				along as the options to <stickyWinHTML>; defaults to an empty object.
		handleResponse - (function) handles the response from the server. Default will wrap the response
				html with <stickyWinHTML> if that option is enabled (which it isn't by default), then calls <StickyWin.setContent>
				and then <StickyWin.show>. This method is meant to be replaced with custom handlers if you want a different
				behavior (which is why it's an option).
	*/
var SWA = {
	options: {
		url: '',
		showNow: false,
		XHRoptions: {
			method: 'get'
		},
		wrapWithStickyWinDefaultHTML: false, 
		caption: '',
		stickyWinHTMLOptions:{},
		handleResponse: function(response){
			if(this.options.wrapWithStickyWinDefaultHTML) 
				response = stickyWinHTML(this.options.caption, response, this.options.stickyWinHTMLOptions);
			this.setContent(response);
			this.show();
		}
	},
	initialize: function(options){
		this.parent(options);
		this.createXHR();
	},
	createXHR: function(){
		var opt = $merge(this.options.XHRoptions, {
			onSuccess: this.options.handleResponse.bind(this)
		});
		this.XHR = new XHR(opt);
	},
	update: function(url){
		this.XHR.send(url||this.options.url);
		return this;
	}
};
try {	StickyWin.Ajax = StickyWin.extend(SWA); } catch(e){}
try {	StickyWinFx.Ajax = StickyWinFx.extend(SWA); } catch(e){}
try {	StickyWinModal.Ajax = StickyWinModal.extend(SWA); } catch(e){}
try {	StickyWinFxModal.Ajax = StickyWinFxModal.extend(SWA); } catch(e){}
/* do not edit below this line */	 
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/stickyWin.Ajax.js,v $
$Log: stickyWin.Ajax.js,v $
Revision 1.4  2007/09/24 22:10:19  newtona
fixed a bug in popupdetails;
StickyWin*.Ajax.update() now returns the instance (return this);

Revision 1.3  2007/09/24 21:28:05  newtona
fixed a typo in stickyWin.Ajax.js

Revision 1.2  2007/09/24 21:07:45  newtona
hey, semi-colons!

Revision 1.1  2007/09/24 20:55:49  newtona
new file: StickyWin.Ajax - adds ajax support to all stickywin classes (creates new classes, just append .Ajax to any of the existing ones)
updated redball common full to include StickyWin.Ajax
date.picker, product.picker - updated syntax to use Element.empty
form.validator - now passes along the event object to the onFormValidate event so that the form submit event can be stopped if you like
popupdetails - added html response support; you can now return the html you wish to display rather than a json object; only applies to ajax. Also added a cache so that multiple requests are not made for the same url.
stickyWinHTML - ractored so that options are now, you know, *optional*
MooScroller - added support for width option for horizontal scrolling


*/
/*	Script: simple.error.popup.js
		The function in this script just makes a little alert box with a close button.
		
		Dependencies:
		CNET - <stickyWin.js>, <stickyWin.Modal.js>, <stickyWin.default.layout.js>
		Assets - images located at www.cnet.com/html/rb/js/assets/global/simple.error.popup (see css inline)
		
		Function: simpleErrorPopup
		This function just makes a little alert box with a close button.
		
		Arguments:
		msghdr - the caption for the window
		msg - the error message
		baseHref - the location of the icon_problems_sm.gif file; defaults to cnet's domain.
		
		Example:
		>simpleErrorPopup('Woops!', 'Oh nos! I've got five Internets open!');
		
		Returns: 
		An instance of <StickyWinModal>
	*/
var simpleErrorPopup = function(msghdr, msg, baseHref) {
	baseHref = baseHref||"http://www.cnet.com/html/rb/assets/global/simple.error.popup";
	msg = '<p class="errorMsg SWclearfix">' +
						'<img src="'+baseHref+'/simple.error.popup/icon_problems_sm.gif"'+
						' class="bang clearfix" style="float: left; width: 30px; height: 30px; margin: 3px 5px 5px 0px;">'
						 + msg + '</p>';
	var body = stickyWinHTML(msghdr, msg, {width: '250px'});
	return new StickyWinModal({
		modalOptions: {
			modalStyle: {
				zIndex: 11000
			}
		},
		zIndex: 110001,
		content: body,
		position: 'center' //center, corner
	});
};
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/utilities/simple.error.popup.js,v $
$Log: simple.error.popup.js,v $
Revision 1.13  2007/11/29 19:00:57  newtona
- fixed an issue with StickyWin that could create inheritance issues because of a defect in $merge. this fixed an actual issue with DatePicker, specifically, having more than one on a page
- fixed an issue with the basehref in simple.error.popup

Revision 1.12  2007/10/23 23:10:26  newtona
added mootools debugger to cvs
new file: setAssetHref.js; enables you to quickly set the location of image assets contained in the framework so you don't use CNET's versions, which can sometimes be slow.

Revision 1.11  2007/10/18 21:43:35  newtona
updating components that reference images and assets at www.cnet.com to allow for easy over-writing for that source url

Revision 1.10  2007/05/11 00:05:29  newtona
adding clearfix css to all sticky win instances

Revision 1.9  2007/05/07 21:38:33  newtona
zindex tweak so that error popup is higher than the default stickywin (in case there's an error on a page with a stickywin)

Revision 1.8  2007/03/08 23:31:22  newtona
strict javascript warnings cleaned up
removed deprecated dbug loadtimers
dbug enables on debug.cookie()

Revision 1.7  2007/02/21 00:42:30  newtona
docs update

Revision 1.6  2007/02/21 00:31:32  newtona
using default sticky win html now.

Revision 1.5  2007/02/06 18:12:26  newtona
changed the way that the css was added to the dom. only does this once now, even if you execute the function numerous times.

Revision 1.4  2007/01/26 05:56:03  newtona
syntax update for mootools 1.0
docs update

Revision 1.3  2007/01/22 21:55:18  newtona
changed image paths to www.cnet.com
updated stickyWin namespace to StickyWin

Revision 1.2  2007/01/11 20:56:05  newtona
docs change

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.2  2007/01/05 18:54:51  newtona
some documentation changes

Revision 1.1  2007/01/05 18:32:04  newtona
first check in


*/
/*	Script: date.picker.js
		Allows the user to enter a date in any popuplar format or choose from a calendar.
		
		Dependencies:
		mootools - <Moo.js>, <Utility.js>, <Common.js>, <Function.js>, <Element.js>, <Array.js>, <String.js>, <Event.js>
		cnet - <stickyWin.js> and all its dependencies
		optional - <Drag.Base.js>, <stickyWinFx.js>
		
		Authors:
		Paul Anderson
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Class: DatePicker
		Allows the user to enter a date in any popuplar format or choose from a calendar.
		
		Arguments:
		input - the id of a text input, or a reference to the element itself
		options - an object with key/value settings
		
		Options:
		calendarId - (string) the id of the calendar to show; defaults to "popupCalendar" + the date (so it’s unique)
		months - (array) the months of the year. Defaults to ["Janurary", "February", etc.]
		days - (array) the days of the week. Defaults to ["Su", "Mo", "Tu", etc.]
		stickyWinOptions - (object) options to pass along to the stickyWin popup object. Defaults to {position: 'bottomLeft', offset: {x:10, y:10}}
		stickyWinToUse - which <StickyWin> class to use (<StickyWin>, <StickyWinFx>, etc.)
		draggable: (boolean) whether or not the popup is draggable. Requires <Drag.Base.js>. Defautls to true (if <Drag.Base.js> is not present, the element won't be draggable, but it won't throw an error.
		dragOptions: (object) options to pass on to <Drag.Base>
		additionalShowLinks - (array) collection of dom elements (or ids) that should show the calendar for the input
		showOnInputFocus - (boolean) show the calendar when the input is focused. Defaults to true. If set to false, you must specify at least one object in additionalShowLinks if you want the calendar to be accessible. **NOTE: you can set this to false and specy no additional show links that this class will still auto-format date inputs for you**
		useDefaultCss - (boolean) use the default css described in this class. If false, you must define your own css. Defaults to true.
		hideCalendarOnPick - (boolean) hide the calendar when the user chooses a date. Defaults to true.
		onPick - function to execute when the user choose a date
		onShow - function to execute when the calendar appears
		onHide - function to execute when the calendar is hidden
		CSS:
		The calendar popup builds a table with all the dates and months and whatnot. You may style this table using the following descriptors:

		div.calendarHolder - the div containing the calendar table.
		table.datePicker - the table with the calendar values
		tr.dateNav - the row containing the forward, back, and close buttons, and the month name
		tr.dayNames - the row containing the names of the days of the week
		tr.dayRow - one of the rows containing actual dates
		td.today - the td that contains today's date
		td.selectedDate - the td that contains the user's selection
		td.otherMonthDate - tds that contain dates before or after the current selected month
		
		Autoformatting and Date format: 
		This class will take a user's input of a date value and convert it into MM/DD/YYYY. If the user inputs 01.02.03,
		this class will update it to 01/02/2003 on the blur event of the field. The same is true for 01.02.2003, 01/02/03,
		01 02 2003, 2003.02.01, and so on.
		
		If you need this class to return a different format, you can use <Class.implement> to create your own formatter. If
		javascript had a better date object, we wouldn't have to do it like this, but what are ya gonna do?
		
		Example:
(start code)
<input type="text" name="date" id="dateInput"> <img src="calendar.gif" id="calendarImg">
<script>
new DatePicker('dateInput', {
	additionalShowLinks: ['calendarImg'],
	showOnInputFocus: false
});
(end)
	*/
	var DatePicker = new Class({
		options: {
			baseHref: 'http://www.cnet.com/html/rb/assets/global/',
			defaultCss: 'div.calendarHolder{width:210px; height:182px; padding-left:8px; padding-top:1px; '+
				'background:url(%baseHref%/datePicker/calendar.back.png) no-repeat} '+
			  '* html div.calendarHolder {background:url(%baseHref%/datePicker/calendar.back.gif) no-repeat}'+
				'table.datePicker * {font-size:11px; line-height:16px;} '+
				'table.datePicker{margin:6px 0px 0px 0px; width:190px; padding:0px 5px 0px 5px} '+
				'table.datePicker td{cursor:pointer; text-align:center} '+
				'table.datePicker img.closebtn{margin-top:2px} '+
				'tr.dateNav{height:22px; margin-top:8px} '+
				'tr.dayNames td{color:#666; font-weight:bold; border-bottom:1px solid #ddd} '+
				'table.datePicker tr.dayRow td:hover{background:#ccc} '+
				'td.today{color:#bb0904} '+
				'td.otherMonthDate{border:1px solid #fff; color:#666; background:#f3f3f3} '+
				'td.selectedDate{border:1px solid #20397b; background:#dcddef}',
			fullDay: 86400000,
			calendarId: false,
			stickyWinOptions: {
				position: "bottomLeft",
				offset: {x:10, y:10},
				fadeDuration: 400
			},
			draggable: true,
			dragOptions: {},
			showOnInputFocus: true,
			useDefaultCss: true,
			hideCalendarOnPick: true,
			onPick: Class.empty,
			onShow: Class.empty,
			onHide: Class.empty
		},
			
		initialize: function(input, options){
			//gotta declare array options here to avoid inheritance corruption
			this.options.months = ["January","February","March","April","May","June","July",
														 "August","September","October","November","December"];
			this.options.days = ["Su","Mo","Tu","We","Th","Fr","Sa"];
			this.options.additionalShowLinks = [];

			var StickyWinToUse = (typeof StickyWinFx == "undefined")?StickyWin:StickyWinFx;
			this.setOptions({
				stickyWinToUse: StickyWinToUse
			}, options);
			if(!this.options.calendarId) this.options.calendarId = "popupCalendar" + new Date().getTime();
			this.input = $(input);
			if(this.options.useDefaultCss)this.writeCss();
			this.setUpObservers();
			this.getCalendar();
		},
		setUpObservers: function(){
			if (this.options.showOnInputFocus) this.input.addEvent('focus', this.show.bind(this));
			try {this.input.addEvent('blur', this.updateInput.bind(this));}catch(e){} //ie sometimes doesn't like this.
			this.options.additionalShowLinks.each(function(lnk){$(lnk).addEvent('click', this.show.bind(this))}, this);
		},
		writeCss: function(css) {
			css = $pick(css,this.options.defaultCss).replace("%baseHref%", this.options.baseHref, "g");
			window.addEvent('domready', function(){
				try {
					if(!$('datePickerStyle')) {
						var style = new Element('style').setProperty('id','datePickerStyle').injectInside($$('head')[0]);
						if (!style.setText.attempt(css, style)) style.appendText(css);
					}
				}catch(e){dbug.log('error: %s',e);}
			});
		},
/*	Property: updateInput
		Takes a given date and updates the input field with its value.
		
		Arguments:
		date - a date or a string that is parsable as a date (see <validDate>)
	*/
		updateInput: function(date){
			if(!$type(date) == "string" || (date && !date.getTime)) date = this.input.getValue();
			var dateStr = this.formatDate(this.validDate(date));
			if($type(dateStr) == "string") {
				this.input.value = dateStr;
				return dateStr;
			}
			return date;
		},
/*	Property: validDate
		Parses a string into a Date object and returns it.
		
		Arguments:
		val - (optional) the date to parse. a string or a date object. If no value is specified, the input 
			value will be used instead.
		
		Accepted formats:
		01.02.03, 01.02.2003, 01/02/03, 01 02 2003, 2003.02.01, and so on.
	*/		
		validDate: function(val) {
			val = $pick(val, this.input.getValue());
			val = val.replace(/^\s+|\s+$/g,"");
			var asDate = Date.parse(val);
			if (isNaN(asDate)) asDate = Date.parse(val.replace(/[^\w\s]/g,"/"));
			if (isNaN(asDate)) asDate = Date.parse(val.replace(/[^\w\s]/g,"/") + "/" + new Date().getFullYear());
			if (!isNaN(asDate)) {
				var newDate = new Date(asDate);
				if (newDate.getFullYear() < 2000 && val.indexOf(newDate.getFullYear()) < 0) {
					newDate.setFullYear(newDate.getFullYear() + 100);
				}
				return newDate;
			} else return asDate;
		},
/*	Property: formatDate
		formats a date object into MM/DD/YYYY.
		
		Arguments:
		date - (Date object) the date to format.
	*/
		formatDate: function (date) {
			try {
				// always "get" as UTC, without timezone, so there's no confusion over the calendar day
					var fd = ((date.getUTCMonth() < 9) ? "0" : "") + (date.getUTCMonth()+1) + "/";
					fd += ((date.getUTCDate() < 10) ? "0" : "") + date.getUTCDate() + "/";
					fd += date.getUTCFullYear();
					return fd;
			} catch(e){return date}
		},
		
		zeroHourGMT: function(date) {
			date.setTime(date.getTime() - date.getTime() % 86400000);
			return date;
		},
		
		getCalendar: function() {
			if(!this.calendar) {
				var cal = new Element("table").setProperties({
					'id': this.options.calendarId,
					'border':'0',
					'cellpadding':'0',
					'cellspacing':'0'
				});
				cal.addClass('datePicker');
		    $(cal.insertRow(0).insertCell(0)).appendText("x");
				for (var c=0;c<6;c++) $(cal.rows[0]).adopt(cal.rows[0].cells[0].cloneNode(true));
				for (var r=0;r<7;r++) $(cal.rows[0].parentNode).adopt(cal.rows[0].cloneNode(true));
				$(cal.rows[1]).addClass('dayNames');
				for (var r=2;r<8;r++) $(cal.rows[r]).addClass('dayRow');
				for (var d=0;d<7;d++) cal.rows[1].cells[d].firstChild.data = this.options.days[d];
				for (var t=6;t>3;t--) cal.rows[0].deleteCell(t);
				$(cal.rows[0]).addClass('dateNav');
				if(!window.ie6)cal.rows[0].cells[0].firstChild.data=String.fromCharCode(9668);
				else cal.rows[0].cells[0].firstChild.data="<";
				cal.rows[0].cells[1].colSpan=4;
				if(!window.ie6) cal.rows[0].cells[2].firstChild.data=String.fromCharCode(9658);
				else cal.rows[0].cells[2].firstChild.data=">";
				cal.rows[0].cells[3].firstChild.data=String.fromCharCode(215);
				$(cal.rows[0].cells[3].empty()).adopt(this.getCloseImg());
					//xb.adopt(xb.previousSibling);
				cal.addEvent('click', this.clickCalendar.bind(this));
				this.calendar = cal;
				this.container = new Element('div').adopt(cal).addClass('calendarHolder');
				//make stickywin
				this.options.stickyWinOptions.content = this.container;
				this.options.stickyWinOptions.showNow = false;
				this.options.stickyWinOptions.relativeTo = this.input;
				this.stickyWin = new this.options.stickyWinToUse(this.options.stickyWinOptions);
				if(this.options.draggable) {
					try {
						this.stickyWin.win.makeDraggable(Object.extend(this.options.dragOptions, {
							handle:cal.rows[0].cells[1],
							onDrag:function(){
								if(this.stickyWin.shim) this.stickyWin.shim.show.bind(this.stickyWin)
							}.bind(this)
						}));
						cal.rows[0].cells[1].setStyle('cursor', 'move');
					} catch(e) {}//drag isn't available
				}
			}
			return this.calendar;
		},
/*	Properties: getCloseImg
		Returns an img object to use for the close funciton.
		
		You can use <Class.implement> to redefine this so that it returns a dom element of your choosing.
		You will need to add your own call to <DatePicker.hide>.
		
		Arguments:
		url - the url to the image
	*/
		getCloseImg: function(url){
      url = url||this.options.baseHref + "/simple.error.popup/closebtn.gif";
			var closer = new Element("img").setProperty('src', url);
			closer.addEvents({
				'mouseover': function(){
					closer.src = closer.src.replace('.gif', '_over.gif');
				},
				'mouseout':function(){
					closer.src = closer.src.replace('_over.gif', '.gif');
				},
				'click': this.hide.bind(this)
			}).setStyles({
				width: '13px',
				height: '13px'
			}).addClass('closebtn');
			return closer;
		},
		
/*	Property: hide
		Hides the calendar popup.
	*/
		hide: function(){
			this.stickyWin.hide();
			this.fireEvent('onHide');
		},
/*	Property: show
		Shows the calendar popup. This will reposition the popup and display the date that the user has entered or today's date if they have not entered anything.
	*/
		show: function(){
	    this.today = this.zeroHourGMT(new Date());
			this.inputDate = new Date(this.updateInput());
	    this.refDate = isNaN(this.inputDate) ? this.today : this.zeroHourGMT(new Date(this.inputDate));
			this.getCalendar();
	    this.fillCalendar(this.refDate);
			this.stickyWin.show();
			this.fireEvent('onShow');
		},
		clickCalendar: function(e) {
			e = new Event(e);
			if (!e.target.firstChild || !e.target.firstChild.data) return;
			var val = e.target.firstChild.data;
			if (val.charCodeAt(0) > 9600 || val == "<" || val == ">") {
				var newRef = this.calendar.rows[2].cells[0].refDate - this.options.fullDay;
				if (val.charCodeAt(0) != 9668 && val != "<") newRef = this.calendar.rows[7].cells[6].refDate + this.options.fullDay;
				this.fillCalendar(new Date(newRef));
				return;
			}
			if (e.target.refDate) {
				var newDate = new Date(e.target.refDate);
				this.input.value = this.formatDate(newDate);
				/* trip onchange events in text field */
				this.input.fireEvent("change");
				this.input.fireEvent("blur");
				this.fireEvent('onPick');
				if(this.options.hideCalendarOnPick) this.hide();
			}
		},
		fillCalendar: function (forDate) {
			var startDate = new Date(forDate.getTime());
			startDate.setUTCDate(1);
			startDate.setTime(startDate.getTime() - (this.options.fullDay * startDate.getUTCDay()));
			this.calendar.rows[0].cells[1].firstChild.data = this.options.months[forDate.getUTCMonth()] + " " + forDate.getUTCFullYear();
			var atDate = startDate;
			this.calendar.getElements('td').each(function (el){
				el.removeClass('selectedDate').removeClass('otherMonthDate').removeClass('today');
			});
			for (var w=2; w<8; w++) for (var d=0; d<7; d++) {
				var td = this.calendar.rows[w].cells[d];
				td.firstChild.data = atDate.getUTCDate();
				td.refDate = atDate.getTime();
				if(atDate.getTime() == this.today.getTime()) td.addClass('today');
				if(atDate.getTime() == this.refDate.getTime()) td.addClass('selectedDate');
				if(atDate.getUTCMonth() != forDate.getUTCMonth()) td.addClass('otherMonthDate');
				atDate.setTime(atDate.getTime() + this.options.fullDay);
			}
		}
	});
/*	Note:
		DatePicker implements <Options> and <Events>.
	*/
	DatePicker.implement(new Options);
	DatePicker.implement(new Events);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/date.picker.js,v $
$Log: date.picker.js,v $
Revision 1.20  2007/11/29 19:00:57  newtona
- fixed an issue with StickyWin that could create inheritance issues because of a defect in $merge. this fixed an actual issue with DatePicker, specifically, having more than one on a page
- fixed an issue with the basehref in simple.error.popup

Revision 1.19  2007/10/25 00:17:25  newtona
tweaking date picker asset paths

Revision 1.18  2007/10/25 00:10:58  newtona
left in an extra comma...

Revision 1.17  2007/10/25 00:09:08  newtona
syntax tweak in date picker

Revision 1.16  2007/10/25 00:04:37  newtona
missed a comma

Revision 1.15  2007/10/25 00:02:39  newtona
fixing options references in DatePicker

Revision 1.14  2007/10/23 23:10:26  newtona
added mootools debugger to cvs
new file: setAssetHref.js; enables you to quickly set the location of image assets contained in the framework so you don't use CNET's versions, which can sometimes be slow.

Revision 1.13  2007/10/18 21:43:35  newtona
updating components that reference images and assets at www.cnet.com to allow for easy over-writing for that source url

Revision 1.12  2007/09/24 20:55:49  newtona
new file: StickyWin.Ajax - adds ajax support to all stickywin classes (creates new classes, just append .Ajax to any of the existing ones)
updated redball common full to include StickyWin.Ajax
date.picker, product.picker - updated syntax to use Element.empty
form.validator - now passes along the event object to the onFormValidate event so that the form submit event can be stopped if you like
popupdetails - added html response support; you can now return the html you wish to display rather than a json object; only applies to ajax. Also added a cache so that multiple requests are not made for the same url.
stickyWinHTML - ractored so that options are now, you know, *optional*
MooScroller - added support for width option for horizontal scrolling

Revision 1.11  2007/07/18 16:15:21  newtona
forgot to bind the style objects in the setText.attempt method...

Revision 1.10  2007/07/16 21:00:21  newtona
using Element.setText for all style injection methods (fixes IE6 problems)
moving Element.setText to element.legacy.js; this function is in Mootools 1.11, but if your environment is running 1.0 you'll need this.

Revision 1.9  2007/05/16 20:17:52  newtona
changing window.onDomReady to window.addEvent('domready'

Revision 1.8  2007/03/08 23:29:31  newtona
date picker: strict javascript warnings cleaned up
popup details strict javascript warnings cleaned up
product.picker: strict javascript warnings cleaned up, updating input now fires onchange event
confirmer: new file

Revision 1.7  2007/02/27 21:46:43  newtona
docs update; fixing references

Revision 1.6  2007/02/21 00:27:08  newtona
switched Class.create to Class.empty

Revision 1.5  2007/02/07 20:51:41  newtona
implemented Options class
implemented Events class
StickyWin now uses Element.position

Revision 1.4  2007/02/03 01:36:41  newtona
fixed a fireevent bug

Revision 1.3  2007/01/29 23:50:53  newtona
additional bug fixes and tweaks. stable now.

Revision 1.2  2007/01/27 01:51:36  newtona
numerous ie6 fixes.

Revision 1.1  2007/01/26 21:55:04  newtona
*** empty log message ***


*/
/*	Script: TagMaker.js
		Prompts the user to fill in the gaps to create an html tag output.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools 1.11 - <Core.js>, <Class.js>, <Class.Extras.js>, <Array.js>, <Function.js>, <Number.js>, <String.js>, <Element.js>
					<Window.Size.js>, <Element.Dimensions.js>, <Element.Event.js>, <Element.Selectors.js>, <Element.Form.js>,
					<Fx.Base.js>, <Fx.Css.js>, <Fx.Style.js>, <Fx.Styles.js>, <Tips.js>
		Optional - <Drag.Base.js>, <Drag.Move.js>
		CNET - <IframeShim.js>, <clipboard.js>, <form.validator.js>, <stickyWin.js>, <stickyWinFx.js>, <stickyWin.default.layout.js>,
					<html.table.js>, <dbug.js>, <simple.error.popup.js>, <element.dimensions.js>, <element.forms.js>, <element.shortcuts.js>,
					<element.position.js>
		CNET Optional - <product.picker.js>, <stickyWinFx.Drag.js>
		
		Class: TagMaker
		Prompts the user to fill in the gaps to create an html tag output.
		
		Arguments:
		options - a key/value set of options
		
		Options:
		name - (string) the name displayed in the caption area of the popup
		output - (string) the html tag with tokens for the areas the user is to fill in (see example below)
		picklets - (object) a key/value set where the keys are the tokens in the output and the values are arrays of picklets; see <Picklet> and <ProductPicker>;
		help - (object) a key/value set where the keys are the tokens in the output and the values are help text for tooltips; see example.
		example - (object) a key/value set where the keys are the tokens in the output and the values are examples of valid inputs.
		class - (object) (object) a key/value set where the keys are the tokens in the output and the values are css classes; use these
			to pass along validators for <FormValidator> if you want the fields validated.
		selectLists - (object) (object) a key/value set where the keys are the tokens in the output and the values are arrays of objects. These sub-objects have keys for "key" and "value" that correspond to the innerText of the option and the value of the option respectively. Additionally, one of them can have the key/value set of "selected:true" to have that option be selected. See example.
		width - (integer) the width for the prompt; defaults to 400
		masHeight - (integer) the maximum height for the prompt; defaults to 500
		showResult - (boolean) if true (the default) an input is shown with the resulting output in it
		clearOnPrompt - (boolean) if true (the default) the prompt is emptied every time it is displayed
		css - (string) css rules to be injected into the page to style the prompt; defaults to the default style included in this class.

		Events:
		onPrompt - (function) callback executed when the prompt is displayed to the user
		onChoose - (function) callback executed when the user clicks "paste" or "copy", which closes the prompt
	*/
var TagMaker = new Class({
	options: {
		name: "Tag Builder",
		output: '',
		picklets: {},
		help: {},
		example: {},
		'class': {},
		selectLists: {},
		width: 400,
		maxHeight: 500,
		showResult: true,
		clearOnPrompt: true,
		baseHref: "http://www.cnet.com/html/rb/assets/global/tips/", 
		css: "table.trinket {	width: 98%;	margin: 0px auto;	font-size: 10px; }\
					table.trinket td {	vertical-align: top;	padding: 4px;}\
					table.trinket td a.button {	position: relative;	top: -2px;}\
					table.trinket td.example {	font-size: 9px;	color: #666;	text-align: right;	border-bottom: 1px solid #ddd;\
						padding-bottom: 6px;}\
					table.trinket div.validation-advice {	background-color: #a36565;	font-weight: bold;	color: #fff;	padding: 4px;\
						margin-top: 3px;}\
					table.trinket input.text {width: 100%;}\
					.tagMakerTipElement { 	cursor: help; }\
					.tagMaker-tip {	color: #fff;	width: 172px;	z-index: 13000; }\
					.tagMaker-title {	font-weight: bold;	font-size: 11px;	margin: 0;	padding: 8px 8px 4px;\
							background: url(%baseHref%/bubble.png) top left;}\
					.tagMaker-text { font-size: 11px; 	padding: 4px 8px 8px; \
							background: url(%baseHref%/bubble.png) bottom right; }",
		onPrompt: Class.empty,
		onChoose: Class.empty
	},
	initialize: function(options){
		this.setOptions(options);
		this.buttons = [
			{
				text: 'Copy',
				onClick: this.copyToClipboard.bind(this),
				properties: {
					'class': 'closeSticky tip',
					title: 'Copy::Copy the html to your OS clipboard (like hitting Ctrl+C)'
				}
			},
			{
				text: 'Paste',
				onClick: function(){
					if(this.validator.validate()) this.insert();
				}.bind(this),
				properties: {
					'class': 'tip',
					title: 'Paste::Insert the html into the field you are editing'
				}
			},
			{
				text: 'Close',
				properties: {
					'class': 'closeSticky tip',
					title: 'Close::Close this popup'
				}
			}
		];
		this.writeCss();
	},
	writeCss: function(){
		window.addEvent('domready', function(){
			try {
				if(!$('defaultTagBuilderStyle')) {
					var css = this.options.css.replace("%baseHref%", this.options.baseHref, "g");
					var styler = new Element('style').setProperty('id','defaultTagBuilderStyle').injectInside($$('head')[0]);
					if (!styler.setText.attempt(css, styler)) styler.appendText(css);
				}
			}catch(e){dbug.log('error: %s',e);}
		}.bind(this));
	},

	
/*	Property: prompt
		Prompts the user to interact with the builder.
		
		Arguments:
		target - (DOM reference or id) the input/ui element that the trinket is associated with per prompt. This allows you to have on instance that creates, say, links, but show the same one for different inputs.
	*/
	prompt: function(target){
		this.target = $(target);
		var content = this.getContent();
		if (this.options.clearOnPrompt) this.clear();
		if(content) {
				var relativeTo = (document.compatMode == "BackCompat" && this.target)?this.target:document.body;
				if(!this.win) this.win = new StickyWinFx({
					content: content,
					draggable: true,
					relativeTo: relativeTo,
					onClose: function(){
						$$('.tagMaker-tip').hide();
					}
				});
				if(!this.win.visible) this.win.show();
		}
		var innerText = this.getInnerTextInput();
		this.range = target.getSelectedRange();
		if(innerText) innerText.value = target.getTextInRange(this.range.start, this.range.end)||"";
		
		this.fireEvent('onPrompt');
	},
	clear: function(){
		this.body.getElements('input').each(function(input){
			input.value = '';
		});
	},
	getKeys: function(text) {
		return text.split('%').filter(function(inputKey, index){
				return index%2;
		});
	},
	getInnerTextInput: function(){
		return this.body.getElement('input[name=Inner-Text]');
	},
	getContent: function(){
		var opt = this.options; //save some bytes
		if(!this.form) { //if the body hasn't been created, create it
			this.form = new Element('form'); //the form
			
				var table = new HtmlTable({properties: {'class':'trinket'}});
				this.getKeys(opt.output).each(function(inputKey) {
					if(this.options.selectLists[inputKey]){
						var input = new Element('select').setProperties({
							name: inputKey.replaceAll(' ', '-')
						}).addEvent('change', this.createOutput.bind(this));
						this.options.selectLists[inputKey].each(function(opt){
							var option = new Element('option').injectInside(input);
							if(opt.selected) option.selected = true;
							option.value = opt.value;
							option.text = opt.key;
						}, this);
						table.push([inputKey, input]);
					} else {
						var input = new Element('input').setProperties({
							type: 'text',
							name: inputKey.replaceAll(' ', '-'),
							title: inputKey+'::'+opt.help[inputKey],
							'class': 'text tip ' + ((opt['class'])?opt['class'][inputKey]||'':'')
						}).addEvent('keyup', this.createOutput.bind(this)).addEvent('focus', function(){this.select()});
						if(opt.picklets[inputKey]) {
							var a = new Element('a').addClass('button').setHTML('choose');
							var div = new Element('div').adopt(input.setStyle('width','160px')).adopt(a);
							var picklets = ($type(opt.picklets[inputKey]) == "array")?opt.picklets[inputKey]:[opt.picklets[inputKey]];
							new ProductPicker(input, picklets, {
								showOnFocus: false, 
								additionalShowLinks: [a],
								onPick: function(input, data, picker){
									try {
										var ltInput = this.getInnerTextInput();
										if(ltInput && !ltInput.value) {
											try {
												ltInput.value = picker.currentPicklet.options.listItemName(data);
											}catch (e){dbug.log('set value error: ', e);}
										}
										var val = input.value;
										if(inputKey == "Full Path" && val.indexOf('http://')==0)
												input.value = val.substring(val.indexOf('/', 7), val.length);
										this.createOutput();
									} catch(e){dbug.log(e)}
								}.bind(this)
							});
							table.push([inputKey, div]);
						} else table.push([inputKey, input]);
					}
					//[{content: <content>, properties: {colspan: 2, rowspan: 3, 'class': "cssClass", style: "border: 1px solid blue"}]
					if(this.options.example[inputKey]) 
						table.push([{content: 'eg. '+this.options.example[inputKey], properties: {colspan: 2, 'class': 'example'}}]);
				}, this);
				this.resultInput = new Element('input').setProperties({
						type: 'text',
						title: 'HTML::This is the resulting tag html.',
						'class': 'text result tip'
					}).addEvent('focus', function(){this.select()});
				table.push(['HTML', this.resultInput]).tr.setStyle('display', this.options.showResult?'':'none');

			this.form = table.table;
			this.body = new Element('div').adopt(this.form).setStyles({
				overflow:'auto',
				maxHeight: this.options.maxHeight
			});
			this.validator = new FormValidator(this.form);
			this.validator.insertAdvice = function(advice, field){
				var p = $(field.parentNode);
				if(p) p.adopt(advice);
			};
		}

		if(!this.content) {
			this.content = stickyWinHTML(this.options.name, this.body, {
				buttons: this.buttons,
				width: this.options.width.toInt()+'px'
			});
			new Tips(this.content.getElements('.tip'), {
				showDelay: 700,
				maxTitleChars: 50, 
				maxOpacity: .9,
				className: 'tagMaker'
			});
		}
		return this.content;

	},
	createOutput: function(){
		var inputs = this.form.getElementsBySelector('input, select');
		var html = this.options.output;
		inputs.each(function(input) {
			if(!input.hasClass('result')) {
				html = html.replaceAll('%'+input.getProperty('name').replaceAll('-', ' ').toLowerCase()+'%',
					input.getValue(), 'i');
			}
		});
		return this.resultInput.value = html;
	},
	copyToClipboard: function(){
		var inputs = this.form.getElements('input');
		var result = inputs[inputs.length-1];
		result.select();
		Clipboard.copy(result);
		$$('.tagMaker-tip').hide();
		this.win.hide();
		this.fireEvent('onChoose');
	},
	insert: function(){
		if(!this.target) {
			simpleErrorPopup('Cannot Paste','This tag builder was not launched with a target input specified; you\'ll have to copy the tag yourself. Sorry!');
			return;
		}
		var value = (this.target)?this.target.value:this.target;
		var output = this.body.getElement("input.result");
		
		var currentScrollPos; 
		if (this.target.scrollTop || this.target.scrollLeft) {
			currentScrollPos = {
				scrollTop: this.target.scrollTop,
				scrollLeft: this.target.scrollLeft
			};
		}
		this.target.value = value.substring(0, this.range.start) + output.value + value.substring((this.range.end-this.range.start) + this.range.start, value.length);
		if(currentScrollPos) {
			this.target.scrollTop = currentScrollPos.scrollTop;
			this.target.scrollLeft = currentScrollPos.scrollLeft;
		}

		this.target.selectRange(this.range.start, output.value.length + this.range.start);
		this.fireEvent('onChoose');
		$$('.tagMaker-tip').hide();
		this.win.hide();
		return;
	}
});
TagMaker.implement(new Options, new Events);


/*	Class: TagMaker.image
		Default image tag maker.	*/
TagMaker.image = TagMaker.extend({
	options: {
		name: "Image Builder",
		output: '<img src="%Full Url%" width="%Width%" height="%Height%" alt="%Alt Text%" style="%Alignment%"/>',
		help: {
			'Full Url':'Enter the external URL (http://...) to the image',
			'Width':'Enter the width in pixels.',
			'Height':'Enter the height in pixels.',
			'Alt Text':'Enter the alternate text for the image.',
			'Alignment':'Choose how to float the image.'
		},
		example: {
			'Full Url':'http://i.i.com.com/cnwk.1d/i/hdft/redball.gif'
		},
		'class': {
			'Full Url':'validate-url required',
			'Width':'validate-digits required',
			'Height':'validate-digits required',
			'Alt Text':'required'
		},
		selectLists: {
			Alignment: [
				{
					key: 'left',
					value: 'float: left'
				},
				{
					key: 'right',
					value: 'float: right'
				},
				{
					key: 'none',
					value: 'float: none',
					selected: true
				},
				{
					key: 'center',
					value: 'margin-left: auto; margin-right: auto;'
				}
			]		
		},
		showResult: false
	}
});

/*	Class: TagMaker.anchor
		Default TagMaker for links.	*/

var TMPicklets = [];
if(typeof CNETProductPicker_ReviewPath != "undefined") TMPicklets.push(CNETProductPicker_ReviewPath);
if(typeof CNETProductPicker_PricePath != "undefined") TMPicklets.push(CNETProductPicker_PricePath);
if(typeof NewsStoryPicker_Path != "undefined") TMPicklets.push(NewsStoryPicker_Path);
TagMaker.anchor = TagMaker.extend({
	options: {
		name: "Anchor Builder",
		output: '<a href="%Full Url%">%Inner Text%</a>',
		picklets: {
			'Full Url': (TMPicklets.length)?TMPicklets:false
		},
		help: {
			'Full Url':'Enter the external URL (http://...)',
			'Inner Text':'Enter the text for the link body'
		},
		example: {
			'Full Url':'http://www.microsoft.com',
			'Inner Text':'Microsoft'
		},
		'class': {
			'Full Url':'validate-url'
		}
	}
});

/*	Class: TagMaker.cnetVideo
		CNET Internal; Default tag maker for the &lt;cnet:video/&gt; tag	*/

TagMaker.cnetVideo = TagMaker.extend({
	options: {
		name: "CNET Video Embed Tag",
		output: '<cnet:video ssaVideoId="%Video Id%" float="%Alignment%"/>',
		help: {
			'Video Id':'The id of the video to embed'
		},
		'class':{
			'Video Id':'validate-digits required'
		},
		selectLists: {
			Alignment: [
				{
					key: 'left',
					value: 'left'
				},
				{
					key: 'right',
					value: 'right'
				},
				{
					key: 'none',
					value: '',
					selected: true
				}
			]		
		}
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/TagMaker.js,v $
$Log: TagMaker.js,v $
Revision 1.9  2007/10/18 21:43:35  newtona
updating components that reference images and assets at www.cnet.com to allow for easy over-writing for that source url

Revision 1.8  2007/09/28 00:21:19  newtona
bad reference to this.input (should have been this.target)

Revision 1.7  2007/09/27 23:24:09  newtona
adding scrollback method to tagmaker

Revision 1.6  2007/09/27 21:50:51  newtona
tagmaker: hide tooltips when the popup is hidden
jsonp: return the data AND the instance of jsonp oncomplete.

Revision 1.5  2007/09/18 18:41:04  newtona
tweaking the layout a bit in tagmaker

Revision 1.4  2007/09/18 18:16:24  newtona
ok. now I'm just adding semi-colons where they don't belong...

Revision 1.3  2007/09/18 00:44:40  newtona
removing unchecked picklet references in TagMaker so that the script isn't dependent on them.

Revision 1.2  2007/09/18 00:33:22  newtona
damned semicolons

Revision 1.1  2007/09/15 00:07:08  newtona
collission in last check in; merged and re-doing.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
fixed a bug with validate-digits in form validator
new class: TagMaker
implemented TagMaker into simple.editor
updated caption (which is the drag handle for stickyWinHTML) to be the entire width of the caption area
html.table: docs update
tabswapper: docs update


*/
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
/*	Script: multiple.open.accordion.js
		Creates a Mootools <Fx.Accordion> that allows the user to open more than one element.
		
		Dependancies:
			 mootools - 	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>, <Fx.Base.js>, <Fx.Elements.js>, <Fx.Styles.js>, <Fx.Style.js>
			
		Author:
			Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>

		
		Class: MultipleOpenAccordion
		Extends the <Fx.Elements> class from Mootools for an accordion element that allows
		the user to open more than one element.
		
		Arguments:
		togglers - elements that activate each section
		elements - the elements to resize
		options - the options object of key/value settings
		
		Options:
		openAll - (boolean) open all elements on startup; defaults to true.
		allowMultipleOpen - (boolean) allows users to open more than one element at a time; defaults to true.
		firstElementsOpen - (array) an array of elements to open on startup;
				only used if openAll = false and allowMultipleOpen = true;
				defaults to [0]; can be empty ([]) to signifiy that all should be closed;
		start - (string) 'first-open' slides open each element in firstElementsOpen;
										 'open-first' opens each element in firstElementsOpen immediately using no effects (default)
		fixedHeight - integer, if you want your accordion to have a fixed height. defaults to false.
		fixedWidth - integer, if you want your accordion to have a fixed width. defaults to false.
		alwaysHide - boolean, if you want the ability to close your only-open item. defaults to true.
		wait - boolean. means that open and close transitions can cancel current ones (so if you click
		 on items before the previous finishes transitioning, the clicked transition will fire canceling the previous). 
		 true means that if one element is sliding open or closed, clicking on another will have no effect. 
		 for Accordion defaults to false.
		onActive - function to execute when an element starts to show; passed arguments: (toggler, section)
		onBackground - function to execute when an element starts to hide; passed arguments: (toggler, section)
		height - boolean, will add a height transition to the accordion if true. defaults to true.
		opacity - boolean, will add an opacity transition to the accordion if true. defaults to true.
		width - boolean, will add a width transition to the accordion if true. defaults to false, 
						css mastery is required to make this work!
	*/
var MultipleOpenAccordion = Fx.Elements.extend({
	options: {
		openAll: true,
		allowMultipleOpen: true,
		firstElementsOpen: [0],
		start: 'open-first',
		fixedHeight: false,
		fixedWidth: false,
		alwaysHide: true,
		wait: false,
		onActive: Class.empty,
		onBackground: Class.empty,
		height: true,
		opacity: true,
		width: false
	},
	initialize: function(togglers, elements, options){
		this.parent(elements, options);
		this.setOptions(options);
		this.previousClick = null;
		this.elementsVisible = [];
		togglers.each(function(tog, i){
			$(tog).addEvent('click', function(){this.toggleSection(i)}.bind(this));
		}, this);
		this.togglers = togglers;
		this.h = {}; 
		this.w = {};
		this.o = {};
		this.now = [];
		this.elements.each(function(el, i){
			el = $(el);
			this.now[i] = {};
			el.setStyle('overflow','hidden');
			if(!(this.options.openAll && this.options.allowMultipleOpen)) el.setStyle('height', 0);
		}, this);
		if(!this.options.openAll || !this.options.allowMultipleOpen) {
			switch(this.options.start){
				case 'first-open': this.showSection(this.options.firstElementsOpen[0]); break;
				case 'open-first': this.toggleSection(this.options.firstElementsOpen[0]); break;
			}
		}
		if (this.options.openAll && this.options.allowMultipleOpen) this.showAll();
		else if (this.options.allowMultipleOpen) this.openSections(this.options.firstElementsOpen);
	},
	hideThis: function(i){ //sets up the effects for hiding an element
		this.elementsVisible[i] = false;
		if (this.options.height) this.h = {'height': [this.elements[i].offsetHeight, 0]};
		if (this.options.width) this.w = {'width': [this.elements[i].offsetWidth, 0]};
		if (this.options.opacity) this.o = {'opacity': [this.now[i]['opacity'] || 1, 0]};
		this.fireEvent("onBackground", [this.togglers[i], this.elements[i]]);
	},

	showThis: function(i){ //sets up the effects for showing an element
		this.elementsVisible[i] = true;
		if (this.options.height) this.h = {'height': [this.elements[i].offsetHeight, this.options.fixedHeight || this.elements[i].scrollHeight]};
		if (this.options.width) this.w = {'width': [this.elements[i].offsetWidth, this.options.fixedWidth || this.elements[i].scrollWidth]};
		if (this.options.opacity) this.o = {'opacity': [this.now[i]['opacity'] || 0, 1]};
		this.fireEvent("onActive", [this.togglers[i], this.elements[i]]);
	},
/*	Property: toggleSection
		Opens or closes a section depending on its state and the options of the Accordion.
		
		Argumetns:
		iToToggle - (integer) the index of the section to open or close
	*/
	toggleSection: function(iToToggle){
		//let's open an object, or close it, depending on it's state
		//now, if the index to toggle isn't the previous click
		//or we're going to allow items to be closed (so that all of them are closed
		//or we're allowing more than one item to be open at a time, continue
		//otherwise, we're looking at an item that was just clicked, and it should already be open
		if(iToToggle != this.previousClick || this.options.alwaysHide || this.options.allowMultipleOpen) {
			//save the previous click
			this.previousClick = iToToggle;
			var objObjs = {};
			var err = false;
			//go through each element
			this.elements.each(function(el, i){
				var update = false;
				//set up it's now state
				this.now[i] = this.now[i] || {};
				//if the element is the one clicked
				if(i==iToToggle){
					//if the element is visible, hide it if we allow alwaysHide or multiple
					if (this.elementsVisible[i] && (this.options.allowMultipleOpen || this.options.alwaysHide)){
						//if ! wait and timer
						if(!(this.options.wait && this.timer)) {
							//hide it
							update = true;
							this.hideThis(i);
						} else {
							this.previousClick = null;
							err = true;
						}
					} else if(!this.elementsVisible[i]){
					//else if hidden, show it
						//if ! wait and timer
						if(!(this.options.wait && this.timer)) {
							//show it
							update = true;
							this.showThis(i);
						} else {
							this.previousClick = null;
							err = true;
						}
					}
				} else if(this.elementsVisible[i] && !this.options.allowMultipleOpen) {
				//else (not clicked) if it's visible, hide it, unless we allow multiple open
					//if ! wait and timer
					if(!(this.options.wait && this.timer)) {
						//hide it
						update = true;
						this.hideThis(i);
					} else {
						this.previousClick = null;
						err = true;
					}
				} //else it's not clicked, it's not open, so leave it alone because we allow multiples
				//set up the effect instructions
				if(update) objObjs[i] = $merge(this.h, $merge(this.o, this.w));
			}, this);
			//if there's an error, just stop
			if (err) return false;
			//execute the custom function, which resizes everything.
			return this.custom(objObjs);
		}
		return false;
	},
/*	Property: showSection
		Opens a section of the accordion if it's not open already.
		
		Arguments:
		i - (integer) the index of the section to show
		useFx - (boolean) open it immediately (false) or slide it open using the effects (true);  defaults to false;
	*/
	showSection: function(i, useFx){
		if($pick(useFx, false)) {
			if (!this.elementsVisible[i]) this.toggleSection(i);
		} else {
			this.setSectionStyle(i,$(this.elements[i]).scrollWidth, $(this.elements[i]).scrollHeight, 1);
			this.elementsVisible[i] = true;
			this.fireEvent("onActive", [this.togglers[i], this.elements[i]]);
		}
	},
/*	Property: hideSection
		Closes a section of the accordion if it's not closed already.
		
		Arguments:
		i - (integer) the index of the section to hide
		useFx - (boolean) close it immediately (false) or slide it closed using the effects (true);  defaults to false;
	*/
	hideSection: function(i, useFx){
		if($pick(useFx, false)) {	
			if (this.elementsVisible[i]) this.toggleSection(i);
		} else {
			this.setSectionStyle(i,0,0,0);
			this.elementsVisible[i] = false;
			this.fireEvent("onBackground", [this.togglers[i], this.elements[i]]);
		}
	},
	//internal function; sets a section (i) to the width (w), height (h), and opacity (o) passed in
	setSectionStyle: function(i,w,h,o){ 
			if (this.options.opacity) $(this.elements[i]).setOpacity(o);
			if (this.options.height) $(this.elements[i]).setStyle('height',h+'px');
			if (this.options.width) $(this.elements[i]).setStyle('width',w+'px');
	},
/*	Property: showAll
		Opens all the elements in the accordion immediately; used on startup	*/
	showAll: function(){
		if(this.options.allowMultipleOpen){
			this.elements.each(function(el,idx){
					this.showSection(idx, false);
			}, this);
		}
	},
/*	Property: hideAll
		Closes all the elements in the accordion immediately; used on startup	*/
	hideAll: function(){
		if(this.options.allowMultipleOpen){
			this.elements.each(function(el,idx){
				this.hideSection(idx, false);
			}, this);
		}
	},
/*	Property: openSection
		Opens specific sections of the accordion immediately; used on startup.
		
		Arguments:
		sections - array of indexes to open.
	*/
	openSections: function(sections) {
		if(this.options.allowMultipleOpen){
			this.elements.each(function(el,idx){
				if(sections.test(idx)) this.showSection(idx, false);
				else this.hideSection(idx, false);
			}, this);
		}
	}
});
MultipleOpenAccordion.implement(new Options);
MultipleOpenAccordion.implement(new Events);
/* do not edit below this line */   

/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/layout.widgets/multiple.open.accordion.js,v $
$Log: multiple.open.accordion.js,v $
Revision 1.8  2007/06/21 20:20:29  newtona
multiopenaccordion showall and hideall weren't working; closing bug 296095

Revision 1.7  2007/05/29 20:34:34  newtona
refactored a lot; fixed issues with onBackground and onActive events.

Revision 1.6  2007/04/04 17:28:53  newtona
subtle syntax error fix.

Revision 1.5  2007/03/08 23:29:59  newtona
strict javascript warnings cleaned up

Revision 1.4  2007/02/27 21:46:43  newtona
docs update; fixing references

Revision 1.3  2007/02/07 20:51:55  newtona
implemented Options class
implemented Events class

Revision 1.2  2007/01/26 05:53:47  newtona
syntax update for mootools 1.0

Revision 1.1  2007/01/22 21:59:03  newtona
moved from fx.multiple.open.accordion.js

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.5  2006/12/06 20:14:59  newtona
carousel - improved performance, changed some syntax, actually deployed into usage and tested
cnet.nav.accordion - improved css selectors for time
multiple accordion - fixed a typo
dbug.js - added load timers
element.cnet.js - changed syntax to utilize mootools more effectively
function.cnet.js - equated $set to $pick in preparation for mootools v1

Revision 1.4  2006/11/06 19:19:31  newtona
fixed a bug and removed some dbug.log statements

Revision 1.3  2006/11/04 01:35:27  newtona
removing a dbug line

Revision 1.2  2006/11/04 00:53:45  newtona
no change

Revision 1.1  2006/11/02 21:28:08  newtona
checking in for the first time.


*/
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
/*
Script: mouseovers.js
Collection of mouseover behaviours (images, class toggles, etc.).
These functions handle standard mouseover behaviour.
		
Dependancies:
	 mootools - <Moo.js>, <Utility.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>
	
Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>


Function: imgMouseOverEvents
		handles hover states for images. Producers simply author all their 
		images to have an on version and an off version with same naming 
		conventions, then call this function with those conventions and 
		a css selector. All images that match that selector will get the 
		mouseover behavior applied to them automatically.

Example:
		(start code)
		<img src="myimg_off.gif" class="autoMouseOver">
		assuming that my hover image is the same path with _off
		   substituted with _on; so: myimg_on.gif is the hover version
		<script>
			imgMouseOverEvents('_off', '_on', 'img.autoMouseOver');
		</script>
		(end)
		You can call this function as soon as the DOM is ready.
		
		Note:
		The default instance of this function is included in this library.
		If producers name their on/off state files with "_on" and "_off"
		in the file names and give their images the class "autoMouseOver"
		then they don't have to write any javascript. This also works for
		inputs.
		
		Automatically executed versions:
		img.autoMouseOverOff - swaps '_off' for '_over'
		img.autoMouseOver - swaps '_off' for '_on'
		input.autoMouseOver - swaps '_off' for '_on'
		
		Arguments:
		outString - the string to substitute for the on string when the user mouses out
		overString - the string to substitute for the out string when the users mouses over
		selector - css selector to apply this behaviour
		
		See Also: <tabMouseOvers>
	*/
function imgMouseOverEvents(outString, overString, selector) {
	$$(selector).each(function(image) {
		image = $(image);
		if ($type(image.src)) {
			if (image.src.indexOf(outString) > 0) {
				image.addEvent('mouseover',function(){
					image.src = image.src.replace(outString, overString);
				}).addEvent('mouseout', function(){ 
					image.src = image.src.replace(overString, outString);
				});
			}
		}
	});
};
window.addEvent('domready', function(){imgMouseOverEvents('_off', '_over', 'img.autoMouseOverOff, input.autoMouseOverOff');});
window.addEvent('domready', function(){imgMouseOverEvents('_off', '_on', 'img.autoMouseOver, input.autoMouseOver');});

/*	
Function: tabMouseOvers
		tabMouseOvers are almost identical to <imgMouseOverEvents>.
		this function will swap out one css class for another when the
		user mouses over a dom element (doesn't have to be a tab layout)
		You also have the option of having the class of the DOM element
		change when the user mouses over a child of the DOM element that's
		supposed to toggle (for instance, if your tab has a link in it,
		you can have the tab change when the user mouses over the anchor
		instead of the whole tab).
		
		pass in the css class for the 'on' and 'off states, as well as 
		the css selector for the DOM element, and, optionally, the selector
		for the sub elements for the mouseover action.
		
		you can also optionally set applyToBoth to set the mouseover to both
		the selector and the subselector if you like
		
		Arguments:
		cssOn - the "on" state for the tab; this css class will be added 
						when the user mouses over the element.
		cssOff - the "off" state for the tab
		selector - the selector for all the tabs
		subselector - the selector for any sub elements that you wish to attach
						the mouseover behavior to
		applyToBoth - a boolean; if you want to apply the mouseover behavior
						to both the selector and the subselector; false = just the
						subselector
		
		example:
		><ul id="myTabs">
		>	<li><a href="1">one</a></li>
		>	<li><a href="2">two</a></li>
		>	<li><a href="3">three</a></li>
		></ul>
		><script>
		>	tabMouseOvers('on', 'off', '#myTabs li", "a", false);
		></script>
		
		now, when the user mouses over the anchor tags, the parent li object
		will get the class "on" added to it.
		
		note that those last two, the subselector and the applyToBoth are optional
*/
function tabMouseOvers(cssOn, cssOff, selector, subselector, applyToBoth){
	$$(selector).each(function(el){
		el.applyToBoth = $pick(applyToBoth, false);
		if(applyToBoth && subselector) {
			el.getElementsBySelector(subselector).each(function(el){
				el.addClass(cssOff).removeClass(cssOn);
			});
		}
		el.addClass(cssOff).removeClass(cssOn);
		el.addEvent('mouseover', function(){
			this.addClass(cssOn).removeClass(cssOff);
			if(applyToBoth && subselector) {
				this.getElementsBySelector(subselector).each(function(subel){
					subel.addClass(cssOn).removeClass(cssOff);
				});
			}
		});
		el.addEvent('mouseout', function(){
			this.addClass(cssOff).removeClass(cssOn);
			if(applyToBoth && subselector) {
				$A(this.getElementsBySelector(subselector)).each(function(subel){
					subel.addClass(cssOff).removeClass(cssOn);
				});
			}
		});
	});
};
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/layout.widgets/mouseovers.js,v $
$Log: mouseovers.js,v $
Revision 1.8  2007/05/16 20:17:52  newtona
changing window.onDomReady to window.addEvent('domready'

Revision 1.7  2007/02/27 19:41:10  newtona
accidentally tied the wrong css class to the default states for mouseovers. fixed.

Revision 1.6  2007/02/03 01:38:53  newtona
cleaned up the default entries (autoMouseOverOff)
added some docs about these default entries

Revision 1.5  2007/01/26 05:52:32  newtona
syntax update for mootools 1.0
fixed a bug

Revision 1.4  2007/01/23 00:11:59  newtona
fixed a syntax error

Revision 1.3  2007/01/22 21:59:36  newtona
updated for mootools 1.0

Revision 1.2  2007/01/11 20:55:47  newtona
fixed syntax error with Window.onDomReady

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.5  2007/01/09 01:26:49  newtona
changed $S to $$

Revision 1.4  2007/01/05 19:31:30  newtona
swapped out Event.onDomReady for Window.onDOMReady

Revision 1.3  2006/11/03 18:45:36  newtona
found conflict on tips page
http://help.dldev2.cnet.com:8006/9611-12576_39-0.html?tag=button1&nodeId=6501&jsdebug=true

in imgMouseOverEvents

added this line:

image = $(image);

To apply Mootools Element properties to each image as I apply them

Revision 1.2  2006/11/02 21:26:42  newtona
checking in commerce release version of global framework.

notable changes here:
cnet.functions.js is the only file really modified, the rest are just getting cvs footers (again).

cnet.functions adds numerous new classes:

$type.isNumber
$type.isSet
$set

*//*
Script: tabswapper.js
Handles the scripting for a common UI layout; the tabbed box.

Dependancies:
	mootools - 	<Moo.js>, <Utility.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>, <Fx.Base.js>, <Dom.js>, <Cookie.js>
	cnet - <element.shortcuts.js>
	
Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>

Class: TabSwapper
		Handles the scripting for a common UI layout; the tabbed box.
		If you have a set of dom elements that are going to toggle visibility based
		on the related tabs above them (they don't have to be above, but usually are)
		you can instantiate a TabSwapper and it's handled for you.
		
		Example:
		
		><ul id="myTabs">
		>	<li><a href="1">one</a></li>
		>	<li><a href="2">two</a></li>
		>	<li><a href="3">three</a></li>
		></ul>
		><div id="myContent">
		>	<div>content 1</div>
		>	<div>content 2</div>
		>	<div>content 3</div>
		></div>
		><script>
		>	var myTabSwapper = new TabSwapper({
		>		selectedClass: "on",
		>		deselectedClass: "off",
		>		mouseoverClass: "over",
		>		mouseoutClass: "out",
		>		tabs: $$("#myTabs li"),
		>		clickers: $$("#myTabs li a"),
		>		sections: $$("#myContent div"),
		>		smooth: true,
		>		cookieName: "rememberMe"
		>	});
		></script>
		
		Notes:
		 - you don't have to specify the classes for mouseover/out
		 - you don't have to specify a click selector; it'll just
		   use the tab DOM elements if you don't give it the click
			 selector
		 - the click selector is NOT a subselector of the tabs; be sure
		   to specify a full css selector for these
		 - smooth: is off by default; adds some nice transitional effects
		 - cookieName: will store the users's last selected tab in a cookie
		   and restore this tab when they next visit
			 
Arguments:
	options - optional, an object containing options.

Options:
			selectedClass - the class for the tab when it is selected
			deselectedClass - the class for the tab when it isn't selected
			mouseoverClass - the class for the tab when the user mouses over
			rearrangeDOM - (boolean) arranges the tabs and sections in the dom to be in the same order as they are in the class; defaults to true.
			tabs - (array) an array of DOM elements for the tabs (these get the above classes added to them when the user interacts with the interface); can also be a <$$> selector (string).
			clickers - (optional, array) an array of DOM elements for the clickers; if your tab contains a child DOM element that the user clicks - not the whole tab but an element within it - to switch the content, pass in an array of them here. If you don't pass these in, the array of tabs is used instead (the default). Can also be a <$$> selector (string).
			sections - (array) an array of DOM elements for the sections (these change when the clickers are clicked); can also be a <$$> selector (string).
			initPanel - the panel to show on init; 0 is default (optional)
			smooth - use effects to smooth transitions; false is default (optional)
			cookieName - if defined, the browser will remember their previous selection
					 	using a cookie (optional)
			cookieDays - how many days to remember this? default is 999, but it's
						ignored if cookieName isn't set (optional)
			effectOptions - the options to pass on to the transition effect if the "smooth" option is set to true; defaults to {duration: 500}
			onBackground - callback executed when a section is hidden; passed three arguments: the index of the section, the section, and the tab
			onActive - callback executed when a section is shown; passed three arguments: the index of the section, the section, and the tab
			onActiveAfterFx - callback executed when a section is shown but after the effects have completed (so it's visible to the user); passed three arguments: the index of the section, the section, and the tab
	*/

var TabSwapper = new Class({
	options: {
		selectedClass: 'tabSelected',
		mouseoverClass: 'tabOver',
		deselectedClass: '',
		rearrangeDOM: true,
		tabs: [],
		clickers: [],
		sections: [],
		initPanel: 0, 
		smooth: false, 
		effectOptions: {
			duration: 500
		},
		cookieName: null, 
		cookieDays: 999,
		onActive: Class.empty,
		onActiveAfterFx: Class.empty,
		onBackground: Class.empty
	},
	initialize: function(options){
		this.tabs = [];
		this.sections = [];
		this.clickers = [];
		options = this.compatability(options);
		this.setOptions(options);
		this.sectionOpacities = [];
		this.setup();

		if(this.options.cookieName && this.recall()) this.swap(this.recall().toInt());
		else this.swap(this.options.initPanel);
	},
	compatability: function(options){
		if(options.tabSelector){
			options.tabs = $$(options.tabSelector);
			options.sections = $$(options.sectionSelector);
			options.clickers = $$(options.clickSelector);
		}
		return options;
	},
	setup: function(){
		var opt = this.options;
		sections = $$(opt.sections);
		tabs = $$(opt.tabs);
		clickers = $$(opt.clickers);
		tabs.each(function(tab, index){
			this.addTab(tab, sections[index], clickers[index], index);
		}, this);
	},
/*	Property; addTab
		Adds a tab to the interface.
		
		Arguments:
		tab - (DOM element) the tab; (see Options)
		clicker - (DOM element) the clicker
		section - (DOM element) the section
		index - (integer, optional) where to insert this tab; defaults to the last place (i.e. push)
	*/
	addTab: function(tab, section, clicker, index){
		tab = $(tab); clicker = $(clicker); section = $(section);
		//if the tab is already in the interface, just move it
		if(this.tabs.indexOf(tab) >= 0 && tab.getProperty('tabbered') 
			 && this.tabs.indexOf(tab) != index && this.options.rearrangeDOM) {
			this.moveTab(this.tabs.indexOf(tab), index);
			return;
		}
		//if the index isn't specified, put the tab at the end
		if(!$defined(index)) index = this.tabs.length;
		//if this isn't the first item, and there's a tab
		//already in the interface at the index 1 less than this
		//insert this after that one
		if(index > 0 && this.tabs[index-1] && this.options.rearrangeDOM) {
			tab.injectAfter(this.tabs[index-1]);
			section.injectAfter(this.sections[index-1]);
		}
		this.tabs.splice(index, 0, tab);
		this.sections.splice(index, 0, section);
		clicker = clicker || tab;
		this.clickers.splice(index, 0, clicker);

		tab.addEvent('mouseout',function(){
			tab.removeClass(this.options.mouseoverClass);
		}.bind(this)).addEvent('mouseover', function(){
			tab.addClass(this.options.mouseoverClass);
		}.bind(this));

		clicker.addEvent('click', function(){
			this.swap(this.clickers.indexOf(clicker));
		}.bind(this));

		tab.setProperty('tabbered', true);
		this.hideSection(index);
		return;
	},
/*	Property: removeTab
	Removes a tab from the TabSwapper; does NOT remove the DOM elements for the tab or section from the DOM.

	Arguments:
	index - (integer) the index of the tab to remove.
 */
	removeTab: function(index){
		var now = this.tabs[this.now];
		if(this.now == index){
			if(index > 0) this.swap(index - 1);
			else if (index < this.tabs.length) this.swap(index + 1);
		}
		this.sections.splice(index, 1);
		this.tabs.splice(index, 1);
		this.clickers.splice(index, 1);
		this.sectionOpacities.splice(index, 1);
		this.now = this.tabs.indexOf(now);
	},
/*	Property: moveTab
		Moves a tab's index from one location to another.
		
		Arguments:
		from - (integer) the index of the tab to move
		to - (integer) its new location
	*/
	moveTab: function(from, to){
		var tab = this.tabs[from];
		var clicker = this.clickers[from];
		var section = this.sections[from];
		
		var toTab = this.tabs[to];
		var toClicker = this.clickers[to];
		var toSection = this.sections[to];
		
		this.tabs.remove(tab).splice(to, 0, tab);
		this.clickers.remove(clicker).splice(to, 0, clicker);
		this.sections.remove(section).splice(to, 0, section);
		
		tab.injectBefore(toTab);
		clicker.injectBefore(toClicker);
		section.injectBefore(toSection);
	},
/*	Property: swap
		Swaps the view from one tab to another.
		
		Arguments:
		swapIdx - (integer) the index of the tab to show.
	*/
	swap: function(swapIdx){
		this.sections.each(function(sect, idx){
			if(swapIdx == idx) this.showSection(idx);
			else this.hideSection(idx);
		}, this);
		this.save(swapIdx);
	},
	save: function(index){
		if(this.options.cookieName) 
			Cookie.set(this.options.cookieName, index, {duration:this.options.cookieDays});
	},
	recall: function(){
		return (this.options.cookieName)?$pick(Cookie.get(this.options.cookieName), false): false;
	},
	hideSection: function(idx) {
		this.sections[idx].setStyle('display','none');
		this.tabs[idx].removeClass(this.options.selectedClass).addClass(this.options.deselectedClass);
		this.fireEvent('onBackground', [idx, this.sections[idx], this.tabs[idx]]);
	},
	showSection: function(idx) {
		var sect = this.sections[idx];
		if(this.now != idx) {
			if (!this.sectionOpacities[idx]) this.sectionOpacities[idx] = this.sections[idx].effect('opacity', this.options.effectOptions);
			sect.setStyles({
				display:'block',
				opacity: 0
			});
			if(this.options.smooth && (!window.ie6 || (window.ie6 && sect.fxOpacityOk())))
				this.sectionOpacities[idx].start(0,1).chain(function(){
					this.fireEvent('onActiveAfterFx', [idx, this.sections[idx], this.tabs[idx]]);
				}.bind(this));
			else if(sect.getStyle('opacity') < 1) {
				this.sectionOpacities[idx].set(1);
				this.fireEvent('onActiveAfterFx', [idx, this.sections[idx], this.tabs[idx]]);
			}
			this.now = idx;
			this.fireEvent('onActive', [idx, this.sections[idx], this.tabs[idx]]);
		}
		this.tabs[idx].addClass(this.options.selectedClass).removeClass(this.options.deselectedClass);
	}
});
TabSwapper.implement(new Options);
TabSwapper.implement(new Events);
//legacy namespace
var tabSwapper = TabSwapper;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/layout.widgets/tabswapper.js,v $
$Log: tabswapper.js,v $
Revision 1.22  2007/10/02 00:58:14  newtona
form.validator: fixed a bug where validation errors weren't removed under certain situations
tabswapper: .recall() no longer fails if no cookie name is set
jsonp: adding a 'return this'

Revision 1.21  2007/09/15 00:07:08  newtona
collission in last check in; merged and re-doing.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
fixed a bug with validate-digits in form validator
new class: TagMaker
implemented TagMaker into simple.editor
updated caption (which is the drag handle for stickyWinHTML) to be the entire width of the caption area
html.table: docs update
tabswapper: docs update

Revision 1.20  2007/08/06 20:18:18  newtona
forgot to actually implement the new rearrangeDOM option in the tabswapper... duh.

Revision 1.19  2007/08/06 20:04:24  newtona
added option to tabswapper to rearrange dom to match the order of tabs and sections in the class

Revision 1.18  2007/07/30 00:54:46  newtona
fixed a prototyipcal link issue in tabswapper

Revision 1.17  2007/07/05 16:40:10  newtona
dramatic refactor of tabswapper; now tabs can be added, removed, moved. Additionally, you can now pass in for tabs, sections, and clickers a dom collection or a selector.

Revision 1.16  2007/06/28 00:33:28  newtona
dangit. typo (extra close paren)

Revision 1.15  2007/06/28 00:31:03  newtona
tweaking the event timing in tabswapper

Revision 1.14  2007/06/28 00:11:21  newtona
typo in tabswapper; index instead of idx

Revision 1.13  2007/06/27 22:56:47  newtona
doc update in tabswapper

Revision 1.12  2007/06/27 22:45:21  newtona
docs update to overfiew.js
tabswapper gets some events action
fixed a typo in the docs for smoothmove

Revision 1.11  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.10  2007/04/12 23:47:34  newtona
fixed a bug where if you defined tabSelector but not clickSelector, things went whacky; now it acts as it should - if !clickSelector then clickSelector = tabSelector

Revision 1.9  2007/03/28 18:08:35  newtona
tabswapper now uses Element.fxOpacityOk to deal with the IE bug where text gets blurry when you fade an element in and out without a bgcolor set

Revision 1.8  2007/03/23 17:59:39  newtona
tabswapper no longer cmplains about this.recall() on load

Revision 1.7  2007/03/16 17:18:41  newtona
transitions no longer used for ie6

Revision 1.6  2007/02/27 19:40:42  newtona
enforcing element.show to use display block

Revision 1.5  2007/02/07 20:51:55  newtona
implemented Options class
implemented Events class

Revision 1.4  2007/01/26 05:53:33  newtona
syntax update for mootools 1.0
docs update
renamed tabSwapper - > TabSwapper

Revision 1.3  2007/01/22 22:49:48  newtona
updated cookie.set syntax

Revision 1.2  2007/01/22 21:59:19  newtona
updated for mootools 1.0

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.4  2007/01/09 01:26:49  newtona
changed $S to $$

Revision 1.3  2006/11/21 23:55:56  newtona
optimization update

Revision 1.2  2006/11/02 21:26:42  newtona
checking in commerce release version of global framework.

notable changes here:
cnet.functions.js is the only file really modified, the rest are just getting cvs footers (again).

cnet.functions adds numerous new classes:

$type.isNumber
$type.isSet
$set

*//*	Script: simple.slideshow.js
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
/*	Script: jsonp.js
		Creates a Json request using a script tag include and handles the callbacks for you.
		
		Dependencies:
		Mootools - <Moo.js>, <Array.js>, <String.js>, <Function.js>, <Utility.js>, <Element.js>, <Common.js>, <Assets.js>
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Class: JsonP
		Creates a Json request using a script tag include and handles the callbacks for you.
		
		Arguments:
		url - the url to get the json data
		options - an object with key/value options
		
		Options:
		onComplete - (optional) function to execute when the data returns; it will be passed the data and the 
			instance of jsonp that requested it.
		callBackKey - (string) the key in the url that the server uses to wrap the Json results. 
				So, for example, if you used "callBackKey: 'callback'" then the server is expecting
				something like http://..../?q=search+term&callback=myFunction
				defaults to "callback". This must be defined correctly.
		queryString - (string, optional) additional query string values to append to the url
		data - (object, optional) additional key/value data to append to the url
		
		Example:
(start code)
new JsonP('http://api.cnet.com/restApi/v1.0/techProductSearch', {
	data: {
		partTag: 'mtvo',
		iod: 'hlPrice',
		iewType: 'json',
		results: '100',
		query: 'ipod'
	},
	onComplete: myFunction.bind(someObject)
}).request();
(end)

		The above example would generate this url:
(start code) http://api.cnet.com/restApi/v1.0/techProductSearch?partTag=mtvo&iod=hlPrice&viewType=json&results=100&query=ipod&callback=JsonP.requestors[0].handleResults&
(end)

		It would embed this script tag (in the head of the document) and, when it loaded, execute the "myFunction"
		callback defined.
	*/
var JsonP = new Class({
	options: {
		onComplete: Class.empty,
		callBackKey: "callback",
		queryString: "",
		data: {},
		timeout: 5000,
		retries: 0
	},
	initialize: function(url, options){
		this.setOptions(options);
		this.url = this.makeUrl(url).url;
		this.fired = false;
		this.scripts = [];
		this.requests = 0;
		this.triesRemaining = [];
	},
/*	Property: request
		Executes the Json request.
	*/
	request: function(url, requestIndex){
		var u = this.makeUrl(url);
		if(!$chk(requestIndex)) {
			requestIndex = this.requests;
			this.requests++;
		}
		if(!$chk(this.triesRemaining[requestIndex])) this.triesRemaining[requestIndex] = this.options.retries;
		var remaining = this.triesRemaining[requestIndex]; //saving bytes
		dbug.log('retrieving by json script method: %s', u.url);
		var dl = (window.ie)?50:0; //for some reason, IE needs a moment here...
		(function(){
			var script = new Asset.javascript(u.url, {id: 'jsonp_'+u.index+'_'+requestIndex});
			this.fired = true;
			this.addEvent('onComplete', function(){
				try {script.remove();}catch(e){}
			}.bind(this));

			if(remaining) {
				(function(){
					this.triesRemaining[requestIndex] = remaining - 1;
					if(script.getParent() && remaining) {
						dbug.log('removing script (%o) and retrying: try: %s, remaining: %s', requestIndex, remaining);
						script.remove();
						this.request(url, requestIndex);
					}
				}).delay(this.options.timeout, this);
			}
		}.bind(this)).delay(dl);
		return this;
	},
	makeUrl: function(url){
		var index = (JsonP.requestors.contains(this))?
								JsonP.requestors.indexOf(this):
								JsonP.requestors.push(this) - 1;
		if(url) {
			var separator = (url.test('\\?'))?'&':'?';
			var jurl = url + separator + this.options.callBackKey + "=JsonP.requestors[" +
				index+"].handleResults";
			if(this.options.queryString) jurl += "&"+this.options.queryString;
			jurl += "&"+Object.toQueryString(this.options.data);
		} else var jurl = this.url;
		return {url: jurl, index: index};
	},
	handleResults: function(data){
		dbug.log('jsonp received: ', data);
		this.fireEvent('onComplete', [data, this]);
	}
});
JsonP.requestors = [];
JsonP.implement(new Options);
JsonP.implement(new Events);

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/utilities/jsonp.js,v $
$Log: jsonp.js,v $
Revision 1.15  2007/10/02 00:58:14  newtona
form.validator: fixed a bug where validation errors weren't removed under certain situations
tabswapper: .recall() no longer fails if no cookie name is set
jsonp: adding a 'return this'

Revision 1.14  2007/09/27 21:50:51  newtona
tagmaker: hide tooltips when the popup is hidden
jsonp: return the data AND the instance of jsonp oncomplete.

Revision 1.13  2007/08/30 17:52:11  newtona
removing some errant dbug lines; added one to jsonp

Revision 1.12  2007/08/28 20:38:51  newtona
doc update in jsonp
element.setPosition now accounts for fixed position relativeTo elements

Revision 1.11  2007/08/21 00:58:09  newtona
RTSS.History, RTSS.JsonP: added events for add, remove, empty, etc.
RTSS.js: most methods now return the remote class (XHR or JsonP)
UserHistory: add logic to announce actions (add, remove, etc.)
ProductToolbar: implemented place-holder compare function; removed some dbug lines
JsonP: tweaking retry logic

Revision 1.10  2007/08/20 21:14:31  newtona
tweaking jsonp timeout logic

Revision 1.9  2007/08/20 21:05:21  newtona
jsonp: added a timeout/retry system (defaults to not retry)

Revision 1.8  2007/08/20 18:11:49  newtona
Iframeshim: better handling for methods when the shim isn't ready
stickyWin: fixed a bug for the cssClass option in stickyWinHTML
html.table: push now returns the dom elements it creates
jsonp: fixed a bug; request no longer requires a url argument (my bad)
Fx.SmoothMove: just tidying up some syntax.
element.dimensions: updated getDimensions method for computing size of elements that are hidden; no longer clones element

Revision 1.7  2007/08/17 17:24:28  newtona
fixed a bug in jsonp; url is no longer a required argument for the request method

Revision 1.6  2007/08/15 01:03:32  newtona
Added more event info for Autocompleter.js
Slimbox no longer adds css to the page if there aren't any images found for the instance
Iframeshim now exits quietly if you try and position it before the dom is ready
jsonp now handles having more than one request open at a time
removed a console.log statement from window.cnet.js (shame on me for leaving it there)

Revision 1.5  2007/06/21 17:44:04  newtona
fixed a typo; same line was duplicated and I removed the errant one.

Revision 1.4  2007/03/05 19:30:46  newtona
added a short (50ms) delay for IE

Revision 1.3  2007/02/27 21:46:43  newtona
docs update; fixing references

Revision 1.2  2007/02/22 23:58:33  newtona
fixed a bug with the queryString option

Revision 1.1  2007/02/21 00:30:59  newtona
first commit


*/
/*	Script: product.picker.js
		Allows the user to pick a product from a data source.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		mootools - <Moo.js>, <Utility.js>, <Common.js>, <Function.js>, <Element.js>, <Array.js>, <String.js>, <Event.js>
		cnet - <Drag.Base.js>, <stickyWinFx.js>, <jsonp.js>, <element.shortcuts.js>
		
		Note:
		This script contains no <Picklet>s. This means that it isn't very useful unless you write your own
		or include some (such as <CNETProductPicker> or <NewsStoryPicker>
		
		Class: Picklet
		Container for all the information required to allow the user to search a data source and pick a result.
		
		Arguments:
		className - (string, required) the className associated with the picklet
		options - (object, required) a key/value object of options for the picklet
		
		Options:
			(all options are required unless otherwise noted)
			
			url - (string) the base url for the data source, defaults to the CNET API
						(http://api.cnet.com/restApi/v1.0/techProductSearch)
			baseHref - (string) the base url for images for the picker.
			descriptiveName - (string) the name to show in the select list of picklets available for an input
			callBackKey - (string) the wrapper for <JsonP> (see it for details), defaults to 'callback'
			data - (object) an object of key/value pairs to pass along with the request in the url
			getQuery - (function) returns an <Ajax> or <JsonP> object that has not yet been executed. Will 
									be passed the data in the above object and the data generated by the user input into
									the search form.
			inputs - (object) an object of key/value pairs for inputs for the form. See below for details.
			previewHtml - (function) a function that is passed a data result from the ajax/json results that will
								 return the html for the preview using that data object.
			resultsList - (function) a function that is passed the response from the ajax/json object above that
								 will turn that response into a list of items (an array) to be chosen from.
			listItemName - (function) a function that is passed a single item from the resultsList and returns
								 the name to be displayed in the list (string).
			listItemValue - (function) a functino that is passed a single item from the resultsList and returns
									the value that will be set to the value of the input for the picker.
			updateInput - (function) a function that is passed the input and the data item that the user selected
									that then does something with that selection (typically updating the input)
									
		Inputs:

			The inputs for the search form in the picker can be anything. Each input is described in the inputs
			object that is an option of the picklet (see the options>inputs section above).
			
			Each item in the inputs must be defined thusly. First, the key of the object will be used for the
			name of the input (i.e. inputs: {query: ....} will result in <input name="query"...>).
			
			The values for the object will be translated into the input and its descriptors and properties.
			
			tagName - the name of the tag ('input', 'select', etc.);
			type - the type of the input ('text', 'hidden', etc'
			instructions - text that will be displayed to the left of the input
			tip - tool-tip info that is displayed on hover. Format: CAPTION::TIP DEFINITION
			value - the value of the input by default. If type is 'select', this is an array of values.
			optionNames - if the type is 'select', an array of the option text values (what the user sees)
			style - an object of style properties for the input (i.e. style: {width: "100%"})
			
		Example:
			Here is an example picklet in it's entirety:
(start code)
var CNETProductPicker = new Picklet('CNETProductPicker',{
	url: 'http://api.cnet.com/restApi/v1.0/techProductSearch',
	descriptiveName: 'CNET Product Picker Sortable',
	callBackKey: 'callback', //see <JsonP> options
	data: {
		partKey: 'YOUR PARTNER KEY FROM API.CNET.COM',
		iod: 'hlPrice',
		viewType: 'json',
		sortDesc: 'true'
	}, //static data
	getQuery: function(data){ //return <Ajax> or <JsonP>
		return new JsonP(this.options.url, {
			callBackKey: this.options.callBackKey,
			data: $merge(this.options.data, data)
		});
	},
	inputs: {
		query: {
			tagName: 'input',
			type: 'text',
			instructions: 'search for: ',
			tip: 'cnet product search::input a product name and hit &lt;enter&gt; to get results',
			value: '',
			style: {
				width: '100%'
			}
		},
		orderBy: {
			tagName: 'select',
			instructions: 'order by: ',
			style: {
				width: '100%'
			},
			value: ['pop9%2Bdesc', 'edRating7'],
			optionNames: ['most popular', 'editor\'s rating']
		},
		submit: {
			tagName: 'input',
			type: 'submit',
			style: {
				cssFloat: 'right'
			},
			instructions:'',
			value: 'submit'
		}		
	}, //form builder
	previewHtml: function(data){
		var editors = "";
		var html = '<div class="dataId" style="color: #999; font-weight:bold; margin: 0px; padding: 0px;">id: '+data['@id'] +'</div>'+
						'<div class="dataDetails" style="font-size: 10px;"><img height="45" width="'+data.ImageURL[0]["@width"]+'" style="margin-left: 10px" src="'
							+data.ImageURL[1].$+'"/>' + '<br /><b>' + data.Name.$ + '</b>';
		if(data.EditorsRating && data.EditorsRating.$) 
			html += "<br/>editors' rating: "+data.EditorsRating.$;
		html += "<div>";
		if(data.LowPrice && data.LowPrice.$) html += 
			"<span class='productPickerPrices'>"+data.LowPrice.$ +"</span>";
		if(data.HighPrice && data.HighPrice.$ && (data.LowPrice.$ != data.HighPrice.$))
				html += " to <span class='productPickerPrices'>"+data.HighPrice.$ +"</span>";
		html += "</div></div>";
		html += "<div>";
		if(data.Offers && data.Offers['@numFound'] > 0) 
			html += "resellers: " + data.Offers["@numFound"];
		html += "</div>";
		return html;
	}, //html template for returned json data
	resultsList: function(results){
		if(results.CNETResponse.TechProducts && results.CNETResponse.TechProducts["@numFound"] > 0)
			return results.CNETResponse.TechProducts.TechProduct;
		return false;
	},
	listItemName: function(data){
		return data.Name.$
	}, //line item name for the selection list
	listItemValue: function(data){
		return data['@id'];
	},
	//handle the click event; user chooses an item, and this function updates the input 
	//(or does something else)
	updateInput: function(input, data) {
		input.value = data['@id'];
	}	
});
(end)
	*/
var Picklet = new Class({
	initialize: function(className, options){
		this.setOptions(options);
		this.className = className;
		this.getQuery = this.options.getQuery;
	}
});
Picklet.implement(new Options);


/*	Class: ProductPicker
		Handles the UI for picking products; requires at least one <Picklet>.
		
		Arguments:
		input - (dom element or id) the input that the ProductPicker references
		picklets - (array) an array of <Picklets>
		options - a key/value set of options
		
		Options:
		onShow - (function) callback to execute when the ProductPicker is displayed
		onPick - (function) callback to execute when the user clicks an entry
		title - (string) caption for the <StickyWin> popup; defaults to "Product picker"
		showOnFocus - (boolean) true (the default) means show the product picker when the user
									focuses the input
		additionalShowLinks - (array) array of dom elements or ids that show the picker when clicked
		stickyWinToUse - (reference) a reference to a <StickyWin> class to use for the popup; default
										 is <StickyWinFx>
		stickyWinOptions - (object) a key/value set of options to pass along to the <StickyWin>; defaults
										 to: offset x:20, y:20, position: "upperRight" (of the input), draggable: true
		moveIntoView - (boolean) moves the picker to be on screen if it is partially obscured; defaults to true
	*/
var ProductPicker = new Class({
	options: {
		onShow: Class.empty,
		onPick: Class.empty,
		title: 'Product picker',
		showOnFocus: true,
		additionalShowLinks: [],
		stickyWinToUse: StickyWinFx,
		stickyWinOptions: {
			fadeDuration : 200,
			draggable : true
		},
		moveIntoView: true,
		baseHref: 'http://www.cnet.com/html/rb/assets/global/Picker'
	},
	initialize: function(input, picklets, options){
		this.setOptions(options);
		this.input = $(input);
		this.picklets = picklets; //array of picklets
		this.setUpObservers();
		this.writeCss();
	},
	//default css props for the picker
	writeCss: function(){
		var css = "div.productPickerProductDiv div.results { overflow: 'auto'; width: 100%; margin-top: 4px }"+
							"div.productPickerProductDiv select { margin: 4px 0px 4px 0px}"+
							"div.pickerPreview div.sliderContent img {border: 1px solid #000}"+
							"div.pickerPreview div.sliderContent a {color: #0d63a0}";
		try {
			if(!$('pickerStyles')) {
				var style = new Element('style').setProperty('id','pickerStyles').injectInside($$('head')[0]);
				if (!style.setText.attempt(css, style)) style.appendText(css);
			}
		}catch(e){dbug.log('error: %s',e);}
	},
	//returns a select box of all the picklets for the given input
	getPickletList: function(){
		//if more than one
		if(this.picklets.length>1) {
			//make a select list for each one
			var selector = new Element('select').setStyle('width', '399px');
			this.picklets.each(function(picklet, index){
				var opt = new Element('option').setProperty('value',index);
				opt.text = picklet.options.descriptiveName;
				selector.adopt(opt);
			}, this);
			//when changed, show the new form
			selector.addEvent('change', function(){
				this.showForm(this.picklets[selector.getValue()]);
				this.focusInput(true);
			}.bind(this));
			return selector;
		} else return false;
	},
	//builds the picker object (happens only once)
	buildPicker: function(picklet){
		var contents = new Element('div');
		this.formBody = new Element('div'); //holds the form for each picklet
		this.pickletList = this.getPickletList(); //the select list of picklets
		if(this.pickletList) contents.adopt(this.pickletList);
		contents.adopt(this.formBody);
		//the layout for the picker
		var body = stickyWinHTML(this.options.title, contents, {
				width: '450px',
				closeTxt: 'close'
			}).addClass('productPickerProductDiv');
		//add the first form in the list of picklets
		this.showForm();
		return body;
	},
	//shows the search form for a given picklet
	showForm: function(picklet){
		//if not specified, use the first picklet available
		this.form = this.makeSearchForm(picklet || this.picklets[0]);
		//empty the form body and adopt the new picklet form
		this.formBody.empty().adopt(this.form);
		//results holder
		this.results = new Element('div').addClass('results');
		this.formBody.adopt(this.results);
		//set the fx object to null so that a new one will be created on show
		this.sliderFx = null;
	},
	makeSlider: function(){
		var png = (window.ie)?'gif':'png';
		//slider for the details
		this.slider = new Element('div').addClass('pickerPreview').setStyles({
background:'url('+this.options.baseHref+'/slider.'+png+') top right no-repeat',
				display: 'none',
				height:'250px',
				left:'439px',
				position:'absolute',
				top:'25px',
				width:'0px',
				overflow: 'hidden'
		}).injectInside(this.swin.win).addEvent('mouseover', function(){
			this.previewHover = true;
		}.bind(this)).addEvent('mouseout', function(){
			this.previewHover = false;
			(function(){if (!this.previewHover) this.hidePreview()}).delay(400, this);
		}.bind(this));
		//the content holder for the details within the slider
		this.sliderContent = new Element('div').injectInside(this.slider).setStyles({
			width: '130px',
			height: '200px',
			padding: '10px',
			margin: '10px 10px 0px 0px',
			overflow: 'auto',
			cssFloat: 'right'
		}).addClass('sliderContent');
	},
	//builds the form for searches for a given picklet
	makeSearchForm: function(picklet){
		//save which picklet the user is using at the moment
		this.currentPicklet = picklet;
		var formTable = new Element('table').setStyle('width','100%').setProperties({
			cellpadding: '0',
			cellspacing: '0'
		});
		var tBody = new Element('tbody').injectInside(formTable);
		var form = new Element('form').addEvent('submit', function(e){
			//when submitted, get the results for this picklet
			this.getResults(new Event(e).target, picklet);
		}.bind(this)).adopt(formTable).setProperty('action','javascript:void(0);');
		//for each input specified in the picklet, create an element
		$each(picklet.options.inputs, function(val, name){
			tBody.adopt(this.getSearchInputTr(val, name));
		}, this);
		return form;
	},
	//builds a table row for a given input in a picklet
	getSearchInputTr: function(val, name){
		try{
			var style = ($type(val.style))?val.style:{};
			//create the input object
			//this is I.E. hackery, because IE does not let you set the name of a DOM element.
			//thanks MSFT.
			var input = (window.ie)?new Element('<' + val.tagName + ' name="' + name + '" />'):
					new Element(val.tagName).setProperty('name', name);
			input.setStyles(style);
			//if the type is specified, set it
			if(val.type)input.setProperty('type', val.type);
			//if there's a tooltip, use it
			if(val.tip && Tips){
				input.setProperty('title', val.tip);
				new Tips([input], {
					onShow: function(tip){
						this.shown = true;
						(function(){
							if(this.shown)
								$(tip).setStyles({ display:'block', opacity: 0 }).effect('opacity', { duration: 300 }).start(0,.9);
						}).delay(500, this);
					},
					onHide: function(tip){
						tip.setStyle('visibility', 'hidden');
						this.shown = false;
					}
				});
			}
			//if it's a select list
			if(val.tagName == "select"){
				//create options for each input value
				val.value.each(function(option, index){
					var opt = new Element('option').setProperty('value',option);
					opt.text = (val.optionNames && val.optionNames[index])?$pick(val.optionNames[index], option):option;
					input.adopt(opt);
				});
			} else input.value = $pick(val.value,""); //else use the value...
			var holder = new Element('tr');
					var colspan=0;
					//if instructions are supplied, add them to the table
					if(val.instructions) holder.adopt(new Element('td').setHTML(val.instructions));
					else colspan=2; //otherwise make the input span the whold table width
					var inputTD = new Element('td').adopt(input);
					if(colspan)inputTD.setProperty('colspan', colspan);
					holder.adopt(inputTD);
			return holder;
		}catch(e){dbug.log(e); return false;}
	},
	//get results using the functions specified in the picklet
	getResults: function(form, picklet){
		if(form.getTag() != "form") form = $$('form').filter(function(fm){ return fm.hasChild(form) })[0];
		if(!form) {
			dbug.log('error computing form');
			return null;
		}
		//get the query object (JsonP or Ajax)
		var query = picklet.getQuery(unescape(form.toQueryString()).parseQuery());
		//handle the results
		query.addEvent('onComplete', this.showResults.bind(this));
		//execute the request
		query.request();
		return this;
	},
	//handle the results from the request
	showResults: function(data){
		var empty = false;
		if(this.results.innerHTML=='') { //no previous results
			empty = true;
			this.results.setStyles({
				height: '0px',
				border: '1px solid #666',
				padding: '0px',
				overflow: 'auto',
				opacity: 0
			});
		} else this.results.empty(); //empty previous results
		//get the items from the result set - an array
		this.items = this.currentPicklet.options.resultsList(data);
		//if there are any
		if(this.items && this.items.length > 0) {
			//loop through them
			this.items.each(function(item, index){
				var name = this.currentPicklet.options.listItemName(item);
				var value = this.currentPicklet.options.listItemValue(item);
				//add it to the list in the picker
				this.results.adopt(this.makeProductListEntry(name, value, index));
			}, this);
		} else this.results.setHTML("Sorry, there don't seem to be any items for that search");
		//show the results
		this.results.effects().start({ height: 200, opacity: 1 });
		//apply the list styles to the list elements
		this.listStyles();
		//make sure the picker is entirely visible
		this.getOnScreen.delay(500, this);
	},
	//moves the picker to be entirely on screen
	getOnScreen: function(){
		if(document.compatMode == "BackCompat") return;
		var s = this.swin.win.getCoordinates();
		if(s.top < window.getScrollTop()) {
			this.swin.win.effect('top').start(window.getScrollTop()+50);
			return;
		}
		if(s.top+s.height > window.getScrollTop()+window.getHeight() && window.getHeight()>s.height) {
			this.swin.win.effect('top').start(window.getScrollTop()+window.getHeight()-s.height-100);
			return;
		}
		try{this.swin.shim.show.delay(500, this.swin.shim);}catch(e){}
		return;
	},
	listStyles: function(){
		var defaultStyle = {
				cursor: 'pointer',
				borderBottom: '1px solid #ddd',
				padding: '2px 8px 2px 8px',
				backgroundColor:'#fff',
				color: '#000',
				fontWeight: 'normal'
			};
		var hoverStyle = {
				backgroundColor:'#fcfbd1',
				color: '#d56a00'
			};
		var selectedStyle = $merge(defaultStyle, {
				color: '#D00000',
				fontWeight: 'bold',
				backgroundColor: '#eee'
			});
		//loop through the results and apply the appropriate style to each one
		this.results.getElements('div.productPickerProductDiv').each(function(p){
			var useStyle = (this.input.value.toInt() == p.getProperty('val').toInt())?selectedStyle:defaultStyle;
			p.setStyles(useStyle);
			if(!window.ie) {//ie doesn't like these mouseover behaviors...
				p.addEvent('mouseover', function(){ p.setStyles(hoverStyle); }.bind(this));
				p.addEvent('mouseout', function(){ p.setStyles(useStyle); });
			}
		}, this);
	},
	//returns a list item for the picker list
	makeProductListEntry: function(name, value, index){
		var pDiv = new Element("div").addClass('productPickerProductDiv').adopt(
				new Element("div").setHTML(name)
			).setProperty('val', value);
		//on mouseover show the details
		pDiv.addEvent('mouseenter', function(e){
			this.preview = true;
			this.sliderContent.setHTML("");
			var content = this.getPreview(index);
			if($type(content)=="string") this.sliderContent.setHTML(content);
			else if($(content)) this.sliderContent.adopt(content);
			this.showPreview.delay(200, this);
		}.bind(this));
		//on mouseover hide the details
		pDiv.addEvent('mouseleave', function(e){
			this.preview = false;
			(function(){if(!this.previewHover) this.hidePreview();}).delay(400, this);
		}.bind(this));
		//on click set the input value
		pDiv.addEvent('click', function(){
			this.currentPicklet.options.updateInput(this.input, this.items[index]);
			this.fireEvent('onPick', [this.input, this.items[index], this]);
			this.hide();
			this.listStyles.delay(200, this);
		}.bind(this));
		return pDiv;
	},
	//make the instance of the stickyWin
	makeStickyWin: function(){
		if(document.compatMode == "BackCompat") this.options.stickyWinOptions.relativeTo = this.input;
		this.swin = new this.options.stickyWinToUse($merge(this.options.stickyWinOptions, {
			draggable: true,
			content: this.buildPicker()
		}));
	},
	focusInput: function(force){
		if ((!this.focused || $pick(force,false)) && this.form.getElement('input')) {
			this.focused = true;
			try { this.form.getElement('input').focus(); } catch(e){}
		}
	},
/*	Property: show
		Shows the ProductPicker.
	*/
	show: function(){
		if (!this.swin) this.makeStickyWin();
		if (!this.slider) this.makeSlider();
		if (!this.swin.visible) this.swin.show();
		this.focusInput();
	},
/*	Property: hide
		Hides the ProductPicker.
	*/
	hide: function(){
		$$('.tool-tip').hide();
		this.swin.hide();
		this.focused = false;
	},
	//observe all the input and links
	setUpObservers: function(){
		try {
			if(this.options.showOnFocus) this.input.addEvent('focus', this.show.bind(this));
			if(this.options.additionalShowLinks.length>0) {
				this.options.additionalShowLinks.each(function(lnk){
					$(lnk).addEvent('click', this.show.bind(this));
				}, this);
			}
		}catch(e){dbug.log(e);}
	},
	//show the preview in the slider
	showPreview: function(index){
		width = this.currentPicklet.options.previewWidth || 150;
		this.sliderContent.setStyle('width', (width-30)+'px');
		if(!this.sliderFx) this.sliderFx = new Fx.Elements([this.slider, this.swin.win]);
		this.sliderFx.clearChain();
		$extend(this.sliderFx.options, {
				duration: 1000, 
				transition: Fx.Transitions.elasticOut
			});
		if(this.preview && this.slider.getStyle('width').toInt() < width-5) {
			this.slider.show('block');
			this.sliderFx.start({
				'0':{//the slider
					'width':width
				},
				'1':{//the popup window (for ie)
					'width':width+450
				}
			});
		}
	},
	//hide the preview box
	hidePreview: function(){
		if(!this.preview) {
		$extend(this.sliderFx.options, {
				duration: 250, 
				transition: Fx.Transitions.backIn
			});
			this.sliderFx.clearChain();
			this.sliderFx.start({
				'0':{//the slider
					'width':[this.slider.getStyle('width').toInt(),0]
				},
				'1':{//the popup window (for ie)
					'width':[this.swin.win.getStyle('width').toInt(), 450]
				}
			}).chain(function(){
				this.slider.hide();
			}.bind(this));
		}
	},
	//get the preview html from the picklet
	getPreview: function(index){
		return this.currentPicklet.options.previewHtml(this.items[index]);
	}
});
ProductPicker.implement(new Options);
ProductPicker.implement(new Events);


/*	Section: ProductPicker global functions
		These functions are available to the <ProductPicker> object itself, not instances of it.
		Use these functions to add <Picklets> to the ProductPicker object, which will be available
		to all instances of the ProductPicker class.
	*/
$extend(ProductPicker, {
	picklets: [],
/*	Property: add
		Adds a <Picklet> to the list of Picklets available to the <ProductPicker> class.
		
		Arguments:
		picklet - a <Picklet>
	*/
	add: function(picklet){
		if(! picklet.className) {
			dbug.log('error: cannot add Picklet %o; missing className: %s', picklet, picklet.className);
			return;
		}
		this.picklets[picklet.className] = picklet;
	},
/*	Property: addAllThese
		Adds several <Picklet>s to the list of Picklets available to the <ProductPicker> class.
		
		Arguments:
		picklets - an array of <Picklet>s
	*/
	addAllThese: function(picklets){
		picklets.each(function(picklet){
			this.add(picklet);
		}, this);
	},
/*	Property: getPicklet
		Returns a <Picklet> that matches the given className (or false, if none was found).
		
		Arguments:
		className - the className for the <Picklet>
	*/
	getPicklet: function(className){
		return ProductPicker.picklets[className]||false;
	}
});

/*	Class: FormPickers
		Adds the appropriate <ProductPickers> to all the inputs in a form as defined in the 
		classNames assigned to each input.
		
		Arguments:
		form - a form element or id
		options - a key/value set of options
		
		Options:
		inputs - (string) selector of input types to parse; defaults to 'input' 
						 (but could include textarea, select, etc.)
		additionalShowLinkClass - (string) className for links that should show the
						 <ProductPicker> when clicked. Each input in the form will be checked to
						 see if it's next sibling (i.e. the dom element right after the input) has
						 this class and, if so, the element will have an event attached so that the
						 picker is shown when it is clicked.
		pickletOptions - (object) options passed along to each ProductPicker created.
	*/
var FormPickers = new Class({
	options: {
		inputs: 'input',
		additionalShowLinkClass: 'openPicker',
		pickletOptions: {}
	},
	initialize: function(form, options){
		this.setOptions(options);
		this.form = $(form);
		this.inputs = this.form.getElementsBySelector(this.options.inputs);
		this.setUpInputs();
	},
	//add pickers for each input that needs one
	setUpInputs: function(inputs){
		inputs = $pick(inputs, this.inputs);
		inputs.each(this.addPickers.bind(this));
	},
	//add the appropriate pickers to an input
	addPickers: function(input){
		var picklets = [];
		//get all the class names
		input.className.split(" ").each(function(clss){
			//if the class is a picklet, add it to the list
			if(ProductPicker.getPicklet(clss)) picklets.push(ProductPicker.getPicklet(clss));
		}, this);
		//if there's a dom element next to the input and it has the link class
		if(input.getNext() && input.getNext().hasClass(this.options.additionalShowLinkClass))
			//add it to the options for this picker
			this.options.pickletOptions.additionalShowLinks = [input.getNext()];
		//make the picker
		if(picklets.length>0)  new ProductPicker(input, picklets, this.options.pickletOptions);
	}
});
FormPickers.implement(new Options);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/product.picker.js,v $
$Log: product.picker.js,v $
Revision 1.30  2007/10/18 21:43:35  newtona
updating components that reference images and assets at www.cnet.com to allow for easy over-writing for that source url

Revision 1.29  2007/10/15 17:43:18  newtona
clearing up a javascript warning

Revision 1.28  2007/10/10 22:16:50  newtona
CNETAPI.Category.Browser: docs update
product.picker: bug fix with getting data
download.product.picklet: new file
redball.common.full.js.bat: added download.product.picklet.js

Revision 1.27  2007/09/24 20:55:49  newtona
new file: StickyWin.Ajax - adds ajax support to all stickywin classes (creates new classes, just append .Ajax to any of the existing ones)
updated redball common full to include StickyWin.Ajax
date.picker, product.picker - updated syntax to use Element.empty
form.validator - now passes along the event object to the onFormValidate event so that the form submit event can be stopped if you like
popupdetails - added html response support; you can now return the html you wish to display rather than a json object; only applies to ajax. Also added a cache so that multiple requests are not made for the same url.
stickyWinHTML - ractored so that options are now, you know, *optional*
MooScroller - added support for width option for horizontal scrolling

Revision 1.26  2007/07/18 16:15:21  newtona
forgot to bind the style objects in the setText.attempt method...

Revision 1.25  2007/07/16 21:00:21  newtona
using Element.setText for all style injection methods (fixes IE6 problems)
moving Element.setText to element.legacy.js; this function is in Mootools 1.11, but if your environment is running 1.0 you'll need this.

Revision 1.24  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.23  2007/05/17 19:45:43  newtona
product picker: hide() now hides tooltips; onPick passes in a 3rd argument that is the picker
stickyWinHTML: fixed a bug with className options for buttons
html.table: fixed a bug with className options for buttons

Revision 1.22  2007/05/16 20:09:41  newtona
adding new js files to redball.common.full
product.picker.js now has no picklets; these are in the implementations/picklets directory
ProductPicker now detects if there is no doctyp and, if not, sets the position of the picker to be fixed (no IE6 support)
small docs update in element.cnet.js
added new picklet: CNETProductPicker_PricePath
added new picklet: NewsStoryPicker_Path
new file: clipboard.js (allows you to insert text into the OS clipboard)
new file: html.table.js (automates building html tables)
new file: element.forms.js (for managing text inputs - get selected text information, insert content around selection, etc.)

Revision 1.21  2007/05/09 20:45:36  newtona
moving picklets into their own location in the implementations content

Revision 1.20  2007/05/07 21:37:45  newtona
product picker now shows up in the middle of the screen by default

Revision 1.19  2007/05/04 22:19:46  newtona
adding onPick event call

Revision 1.18  2007/05/04 22:17:23  newtona
updating cnet api stuff

Revision 1.17  2007/05/04 17:25:25  newtona
updating my default partner key stuff

Revision 1.16  2007/05/03 18:24:24  newtona
iframeshim: removed a dbug line
modalizer: only hide select lists for browsers that need it
product picker: added a try/catch, updated cnet api link/code

Revision 1.15  2007/03/13 19:17:08  newtona
added close button

Revision 1.14  2007/03/08 23:29:31  newtona
date picker: strict javascript warnings cleaned up
popup details strict javascript warnings cleaned up
product.picker: strict javascript warnings cleaned up, updating input now fires onchange event
confirmer: new file

Revision 1.13  2007/03/05 21:52:14  newtona
fixed a bug where the cnet api only returned 1 result; it's not an array

Revision 1.12  2007/03/05 19:55:07  newtona
css tweak for link color in preview

Revision 1.11  2007/03/05 19:45:55  newtona
removed a dbug line

Revision 1.10  2007/03/05 19:36:28  newtona
numerous interface fixes for IE (hurah)
fixed the query string handling for spaces

Revision 1.9  2007/03/01 23:21:06  newtona
tweaking focus logic

Revision 1.8  2007/03/01 23:11:00  newtona
product picker now focuses it's input when you open it.

Revision 1.7  2007/02/27 21:46:43  newtona
docs update; fixing references

Revision 1.6  2007/02/24 00:58:26  newtona
picklet updates - just look & feel stuff

Revision 1.5  2007/02/24 00:33:07  newtona
undoing my css change

Revision 1.4  2007/02/24 00:28:07  newtona
adjusting css location of preview

Revision 1.3  2007/02/22 23:33:46  newtona
added descriptive name to cnet product picker

Revision 1.2  2007/02/22 22:04:38  newtona
updating the input is now a function in the picklet options

Revision 1.1  2007/02/22 21:27:43  newtona
moved product picker from utilities dir
fixed missing ; in stickywin html

Revision 1.3  2007/02/22 20:36:04  newtona
changed references from Picker to ProductPicker

Revision 1.2  2007/02/22 20:01:44  newtona
fixed missing ;

Revision 1.1  2007/02/22 18:18:24  newtona
*** empty log message ***


*/
/*	Script: cnet.product.picklet.js
		This is a <Picklet> for the <ProductPicker> class that returns CNET Products for a given keyword.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Everything listed in <product.picker.js>

		Note:
		Add the className to any input and then create a new <FormPickers> and these 
		will automatically be applied. See <ProductPicker.add> on how to add your own.
		
		Property: CNETProductPicker
		A simple query search for CNET Products (electronics, computers, software, etc.).
	*/
var CNETProductPickerBase = {
	previewWidth: 150,
	descriptiveName: 'CNET Product Picker',
	url: 'http://api.cnet.com/restApi/v1.0/techProductSearch',
	callBackKey: 'callback', //see <JsonP> options
	data: {
		partKey: '19926949750937665684988687810562', //this is my code - aaron newton
		iod: 'hlPrice',
		viewType: 'json'
	}, //static data
	getQuery: function(data){ //return <Ajax> or <JsonP>
		//clean any url encoding from the data, as JsonP encodes it again
		$each(data, function(val, key) { data[key] = unescape(val); });
		return new JsonP(this.options.url, {
			callBackKey: this.options.callBackKey,
			data: $merge(this.options.data, data)
		});
	},
	inputs: {
		query: {
			tagName: 'input',
			type: 'text',
			instructions: '',
			tip: 'cnet product search::input a product name and hit &lt;enter&gt; to get results',
			value: '',
			style: {
				width: '100%'
			}
		}
	}, //form builder
	previewHtml: function(data){
		var editors = "";
		var html = '<div class="dataId" style="color: #999; font-weight:bold; margin: 0px; padding: 0px;">id: '+data['@id'] +'</div>'+
						'<div class="dataDetails" style="font-size: 10px;"><a href="'+ data.ReviewURL.$ +'"><img height="45" width="'+data.ImageURL[0]["@width"]+'" style="margin-left: 10px" src="'
							+data.ImageURL[1].$+'"/></a><br /><b><a href="'+ data.ReviewURL.$ +'">' + data.Name.$ + '</a></b>';
		if(data.EditorsRating && data.EditorsRating.$) 
			html += "<br/>editors' rating: "+data.EditorsRating.$;
		html += "<div>";
		if(data.LowPrice && data.LowPrice.$) html += 
			"<span class='productPickerPrices'>"+data.LowPrice.$ +"</span>";
		if(data.HighPrice && data.HighPrice.$ && (data.LowPrice.$ != data.HighPrice.$))
				html += " to <span class='productPickerPrices'>"+data.HighPrice.$ +"</span>";
		html += "</div></div>";
		html += "<div>";
		if(data.Offers && data.Offers['@numFound'] > 0) 
			html += "resellers: " + data.Offers["@numFound"];
		html += "</div>";
		return html;
	}, //html template for returned json data
	resultsList: function(results){
		if(results.CNETResponse.TechProducts && results.CNETResponse.TechProducts["@numFound"] > 0) {
			if(results.CNETResponse.TechProducts["@numFound"] > 1) return results.CNETResponse.TechProducts.TechProduct;
			else return [results.CNETResponse.TechProducts.TechProduct];
		}
		return false;
	},
	listItemName: function(data){
		return data.Name.$
	}, //line item name for the selection list
	listItemValue: function(data){
		return data['@id'];
	},
	//handle the click event; user chooses an item, and this function updates the input 
	//(or does something else)
	updateInput: function(input, data) {
		input.value = data['@id'];
		input.fireEvent('change');
	}	
};
	
var CNETProductPicker = new Picklet('CNETProductPicker',CNETProductPickerBase);
ProductPicker.add(CNETProductPicker);

/*	Class: CNETProductPicker_ReviewPath 
		Extends <CNETProductPicker> to return a path to the review instead of the id.
 */
var CNETProductPicker_ReviewPath = new Picklet('CNETProductPicker_ReviewPath', $merge(CNETProductPickerBase, {
		descriptiveName: 'CNET Product Picker: Review URL',
		updateInput: function(input, data) {
			var url = data.ReviewURL.$;
			if (url.indexOf("?")>=0) url = url.substring(0,url.indexOf("?"));
			input.value = url;
			input.fireEvent('change');
		}
	})
);
ProductPicker.add(CNETProductPicker_ReviewPath);
/*	Class: CNETProductPicker_PricePath 
		Extends <CNETProductPicker> to return a path to the price page instead of the id.
 */
var CNETProductPicker_PricePath = new Picklet('CNETProductPicker_ReviewPath', $merge(CNETProductPickerBase, {
		descriptiveName: 'CNET Product Picker: Price URL',
		updateInput: function(input, data) {
			var url = data.PriceURL.$;
			if (url.indexOf("?")>=0) url = url.substring(0,url.indexOf("?"));
			input.value = url;
			input.fireEvent('change');
		}
	})
);
ProductPicker.add(CNETProductPicker_PricePath);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.implementations/picklets/cnet.product.picklet.js,v $
$Log: cnet.product.picklet.js,v $
Revision 1.2  2007/05/16 20:09:45  newtona
adding new js files to redball.common.full
product.picker.js now has no picklets; these are in the implementations/picklets directory
ProductPicker now detects if there is no doctyp and, if not, sets the position of the picker to be fixed (no IE6 support)
small docs update in element.cnet.js
added new picklet: CNETProductPicker_PricePath
added new picklet: NewsStoryPicker_Path
new file: clipboard.js (allows you to insert text into the OS clipboard)
new file: html.table.js (automates building html tables)
new file: element.forms.js (for managing text inputs - get selected text information, insert content around selection, etc.)

Revision 1.1  2007/05/10 00:21:05  newtona
moved from product.picker.js


*/
/*	Script: confirmer.js
		Fades a message in and out for the user to tell them that some event (like an ajax save) has occurred.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools - <Moo.js>, <Utilities.js>, <Common.js>, <Array.js>, <String.js>, <Element.js>, <Function.js>, <Fx.Style.js>
		CNET - <element.shortcuts.js>, <element.dimensions.js>, <element.position.js>
		
		Class: Confirmer
		Fades a message in and out for the user to tell them that some event (like an ajax save) has occurred.
		
		Arguments:
		options - (object) a key/value set of options
		
		Options:
		reposition - (boolean) if the element that is going to fade in and out is already present in the 
									DOM and you want to leave it where it is, set this to false and it will just fade
									in and out; defaults to true
		positionOptions - (object) options to pass along to <Element.setPosition>; see below.
		msg - (string, DOM element, or DOM element id) default confirmation message; can be overwritten at the time of
						 prompting (see <Confirmer.prompt>). If the item is a DOM element (or id) then the element will get 
						 the transition, otherwise, the message string will be inserted into a new div element and positioned.
						 Defaults to "your changes have been saved"
		msgContainerSelector - (string; css selector) if the DOM element that's fading in and out contains more HTML,
									with a child element that contains the actual string of your message, this selector describes
									where that string is found within that html, so that new messages can be swapped in and out
									without altering your HTML. Defaults to ".body". If this element is not found, it'll replace 
									the innerHTML of the entire container with the string.
		delay: (integer) delay (in ms) to wait after <prompt> is called before the message fades in. This is useful when
									the user might create numerous prompt events in a row. If they create more than one event
									within this delay period, the prompt will wait until the last one to actually convey the message.
		pause: (integer) period to leave the message visible until fading back out
		effectOptions: (object) options object to be passed to Fx.Style; defaults to {duration: 500}
		prompterStyle: (object) css style object to apply to the style box; only used if the msg option is a string.
		
		
		positionOptions:
		relativeTo - (DOM element or ID) if repositioning (see above), what is it relative to. See
								 <Element.setPosition>. Defaults to document.body.
		position - (string) see <Element.setPosition>; defaults to "upperRight"; only used if reposition is true
		offset - (object) an offset object with x/y values; defaults to {x: -225, y:0}; only used if reposition is true
		zIndex - (integer) the zIndex of the prompter; only used if reposition is true
		onComplete - (function) function to execute when the message finishes fading out
		
		Notes & Examples:
		<Confirmer> concerns itself mostly with fading your message in and out. If your message is already in the DOM, you can create a Confirmer and then just fade that message in and out in place:
(start code)
<input id="myInput" ...> <span id="savedMsg" style="visibility: hidden">your changes have been saved</span>
<script>
var myConf = new Confirmer({
	msg: 'savedMsg'
});
$('myInput').addEvent('change', function(){
	new Ajax(..., {onSuccess: myConf.prompt});
});
</script>
(end)

	You can also position the confirmation element wherever you want it and, additionally, you can pass in a string for the message or a dom element.

(start code)
var myConf = new Confirmer({
	msg: 'your changes are saved!',
	positionOptions: {
		relativeTo: 'myInput',
		position: 'bottomLeft'
	}
});
...
myConf.prompt();
(end)

	The message can be changed at prompt time, so you can reuse an element as you like.
(start code)
var myConf = new Confirmer({
	msg: 'your changes are saved!',
	positionOptions: {
		relativeTo: 'myInput',
		position: 'bottomLeft'
	}
});
...
myConf.prompt({msg: 'your changes were NOT saved'});
(end)
	*/
var Confirmer = new Class({
	options: {
		reposition: true, //for elements already in the DOM
		//if position = false, just fade
		positionOptions: {
			relativeTo: false,
			position: 'upperRight', //see <Element.setPosition>
			offset: {x:-225,y:0},
			zIndex: 9999
		},
		msg: 'your changes have been saved', //string or dom element
		msgContainerSelector: '.body',
		delay: 250,
		pause: 500,
		effectOptions:{
			duration: 500
		},
		prompterStyle:{
			padding: '2px 6px',
			border: '1px solid #9f0000',
			backgroundColor: '#f9d0d0',
			fontWeight: 'bold',
			color: '#000',
			width: '210px'			
		},
		onComplete: Class.empty
	},
	initialize: function(options){
			this.setOptions(options);
			this.options.positionOptions.relativeTo = this.options.positionOptions.relativeTo || document.body;
			this.prompter = ($(this.options.msg))?$(this.options.msg):this.makePrompter(this.options.msg);
			if(this.options.reposition){
				this.prompter.setStyles({
					position: 'absolute',
					display: 'none',
					zIndex: this.options.positionOptions.zIndex
				});
				if(this.prompter.fxOpacityOk()) this.prompter.setStyle('opacity',0);
			} else if(this.prompter.fxOpacityOk()) this.prompter.setStyle('opacity',0);
			else this.prompter.setStyle('visibility','hidden');
			if(!this.prompter.getParent())window.addEvent('domready', function(){
					this.prompter.injectInside(document.body);
			}.bind(this));
		try {
			this.msgHolder = this.prompter.getElement(this.options.msgContainerSelector);
			if(!this.msgHolder) this.msgHolder = this.prompter;
		} catch(e){dbug.log(e)}
	},
	makePrompter: function(msg){
		try {
			return new Element('div').setStyles(this.options.prompterStyle).appendText(msg);
		}catch(e){dbug.log(e); return prompter}
	},
/*	Property: prompt
		Fades in and out the message.
		
		Arguments:
		options - a key/value set of options
		
		Options:
		msg - (string or DOM element) the message to display
		pause - (integer) the duration (in ms) to leave the message visible
		delay - (integer) the duration (in ms) to wait before displaying the message
		positionOptions - (object) options object to pass to <Element.setPosition>
		saveAsDefault - (boolean) overwrite the options specified at instantiation with 
										these new values; defaults to false
										
		Note:
		All of the above options are not required and will default to the values stored
		in the options of the instance. The saveAsDefault option will update the stored
		values with those passed in.
	*/
	prompt: function(options){
		if(!this.paused)this.stop();
		var msg = (options)?options.msg:false;
		options = $merge(this.options, {saveAsDefault: false}, options||{});
		if ($(options.msg) && msg) this.msgHolder.empty().adopt(options.msg);
		else if (!$(options.msg) && options.msg) this.msgHolder.empty().appendText(options.msg);
		if(!this.paused) {
			if(options.reposition) this.position(options.positionOptions);
			(function(){
				this.timer = this.fade(options.pause);
			}).delay(options.delay, this);
		}
		if(options.saveAsDefault) this.setOptions(options);
	},
	fade: function(pause){
		this.paused = true;
		pause = $pick(pause, this.options.pause);
		if(!this.fx && this.prompter.fxOpacityOk()) {
			this.fx = this.prompter.effect('opacity', this.options.effectOptions);
			this.fx.clearChain();
		}
		if(this.options.reposition) this.prompter.setStyle('display','block');
		if(this.prompter.fxOpacityOk()){
			this.prompter.setStyle('visibility','visible');
			this.fx.start(0,1).chain(function(){
				this.timer = (function(){
					this.fx.start(0).chain(function(){
						if(this.options.reposition) this.prompter.hide();
						this.paused = false;
					}.bind(this));
				}).delay(pause, this);
			}.bind(this));
		} else {
			this.prompter.setStyle('visibility','visible');
			this.timer = (function(){
				this.prompter.setStyle('visibility','hidden');
				this.fireEvent('onComplete');
				this.paused = false;
			}).delay(pause+this.options.effectOptions.duration, this);
		}
	},
/*	Property: stop
		Stops the element and hides it immediately.
	*/
	stop: function(){	
		this.paused = false;
		$clear($pick(this.timer, false));
		if(this.fx) this.fx.set(0);
		if(this.options.reposition) this.prompter.hide();
	},
	position: function(positionOptions){
		this.prompter.setPosition($merge(this.options.positionOptions, positionOptions));
	}
});
Confirmer.implement(new Options);
Confirmer.implement(new Events);

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/confirmer.js,v $
$Log: confirmer.js,v $
Revision 1.12  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.11  2007/04/09 21:35:49  newtona
ok. actually fixed the DOM destruction bug...

Revision 1.10  2007/04/09 20:09:07  newtona
syntax problem - left a "this"

Revision 1.9  2007/04/09 20:01:25  newtona
fixed a nasty bug that destroyed the document object!

Revision 1.8  2007/03/30 19:32:20  newtona
changing .flush to .empty

Revision 1.7  2007/03/29 23:12:00  newtona
confirmer now checks for a bg color in IE6 to use crossfading (see Element.fxOpacityOk)
fixed an IE7 css layout issue in stickyDefaultHTML
StickyWin now uses Element.flush
StickyWinFx.Drag now temporarily shows the sticky win (with opacity 0) to execute makeDraggable and makeResizable to prevent a Safari bug

Revision 1.6  2007/03/28 18:08:02  newtona
confirmer now uses Element.fxOpacityOk

Revision 1.5  2007/03/15 18:32:01  newtona
removed a dbug line

Revision 1.4  2007/03/09 01:00:12  newtona
docs update

Revision 1.3  2007/03/09 00:59:26  newtona
numerous layout tweaks

Revision 1.2  2007/03/08 23:59:35  newtona
doc typo

Revision 1.1  2007/03/08 23:29:31  newtona
date picker: strict javascript warnings cleaned up
popup details strict javascript warnings cleaned up
product.picker: strict javascript warnings cleaned up, updating input now fires onchange event
confirmer: new file


*/
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
/*	Script: html.table.js
		Builds table elements with methods to add rows quickly.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools - <Moo.js>, <Utilities.js>, <Common.js>, <Array.js>, <String.js>, <Element.js>, <Function.js>

		Class: HtmlTable
		Builds table elements with methods to add rows quickly.
		
		Arguments: 
		options - (object) a key/value set of options
		
		Options:
		properties - a set of properties for the Table element (defaults to cellpadding: 0, cellspacing: 0, border: 0)
		rows - (array) an array of row objects (see <HtmlTable.push>)
		
		Properties:
		table - the table DOM element (which you would inject into your document somewhere)
	*/
var HtmlTable = new Class({
	options: {
		properties: {
			cellpadding: 0,
			cellspacing: 0,
			border: 0
		},
		rows: []
	},
	initialize: function(options) {
		this.setOptions(options);
		if(this.options.properties.className){
			this.options.properties['class'] = this.options.properties.className;
			delete this.options.properties.className;
		}
		this.table = new Element('table').setProperties(this.options.properties);
		this.tbody = new Element('tbody').injectInside(this.table);
		this.options.rows.each(this.push.bind(this));
	},
	//row = [{content: <content>, properties: {colspan: 2, rowspan: 3, class: "cssClass", style: "border: 1px solid blue"}]
	//OR
	//row = [<content>,<content>,etc.]

/*	Property: row
		Inserts a new table row.
		
		Arguments:
		row - (array) the data for the row.
		
		Row data:
		Row data can be in either of two formats.
		
		simple - an array of strings that will be inserted into each table data
		detailed - an array of objects with definitions for content and properties for each td
		
		Example:
(start code)
var myTable = new HtmlTable();
myTable.push(['value 1','value 2', 'value 3']); //new row
myTable.push([
	{
		content: 'value 4',
		properties: {
			colspan: 2,
			className: 'doubleWide',
			style: '1px solid blue'
	},
	{
		content: 'value 5'
	}
]);
myTable.injectInside(document.body);

RESULT:
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td>value 1</td>
		<td>value 2</td>
		<td>value 3</td>
	</tr>
	<tr>
		<td colspan="2" class="doubleWide" style="1px solid blue">value 4</td>
		<td>value 5</td>
	</tr>
</table>
(end)
	
	Returns:
	An object containing the tr and td tags. Looks like this:
	> {tr: theTableRow, tds: [td, td, td]}
	*/
	push: function(row) {
		var tr = new Element('tr').injectInside(this.tbody);
		var tds = row.map(function (tdata) {
			var td = new Element('td').injectInside(tr);
			if(tdata.properties) {
				if(tdata.properties.className){
					tdata.properties['class'] = tdata.properties.className;
					delete tdata.properties.className;
				}
				td.setProperties(tdata.properties);
			}
			function setContent(content){
				if($(content)) td.adopt($(content));
				else td.setHTML(content);
			};
			if(tdata.content) setContent(tdata.content);
			else setContent(tdata);
			return td;
		}, this);
		return {tr: tr, tds: tds};
	}
});
HtmlTable.implement(new Options);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/layout.widgets/html.table.js,v $
$Log: html.table.js,v $
Revision 1.5  2007/09/15 00:07:08  newtona
collission in last check in; merged and re-doing.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
fixed a bug with validate-digits in form validator
new class: TagMaker
implemented TagMaker into simple.editor
updated caption (which is the drag handle for stickyWinHTML) to be the entire width of the caption area
html.table: docs update
tabswapper: docs update

Revision 1.4  2007/08/20 18:11:49  newtona
Iframeshim: better handling for methods when the shim isn't ready
stickyWin: fixed a bug for the cssClass option in stickyWinHTML
html.table: push now returns the dom elements it creates
jsonp: fixed a bug; request no longer requires a url argument (my bad)
Fx.SmoothMove: just tidying up some syntax.
element.dimensions: updated getDimensions method for computing size of elements that are hidden; no longer clones element

Revision 1.3  2007/06/12 20:46:20  newtona
added tbody to html.table.js
added legacy argument support to Fx.SmoothShow

Revision 1.2  2007/05/17 19:45:43  newtona
product picker: hide() now hides tooltips; onPick passes in a 3rd argument that is the picker
stickyWinHTML: fixed a bug with className options for buttons
html.table: fixed a bug with className options for buttons

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
/*	Script: Autocompleter.js
		3rd party script for managing autocomplete functionality.
		
		Details:
		Author - Harald Kirschner <mail [at] digitarald.de>
		Refactored by - Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		License - MIT-style license
		Version - 1.0rc3
		Source - http://digitarald.de/project/autocompleter/
		
		Dependencies:
		Mootools 1.1 - <Class.Extras>, <Element.Event>, <Element.Selectors>, <Element.Form>, <Element.Dimensions>, <Fx.Style>, <Ajax>, <Json>
		Autocompleter - <Observer.js>
		
		Namespace: Autocompleter
		All functions are part of the <Autocompleter> namespace.
	*/
var Autocompleter = {};
/*	Class: Autocompleter.Base
		Base class for the Autocompleter classes.
		
		Arguments:
		el - (DOM element or id) element to observe.
		options - (object) key/value set of options.
		
		Options:
		minLength - (integer, default 1) Minimum length to start auto compliter
		useSelection - (boolean, default true) Select completed text part (works only for appended strings)
		markQuery - (boolean, default true) Mark queried string with <span class="autocompleter-queried">*</span>
		inheritWidth - (boolean, default true) Inherit width for the autocompleter overlay from the input field
		maxChoices - (integer, default 10). Maximum of suggested fields.
		injectChoice - (function, optional). Callback for injecting the list element with the arguments (itemValue, itemIndex), take a look at updateChoices for default behaviour.
		onSelect - Event Function. Fires when when an item gets selected; passed the input and the value selected.
		onShow - Event Function. Fires when autocompleter list shows.
		onHide - Event Function. Fires when autocompleter list hides.
		customTarget - (element, optional). Allows to override the autocompleter list element with your own list element.
		className - (string, default 'autocompleter-choices'). Class name for the list element.
		zIndex - (integer, default 42). z-Index for the list element.
		observerOptions - optional Options Object. For the Observer class.
		fxOptions - optional Options Object. For the Fx.Style on the list element.
		allowMulti - (boolean, defaults to false) allow more than one value, seperated by a delimeter
		delimeter - (string) default delimter between multi values (defaults to ", ")
		autotrim - (boolean) trim the delimeter on blur
		allowDupes - (boolean, defaults to false) if multi, prevent duplicate entries
		baseHref - (string) the base url where the css and image assets are located (defaults to cnet's servers you should change)

		Note:
		If you're not cnet, you should download these assets to your own local:
		http://www.cnet.com/html/rb/assets/global/autocompleter/Autocompleter.css
		http://www.cnet.com/html/rb/assets/global/autocompleter/spinner.gif
		
		Then either change this script or pass in the local when you instantiate the class.
		
		Example:
		This base class is not used directly (but rather its inheritants are such as <Autocompleter.Ajax>)
		so there is no example here.
	*/
Autocompleter.Base = new Class({

	options: {
		minLength: 1,
		useSelection: true,
		markQuery: true,
		inheritWidth: true,
		dropDownWidth: 100,
		maxChoices: 10,
		injectChoice: null,
		onSelect: Class.empty,
		onShow: Class.empty,
		onHide: Class.empty,
		customTarget: null,
		className: 'autocompleter-choices',
		zIndex: 42,
		observerOptions: {},
		fxOptions: {},
		multi: false,
		delimeter: ', ',
		autotrim: true,
		allowDupes: false,
		/*	if you're not cnet, you should download these assets to your own local:
				http://www.cnet.com/html/rb/assets/global/autocompleter/Autocompleter.css
				http://www.cnet.com/html/rb/assets/global/autocompleter/spinner.gif
			*/
		baseHref: 'http://www.cnet.com/html/rb/assets/global/autocompleter/'
	},

	initialize: function(el, options) {
		this.setOptions(options);
		if(!$('AutocompleterCss')) window.addEvent('domready', function(){
			new Asset.css(this.options.baseHref+'Autocompleter.css', {id: 'AutocompleterCss'});
		}.bind(this));
		this.element = $(el);
		this.build();
		this.observer = new Observer(this.element, this.prefetch.bind(this), $merge({
			delay: 400
		}, this.options.observerOptions));
		this.value = this.observer.value;
		this.queryValue = null;
		this.element.addEvent('blur', function(e){
			this.autoTrim.delay(50, this, e);
		}.bind(this));
		this.addEvent('onSelect', function(){
			this.element.focus();
			this.userChose = true;
			(function(){
				this.userChose = false;
			}).delay(100, this);
		}.bind(this));
	},

/*	Property: build
		Builds the html structure for choices and appends the events to the element.
		Override this function to modify the html generation.	*/

	build: function() {
		if ($(this.options.customTarget)) this.choices = this.options.customTarget;
		else {
			this.choices = new Element('ul', {
				'class': this.options.className,
				'styles': {zIndex: this.options.zIndex}
				}).injectInside(document.body);
			this.fix = new OverlayFix(this.choices);
		}
		this.fx = this.choices.effect('opacity', $merge({wait: false, duration: 200}, this.options.fxOptions))
			.addEvent('onStart', function() {
				if (this.fx.now) return;
				this.choices.setStyle('display', '');
				this.fix.show();
			}.bind(this))
			.addEvent('onComplete', function() {
				if (this.fx.now) return;
				this.choices.setStyle('display', 'none');
				this.fix.hide();
			}.bind(this)).set(0);
		this.element.setProperty('autocomplete', 'off')
			.addEvent(window.ie ? 'keydown' : 'keypress', this.onCommand.bindWithEvent(this))
			.addEvent('mousedown', this.onCommand.bindWithEvent(this, [true]))
			.addEvent('focus', this.toggleFocus.bind(this, [true]))
			.addEvent('blur', this.toggleFocus.bind(this, [false]))
			.addEvent('trash', this.destroy.bind(this));
	},
	
	autoTrim: function(e){
		if(this.userChose) return this.userChose = false;
		var del = this.options.delimeter;
		var val = this.element.getValue();
		if(this.options.autotrim && val.test(del+"$")){
			e = new Event(e);
			this.observer.value = this.element.value = val.substring(0, val.length-del.length);
		}
		return this.observer.value
	},
/*	Property: getQueryValue
		Returns the user's input to use to match against the full list. When options.multi == true, this value is the last entered string after the last index of the delimeter.
		
		Arguments:
		value - (string) optional, the value to clean; defaults to this.observer.value

		Examples:
(start code)
user input: blue
getQueryValue() returns "blue"

user input: blue, green, yellow
options.multi = true
options.delimter = ", "
getQueryValue() returns "yellow"

user input: blue, green, yellow, 
options.multi = true
options.delimter = ", "
getQueryValue() returns ""
(end)
	*/
	getQueryValue: function(value){
		value = $pick(value, this.observer.value);
		return (this.options.multi)?value.lastElement(this.options.delimeter).toString():value||'';
	},
	
/*	Property: destroy
		Remove the autocomplete functionality from the input.
	*/
	destroy: function() {
		this.choices.remove();
	},

	toggleFocus: function(state) {
		this.focussed = state;
		if (!state) this.hideChoices();
	},

	onCommand: function(e, mouse) {
		var val = this.getQueryValue();
		if (mouse && this.focussed) this.prefetch();
		if (e.key) switch (e.key) {
			case 'enter':
				if (this.selected && this.visible) {
					this.choiceSelect(this.selected);
					e.stop();
				} return;
			case 'up': case 'down':
				if (this.getQueryValue() != (val || this.queryValue)) this.prefetch();
				else if (this.queryValue === null) break;
				else if (!this.visible) this.showChoices();
				else {
					this.choiceOver((e.key == 'up')
						? this.selected.getPrevious() || this.choices.getLast()
						: this.selected.getNext() || this.choices.getFirst() );
					this.setSelection();
				}
				e.stop(); return;
			case 'esc': case 'tab': 
				this.hideChoices(); 
				if (this.options.multi) this.element.value = this.element.getValue().trimLastElement();
				return;
		}
		this.value = false;
	},

	setSelection: function() {
		if (!this.options.useSelection) return;
		var del = this.options.delimeter;
		var qVal = this.getQueryValue(this.queryValue);
		var elVal = this.getQueryValue(this.element.getValue());
		var startLength;
		if(this.options.multi)	{
			var index = this.queryValue.lastIndexOf(del);
			var delLength = (index<0)?0:del.length;
			startLength = qVal.length+(index<0?0:index)+delLength;
		} else startLength = qVal.length;

		if (elVal.indexOf(qVal) != 0) return;
		var insert = this.selected.inputValue.substr(startLength);
		if (window.ie) {
			var sel = document.selection.createRange();
			sel.text = insert;
			sel.move("character", - insert.length);
			sel.findText(insert);
			sel.select();
		} else {
			var offset = (this.options.multi && this.element.value.test(del))?
				this.element.getValue().length-elVal.length+qVal.length
				:this.queryValue.length;
			this.element.value = this.element.value.substring(0, offset) + insert;
			this.element.selectionStart = offset;
			this.element.selectionEnd = this.element.value.length;
		}
		this.value = this.observer.value = this.element.value;
	},
/*	Property: hideChoices
		Hides the choices from the user.
	*/
	hideChoices: function() {
		if (!this.visible) return;
		this.visible = this.value = false;
		this.observer.clear();
		this.fx.start(0);
		this.fireEvent('onHide', [this.element, this.choices]);
	},

/*	Property: showChoices
		Shows the choices to the user.
	*/
	showChoices: function() {
		if (this.visible || !this.choices.getFirst()) return;
		this.visible = true;
		var pos = this.element.getCoordinates(this.options.overflown);
		this.choices.setStyles({'left': pos.left, 'top': pos.bottom});
		this.choices.setStyle('width', (this.options.inheritWidth)?pos.width:this.options.dropDownWidth);
		this.fx.start(1);
		this.choiceOver(this.choices.getFirst());
		this.fireEvent('onShow', [this.element, this.choices]);
	},

	prefetch: function() {
		var val = this.getQueryValue(this.element.getValue());
		if (val.length < this.options.minLength) this.hideChoices();
		else if (val == this.queryValue) this.showChoices();
		else this.query();
	},

	updateChoices: function(choices) {
		this.choices.empty();
		this.selected = null;
		if (!choices || !choices.length) return;
		if (this.options.maxChoices < choices.length) choices.length = this.options.maxChoices;
		choices.each(this.options.injectChoice || function(choice, i){
			var el = new Element('li').setHTML(this.markQueryValue(choice));
			el.inputValue = choice;
			this.addChoiceEvents(el).injectInside(this.choices);
		}, this);
		this.showChoices();
	},

	choiceOver: function(el) {
		if (this.selected) this.selected.removeClass('autocompleter-selected');
		this.selected = el.addClass('autocompleter-selected');
	},

	choiceSelect: function(el) {
		if(this.options.multi) {
			var del = this.options.delimeter;
			var value = (this.element.value.trimLastElement(del) + el.inputValue).split(del);
			var fin = [];
			if (!this.options.allowDupes) {
				value.each(function(item){
					if(fin.contains(item))fin.remove(item); //move it to the end
					fin.include(item);
				})
			} else fin = value;
			this.observer.value = this.element.value = fin.join(del)+del;
		} else this.observer.value = this.element.value = el.inputValue;
		
		
		this.hideChoices();
		this.fireEvent('onSelect', [this.element, el.inputValue], 20);
	},

/*	Property: markQueryValue
		Marks the queried word in the given string with <span class="autocompleter-queried">*</span>
		Call this i.e. from your custom parseChoices, same for addChoiceEvents
		
		Arguments:
		txt - (string) the string to mark
	 */
	markQueryValue: function(txt) {
		var val = (this.options.mult)?this.lastQueryElementValue:this.queryValue;
		return (this.options.markQuery && val) ? txt.replace(new RegExp('^(' + val.escapeRegExp() + ')', 'i'), '<span class="autocompleter-queried">$1</span>') : txt;
	},

/*	Property: addChoiceEvents
		Appends the needed event handlers for a choice-entry to the given element.
		
		Arguments:
		el - (DOM element or id) the element to add
*/
	addChoiceEvents: function(el) {
		return el.addEvents({
			'mouseover': this.choiceOver.bind(this, [el]),
			'mousedown': this.choiceSelect.bind(this, [el])
		});
	},
	query: Class.empty
});

Autocompleter.Base.implement(new Events);
Autocompleter.Base.implement(new Options);

/*	Class: OverlayFix
		Private class used by <Autocompleter> - basically an <IframeShim>.
	*/
var OverlayFix = new Class({

	initialize: function(el) {
		this.element = $(el);
		if (window.ie){
			this.element.addEvent('trash', this.destroy.bind(this));
			this.fix = new Element('iframe', {
					'properties': {'frameborder': '0', 'scrolling': 'no', 'src': 'javascript:false;'},
					'styles': {'position': 'absolute', 'border': 'none', 'display': 'none', 'filter': 'progid:DXImageTransform.Microsoft.Alpha(opacity=0)'}})
				.injectAfter(this.element);
		}
	},

	show: function() {
		if (this.fix) this.fix.setStyles($extend(
			this.element.getCoordinates(), {'display': '', 'zIndex': (this.element.getStyle('zIndex') || 1) - 1}));
		return this;
	},

	hide: function() {
		if (this.fix) this.fix.setStyle('display', 'none');
		return this;
	},

	destroy: function() {
		this.fix.remove();
	}

});

String.extend({
	lastElement: function(separator){
		separator = separator || ' ';
		var txt = this; //(separator.test(' $'))?this:this.trim();
		var index = txt.lastIndexOf(separator);
		var result = (index == -1)? txt: txt.substr(index + separator.length, txt.length);
		return result;
	},
 
 
	trimLastElement: function(separator){
		separator = separator || ' ';
		var txt = this; //(separator.test(' $'))?this:this.trim();
		var index = this.lastIndexOf(separator);
		return (index == -1)? "": txt.substr(0, index + separator.length);
	}
});

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/3rdParty/Autocomplete/Autocompleter.js,v $
$Log: Autocompleter.js,v $
Revision 1.6  2007/10/29 18:28:57  newtona
fixed a bug in autocompleter, see: http://forum.mootools.net/viewtopic.php?pid=31481#p31481

Revision 1.5  2007/09/05 18:36:58  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.4  2007/08/15 01:03:30  newtona
Added more event info for Autocompleter.js
Slimbox no longer adds css to the page if there aren't any images found for the instance
Iframeshim now exits quietly if you try and position it before the dom is ready
jsonp now handles having more than one request open at a time
removed a console.log statement from window.cnet.js (shame on me for leaving it there)

Revision 1.3  2007/06/12 20:26:52  newtona
*** empty log message ***

Revision 1.2  2007/06/07 18:43:35  newtona
added CSS to autocompleter.js
removed string.cnet.js dependencies from template parser and stickyWin.default.layout.js

Revision 1.1  2007/06/02 01:35:17  newtona
*** empty log message ***


*/
/*	Script: Autocompleter.JsonP.js
		Implements <JsonP> support for the <Autocompleter> class.
		
		Dependencies:
		Mootools 1.1 - <Class.Extras>, <Element.Event>, <Element.Selectors>, <Element.Form>, <Element.Dimensions>, <Fx.Style>, <Ajax>, <Json>
		Autocompleter - <Autocompleter.js>, <Observer.js>
		CNET - <jsonp.js>
		
		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)
		
		Class: Autocompleter.JsonP
		Implements <JsonP> support for the <Autocompleter> class.
		
		Arguments:
		el - (DOM element or id) element to observe.
		url - (string) the url to query for values
		options - (object) key/value set of options.

		Options:
		postVar - (string) the key to which the user's entry is mapped - passed to the server as postVar=userEntry (see example below)
		jsonpOptions - (object) options passed along to the <JsonP> class.
		onRequest - (callback function) fired when the request is sent
		onComplete - (callback function) fired when the request is complete
		minLength - (integer) Overrides minLength (defaults to 1).
		filterResponse - Function, optional. Allows to override default filterResponse method
		
		Example:
		Let's say the user is typing into an input to search for ipods and you need to take what they've typed ("ipo" so far) and send it to a server to get back filtered results like so:

http://server.com/handler.jsp?query=ipo
		
		Then the postVar option would be "query" so that the user's input is mapped to this key in the query string.
		
(start code)
var myCompleter = new Autocomplete.JsonP($('myinput'), 'http://server.com/handler.jsp', {
	postVar: 'query'
	...
});
(end)
	
		You're not really done though, because you need to handle the results that come back using the functionality in the base <Autocompleter> class. Here's an example that will work with the cnet API:

(start code)
new Autocompleter.JsonP($('jsonp'), 'http://api.cnet.com/restApi/v1.0/techProductSearch',
{
	jsonpOptions: {
		//this data gets added to the query string using JsonP's options
		data: {
			viewType: 'json',
			partKey: '19926949750937665684988687810562', //this is my code, user your own!
			iod:'none',
			start:0,
			results:10
		}
	},
	//require at least a key stroke from the user
	minLength: 1,
	//this function filters the results based on the input
	filterResponse: function(resp) {
		//test it
		if(!choices || choices.length == 0) return [];
		//filter it and return it
		var regex = new RegExp('^' + (this.queryValue || '').escapeRegExp(), 'i');
		return choices.filter(function(choice){
			return (regex.test(choice.Name.$) || regex.test(choice['@id']));
		});
	},
	useSelection: false,
	//because the data returned has a unique structure, we must manage the parsing ourselves
	filterResponse: function(resp) {
		try {
			//this structure is unique to the CNET API
			choices = resp.CNETResponse.TechProducts.TechProduct;
			//test it
			if(!choices || choices.length == 0) return [];
			//filter it and return it
			return choices.filter(function(choice){
				return (choice.Name.$.test(this.getQueryValue(), 'i') || choice['@id'].test(this.getQueryValue()), 'i');
			}.bind(this));
		} catch(e){'filterResponse error: ', dbug.log(e)}
	},
	injectChoice: function(choice) {
		//again, the structure of these items is unique to the CNET API
		if(! choice.Name.$)return;
		var el = new Element('li')
			.setHTML(this.markQueryValue(choice.Name.$))
			.adopt(new Element('span', {'class': 'example-info'}).setHTML(this.markQueryValue(choice['@id'])));
		el.inputValue = choice.Name.$+' ('+choice['@id']+')';
		this.addChoiceEvents(el).injectInside(this.choices);
	}
});
(end)
	*/

Autocompleter.JsonP = Autocompleter.Base.extend({

	options: {
		postVar: 'query',
		jsonpOptions: {},
		onRequest: Class.empty,
		onComplete: Class.empty,
		minLength: 1, 
		filterResponse: null
	},

	initialize: function(el, url, options) {
		this.url = url;
		this.parent(el, options);
		if (this.options.filterResponse) this.filterResponse = this.options.filterResponse.bind(this);
	},

	query: function(){
		var multi = this.options.multi;
		var data = $extend({}, this.options.jsonpOptions.data);
		if(multi) this.lastQueryElementValue = this.element.value.lastElement(this.options.delimeter);
		data[this.options.postVar] = (multi)?this.lastQueryElementValue:this.element.value;

		this.jsonp = new JsonP(this.url, $merge(
			{
				data: data
			},
			this.options.jsonpOptions
		));
		this.jsonp.addEvent('onComplete', this.queryResponse.bind(this));

		this.fireEvent('onRequest', [this.element, this.jsonp]);
		this.jsonp.request();
	},
	
/*	Property: queryResponse
		Inherated classes have to extend this function and use this.parent(resp)
		
		Arguments:
		resp - (String) the response from the ajax query.
*/
	queryResponse: function(resp) {
		try {
			this.value = this.queryValue = this.element.value;
			var choices = this.filterResponse(resp);
			this.selected = false;
			this.hideChoices();
		} catch(e) {
			try { dbug.log('jsonp request error: ', e); } catch(e) {}
		}
		this.fireEvent(choices ? 'onComplete' : 'onFailure', [this.element, choices], 20);
		if (!choices || !choices.length) return;
		this.updateChoices(choices);
	},

	filterResponse: function(resp) {
		var regex = new RegExp('^' + this.queryValue.escapeRegExp(), 'i');
		return this.tokens.filter(function(token) {
			return regex.test(token);
		});
	}

});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/3rdParty/Autocomplete/Autocompleter.JsonP.js,v $
$Log: Autocompleter.JsonP.js,v $
Revision 1.1  2007/06/12 20:26:52  newtona
*** empty log message ***


*/
/*	Script: Autocompleter.Local.js
		Extends the <Autocompleter.Base> class to add support for a pre-defined object.
		
		Class: Autocompleter.Local
		Extends the <Autocompleter.Base> class to add support for a pre-defined object.
		
		Arguments:
		el - (DOM element or id) element to observe.
		tokens - (Array) an array of values
		options - (object) key/value set of options.
		
		Options:
		All values passed to <Autocompleter.Base>
		
		minLength - Overrides minLength to 0.
		filterTokens - Function, optional. Allows to override default filterTokens method

		Example:
(start code)
//this object's structure is arbitrary
var tokens = [
	['Apple', 'Red'],
	['Lemon', 'Yellow'],
	['Grape', 'Purple']	
];

new Autocompleter.Local($('myInput'), tokens, {
	delay: 100,
	//this is a custom filter because our object has a unique structure
	filterTokens: function() {
		var regex = new RegExp('^' + (this.queryValue || '').escapeRegExp(), 'i');
		var filtered = this.tokens.filter(function(token){
			return (regex.test(token[0]) || regex.test(token[1]));
		});
		return filtered;
	},
	//again, because our data structure is unique, we must handle the results ourselves
	injectChoice: function(choice) {
		var el = new Element('li')
			.setHTML(this.markQueryValue(choice[0]))
			.adopt(new Element('span', {'class': 'example-info'}).setHTML(this.markQueryValue(choice[1])));
		el.inputValue = choice[0];
		this.addChoiceEvents(el).injectInside(this.choices);
	}
});
(end)
	*/

Autocompleter.Local = Autocompleter.Base.extend({
	options: {
		minLength: 0,
		filterTokens : null
	},
	initialize: function(el, tokens, options) {
		this.parent(el, options);
		this.tokens = tokens;
		if (this.options.filterTokens) this.filterTokens = this.options.filterTokens.bind(this);
	},
	query: function() {
		this.hideChoices();
		this.queryValue = (this.options.multi)?
				this.element.value.lastElement(this.options.delimeter).trim()
				:this.element.value;
		this.updateChoices(this.filterTokens());
	},
	filterTokens: function(token) {
		var regex = new RegExp('^' + this.queryValue.escapeRegExp(), 'i');
		return this.tokens.filter(function(token) {
			return regex.test(token);
		});
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/3rdParty/Autocomplete/Autocompleter.Local.js,v $
$Log: Autocompleter.Local.js,v $
Revision 1.1  2007/06/12 20:26:52  newtona
*** empty log message ***


*/
/*	Script: Autocompleter.Remote.js
		Contains the classes for the Remote methods for <Autocompleter>.

		Namespace: Autocompleter.Ajax
		Contains the classes for the Remote methods for <Autocompleter>
		
		Details:
		Author - Harald Kirschner <mail [at] digitarald.de>
		Refactored by - Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		License - MIT-style license
		Version - 1.0rc3
		Source - http://digitarald.de/project/autocompleter/
		
		Dependencies:
		Mootools 1.1 - <Class.Extras>, <Element.Event>, <Element.Selectors>, <Element.Form>, <Element.Dimensions>, <Fx.Style>, <Ajax>, <Json>
		Autocompleter - <Autocompleter.js>, <Observer.js>
	*/
Autocompleter.Ajax = {};
/*	Class: Autocompleter.Ajax.Base
		The base functionality for all <Autocompleter.Ajax> functionality.
		
		Arguments:
		el - (DOM element or id) element to observe.
		url - (string) the url to query for values
		options - (object) key/value set of options.
		
		Options:
		postVar - String, default ‘value’. Post variable name for the query string
		postData - Object, optional. Additional request data
		ajaxOptions - optional Options Object. For the Ajax instance
		onRequest - Event Function.
		onComplete - Event Function.
		
		Example:
		The <Autocompleter.Ajax.Base> class is not used directly but rather its inhertants are (see 
		<Autocompleter.Ajax.Json> and <Autocompleter.Ajax.Xhtml>) so there is no example here.
	*/
Autocompleter.Ajax.Base = Autocompleter.Base.extend({

	options: {
		postVar: 'value',
		postData: {},
		ajaxOptions: {},
		onRequest: Class.empty,
		onComplete: Class.empty
	},

	initialize: function(el, url, options) {
		this.parent(el, options);
		this.ajax = new Ajax(url, $merge({
			autoCancel: true
		}, this.options.ajaxOptions));
		this.ajax.addEvent('onComplete', this.queryResponse.bind(this));
		this.ajax.addEvent('onFailure', this.queryResponse.bind(this, [false]));
	},

	query: function(){
		var multi = this.options.multi;
		var data = $extend({}, this.options.postData);
		if(multi) this.lastQueryElementValue = this.element.value.lastElement(this.options.delimeter);
		data[this.options.postVar] = (multi)?this.lastQueryElementValue:this.element.value;
		this.fireEvent('onRequest', [this.element, this.ajax]);
		this.ajax.request(data);
	},
	
/*	Property: queryResponse
		Inherated classes have to extend this function and use this.parent(resp)
		
		Arguments:
		resp - (String) the response from the ajax query.
*/
	queryResponse: function(resp) {
		this.value = this.queryValue = this.element.value;
		this.selected = false;
		this.hideChoices();
		this.fireEvent(resp ? 'onComplete' : 'onFailure', [this.element, this.ajax], 20);
	}

});

/*	Class: Autocompleter.Ajax.Json
		Extends <Autocompleter.Ajax.Base> to include Json support.
		
		Arguments:
		All those specified in <Autocompleter.Ajax.Base> and <Autocompleter.Base>.
		
		Example:
new Autocompleter.Ajax.Json($('ajaxJson'), 'server/auto.php' {
	postVar: 'query'
});
	*/
Autocompleter.Ajax.Json = Autocompleter.Ajax.Base.extend({

	queryResponse: function(resp) {
		this.parent(resp);
		var choices = Json.evaluate(resp || false);
		if (!choices || !choices.length) return;
		this.updateChoices(choices);
	}

});

/*	Class: Autocompleter.Ajax.Xhtml
		Extends <Autocompleter.Ajax.Base> to include Xhtml support.

		Arguments:
		All those specified in <Autocompleter.Ajax.Base> and <Autocompleter.Base>.

		Example:		
(start code)
new Autocompleter.Ajax.Xhtml($('ajaxXhtml'), 'server/auto.php', {
	postData: {html: 1}, //some data to go along with the request
	//handle the data returned
	parseChoices: function(el) {
		var value = el.getFirst().innerHTML;
		el.inputValue = value;
		this.addChoiceEvents(el).getFirst().setHTML(this.markQueryValue(value));
	}
});
(end)
	*/
Autocompleter.Ajax.Xhtml = Autocompleter.Ajax.Base.extend({

	options: {
		parseChoices: null
	},

	queryResponse: function(resp) {
		this.parent(resp);
		if (!resp) return;
		this.choices.setHTML(resp).getChildren().each(this.options.parseChoices || this.parseChoices, this);
		this.showChoices();
	},

	parseChoices: function(el) {
		var value = el.innerHTML;
		el.inputValue = value;
		el.setHTML(this.markQueryValue(value));
	}

});

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/3rdParty/Autocomplete/Autocompleter.Remote.js,v $
$Log: Autocompleter.Remote.js,v $
Revision 1.1  2007/06/12 20:26:52  newtona
*** empty log message ***


*/
/*	Script: Observer.js
		Observes formelements for changes; part of the <Autocomplete> 3rd party package.
		
		Details:
		Author - Harald Kirschner <mail [at] digitarald.de>
		License - MIT-style license
		Version - 1.0rc1
		Source - http://digitarald.de/project/autocompleter/
		
		Dependencies:
		Mootools 1.1 - <Class>, <Class.Extras>, <Element.Event>
		
		Class: Observer
		Observes form elements for changes; part of the <Autocomplete> 3rd party package.
		
		Arguments:
		el - (DOM element or id) element to observe
		onFired - (function) event to execute periodically and/or on keyup
		options - (object) a set of key/value options
		
		Options:
		periodical - (boolean) update and fire the onFired event regularly; defaults to false
		delay - (integer) how often to update using periodical if (periodical is true); defaults to 1000
	*/
var Observer = new Class({

	options: {
		periodical: false,
		delay: 1000
	},

	initialize: function(el, onFired, options){
		this.setOptions(options);
		this.addEvent('onFired', onFired);
		this.element = $(el);
		this.listener = this.fired.bind(this);
		this.value = this.element.getValue();
		if (this.options.periodical) this.timer = this.listener.periodical(this.options.periodical);
		else this.element.addEvent('keyup', this.listener);
	},

	fired: function() {
		var value = this.element.getValue();
		if (this.value == value) return;
		this.clear();
		this.value = value;
		this.timeout = this.fireEvent.delay(this.options.delay, this, ['onFired', [value]]);
	},

	clear: function() {
		$clear(this.timeout);
		return this;
	}
});

Observer.implement(new Options);
Observer.implement(new Events);

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/3rdParty/Autocomplete/Observer.js,v $
$Log: Observer.js,v $
Revision 1.2  2007/06/12 20:26:52  newtona
*** empty log message ***

Revision 1.1  2007/06/02 01:35:17  newtona
*** empty log message ***


*/
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


*//*	Script: simple.editor.js
		A simple html editor for wrapping text with links and whatnot.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>
		CNET - <Element.forms.js>, <Element.shortcuts.js>
		Optional - <Trinket.Base.js>, <Trinket.contexts.js>, <Trinket.LinkBuilder.js>, <StickyWinModal>, <stickyWinHTML>

		Class: SimpleEditor
		A simple html editor for wrapping text with links and whatnot.
		
		Arguments:
		input - (DOM element or id) the input this editor modifies
		buttons - (css selector or <Elements> collection) all the links 
							and/or buttons/images that make changes to the text when clicked.
		commands - (optional, object) a commands object (see below) for this editor.
		
		Commands:
		<SimpleEditor> comes with a handful of common commands to wrap text with bold tags or italics, etc. You can define your own and add them to all SimpleEditors or to a specific instance.

		A command id made up of a shortcut key and a function that is passed the input.
		
		Example:
(start code)
bold: {
	shortcut: 'b',
	command: function(input){
		input.insertAroundCursor({before:'<b>',after:'</b>'});
	}
}
(end)

		When the user clicks the button or hits ctrl+b, the tag will be inserted around the selected text.
		
		See <SimpleEditor.addCommand> and <SimpleEditor.addCommands> on how to add your own.
		
		Buttons/Links:
		The buttons passed in must have a property "rel" equal to the key of the command they execute.
		
		Example:
(start code)
<img src="bold.gif" alt="Bold (ctrl+b)" title="Bold (ctrl+b)" rel="bold">
(end)
		
		In the example above, the rel="bold" will map this image to the bold command.
	*/
var SimpleEditor = new Class({
	initialize: function(input, buttons, commands){
		this.commands = new Hash($merge(SimpleEditor.commands, commands||{}));
		this.input = $(input);
		this.buttons = $$(buttons);
		this.buttons.each(function(button){
			button.addEvent('click', function() {
				this.exec(button.getProperty('rel'));
			}.bind(this));
		}.bind(this));
		this.input.addEvent('keydown', function(e){
			e = new Event(e);
			if (e.control) {
				var key = this.shortCutToKey(e.key);
				if(key) {
					e.stop();
					this.exec(key);
				}
			}
		}.bind(this));
	},
	shortCutToKey: function(shortcut){
		var returnKey = false;
		this.commands.each(function(value, key){
			if(value.shortcut == shortcut) returnKey = key;
		});
		return returnKey;
	},
/*	Property: addCommand
		Inserts a single command to the SimpleEditor.
		
		*Note*: You can use this method on your instance of this class to add the command to that instance, or you can execute it on the class namespace and all <SimpleEditor> instances created after this will get these commands.

		Arguments:
		key - (string) the unique identifier for this command ("bold", "italics", etc.)
		command - (function) funciton to execute on the input; the function is passed the input as an argument
		shortcut - (character, optional) a shortcut key that, when pressed in conjunction with ctrl, will execute
								the function

		Example:
(start code)
//all instances will get bold tags as <strong></strong>
SimpleEditor.addCommand('bold', function(input) {
	input.insertAroundCursor({before:'<strong>',after:'</strong>'});
}, 'b')

//but this instance will get bold tags as <b></b>
var myEditor = new SimpleEditor(input, $$('img.editbuttons'));
myEditor.addCommand('bold', function(input){
	input.insertAroundCursor({before:'<b>',after:'</b>'});
}, 'b');
(end)
	*/
	addCommand: function(key, command, shortcut){
		this.commands.set(key, {
			command: command,
			shortcut: shortcut
		});
	},

/*	Property: addCommand
		Inserts a collection of commands to the SimpleEditor.
		
		*Note*: You can use this method on your instance of this class to add the command to that instance, or you can execute it on the class namespace and all <SimpleEditor> instances created after this will get these commands.

		Arguments:
		commands - (object) a key/value set of commands (see below)
		
		Commands:
		This is an object whose key is the command key. Its members are key/values for the shortcut value and the command function. The example below should illustrate this more clearly.

		Example:
(start code)
//all instances will get bold tags as <strong></strong> and italics as <em></em>
SimpleEditor.addCommands(SimpleEditor.addCommands({
	bold: {
		shortcut: 'b',
		command: function(input){
			input.insertAroundCursor({before:'<strong>',after:'</strong>'});
		}
	},
	italics: {
		shortcut: 'i',
		command: function(input){
			input.insertAroundCursor({before:'<em>',after:'</em>'});
		}
	}
));

//but this instance will get bold tags as <b></b> and italics as <i></i>
var myEditor = new SimpleEditor(input, $$('img.editbuttons'));
myEditor.addCommands(SimpleEditor.addCommands({
	bold: {
		shortcut: 'b',
		command: function(input){
			input.insertAroundCursor({before:'<b>',after:'</b>'});
		}
	},
	italics: {
		shortcut: 'i',
		command: function(input){
			input.insertAroundCursor({before:'<i>',after:'</i>'});
		}
	}
});
(end)
	*/
	addCommands: function(commands){
		this.commands.extend(commands);
	},
	exec: function(key){
		var currentScrollPos; 
		if (this.input.scrollTop || this.input.scrollLeft) {
			currentScrollPos = {
				scrollTop: this.input.scrollTop,
				scrollLeft: this.input.scrollLeft
			};
		}
		if(this.commands.hasKey(key)) this.commands.get(key).command(this.input);
		if(currentScrollPos) {
			this.input.scrollTop = currentScrollPos.scrollTop;
			this.input.scrollLeft = currentScrollPos.scrollLeft;
		}
	}
});
$extend(SimpleEditor, {
	commands: {},
	addCommand: function(key, command, shortcut){
		SimpleEditor.commands[key] = {
			command: command,
			shortcut: shortcut
		}
	},
	addCommands: function(commands){
		$extend(SimpleEditor.commands, commands);
	}
});
/*	Default commands:	*/
SimpleEditor.addCommands({
	/*	bold - <b></b>	*/
	bold: {
		shortcut: 'b',
		command: function(input){
			input.insertAroundCursor({before:'<b>',after:'</b>'});
		}
	},
	/*	underline - <u></u>	*/
	underline: {
		shortcut: 'u',
		command: function(input){
			input.insertAroundCursor({before:'<u>',after:'</u>'});
		}
	},
	/*	anchor - uses <Trinket.LinkBuilder> if present	*/
	anchor: {
		shortcut: 'l',
		command: function(input){
			function simpleLinker(){
				if(window.TagMaker){
					if(!this.linkBuilder) this.linkBuilder = new TagMaker.anchor();
					this.linkBuilder.prompt(input);
				} else {
					var href = window.prompt('The URL for the link');
					var opts = {before: '<a href="'+href+'">', after:'</a>'};
					if (!input.getSelectedText()) opts.defaultMiddle = window.prompt('The link text');
					input.insertAroundCursor(opts);
				}
			}
			try {
				if(Trinket) {
					if(!this.linkBulder){
						var lb = Trinket.available.filter(function(trinket){
							return trinket.name == 'Link Builder';
						});
						this.linkBuilder = (lb.length)?lb[0]:new Trinket.LinkBuilder({
							context: 'default'
						});
						this.linkBuilder.clickPrompt(input);
					}
				} else simpleLinker();
			} catch(e){ simpleLinker(); }
		}
	},
	/*	copy - if <Clipboard.js> is present	*/
	copy: {
		shortcut: false,
		command: function(input){
			if(Clipboard) Clipboard.copyFromElement(input);
			else simpleErrorPopup('Woops', 'Sorry, this function doesn\'t work here; use ctrl+c.');
			input.focus();
		}
	},
	/*	cut - if <Clipboard.js> is present	*/
	cut: {
		shortcut: false,
		command: function(input){
			if(Clipboard) {
				Clipboard.copyFromElement(input);
				input.insertAtCursor('');
			} else simpleErrorPopup('Woops', 'Sorry, this function doesn\'t work here; use ctrl+x.');
		}
	},
	/*	hr - <hr/>	*/
	hr: {
		shortcut: '-',
		command: function(input){
			input.insertAtCursor('\n<hr/>\n');
		}
	},
	/*	img - <img src="">	*/
	img: {
		shortcut: 'g',
		command: function(input){
			if(window.TagMaker) {
				if(!this.anchorBuilder) this.anchorBuilder = new TagMaker.image();
				this.anchorBuilder.prompt(input);
			} else {
				input.insertAtCursor('<img src="'+window.prompt('The url to the image')+'" />');
			}
		}
	},
	/*	stripTags - removes all tags from the selection	*/
	stripTags: {
		shortcut: '\\',
		command: function(input){
			input.insertAtCursor(input.getSelectedText().stripTags());
		}
	},
	/*	supertext - <sup></sup>	*/
	sup: {
		shortcut: false,
		command: function(input){
			input.insertAroundCursor({before:'<sup>', after: '</sup>'});
		}
	},
	/*	subtext - <sub></sub>	*/
	sub: {
		shortcut: false,
		command: function(input){
			input.insertAroundCursor({before:'<sub>', after: '</sub>'});
		}
	},
	/*	paragraph - <p></p>	*/
	paragraph: {
		shortcut: 'enter',
		command: function(input){
			input.insertAroundCursor({before:'\n<p>\n', after: '\n</p>\n'});
		}
	},
	/*	strike - <strike></strike>	*/
	strike: {
		shortcut: 'k',
		command: function(input){
			input.insertAroundCursor({before:'<strike>',after:'</strike>'});
		}
	},
	/*	italics - <i></i>	*/
	italics: {
		shortcut: 'i',
		command: function(input){
			input.insertAroundCursor({before:'<i>',after:'</i>'});
		}
	},
	/*	bullets - <ul><li></li></ul>	*/
	bullets: {
		shortcut: '8',
		command: function(input){
			input.insertAroundCursor({before:'<ul>\n	<li>',after:'</li>\n</ul>'});
		}
	},
	/*	numberList - <ol><li></li></ol>	*/
	numberList: {
		shortcut: '=',
		command: function(input){
			input.insertAroundCursor({before:'<ol>\n	<li>',after:'</li>\n</ol>'});
		}
	},
	/*	clean - removes non-asci MSword style characters with <Element.tidy>	*/
	clean: {
		shortcut: false,
		command: function(input){
			input.tidy();
		}
	},
	/*	preview - uses <StickyWinModal>	to display a preview */
	preview: {
		shortcut: false,
		command: function(input){
			try {
				if(!this.container){
					this.container = new Element('div', {
						styles: {
							border: '1px solid black',
							padding: 8,
							height: 300,
							overflow: 'auto'
						}
					});
					this.preview = new StickyWinModal({
						content: stickyWinHTML("preview", this.container, {
							width: 600,
							buttons: [{
								text: 'close',
								onClick: function(){
									this.container.empty();
								}.bind(this)
							}]
						}),
						showNow: false
					});
				}
				this.container.setHTML(input.getValue());
				this.preview.show();
			} catch(e){dbug.log('you need StickyWinModal and stickyWinHTML')}
		}
	}
});
/* do not edit below this line */	 
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/simple.editor.js,v $
$Log: simple.editor.js,v $
Revision 1.6  2007/09/18 18:20:29  newtona
fixing improper input reference in simple editor

Revision 1.5  2007/09/18 00:38:57  newtona
removing trailing comma in simple editor.

Revision 1.4  2007/09/15 00:16:51  newtona
fixing a syntax error

Revision 1.3  2007/09/15 00:07:08  newtona
collission in last check in; merged and re-doing.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
fixed a bug with validate-digits in form validator
new class: TagMaker
implemented TagMaker into simple.editor
updated caption (which is the drag handle for stickyWinHTML) to be the entire width of the caption area
html.table: docs update
tabswapper: docs update

Revision 1.2	2007/09/07 00:50:03	tierneyc
adding scroll position get / set functionality to the simple editor command exec function.

Revision 1.1	2007/06/02 01:35:46	newtona
*** empty log message ***


*//*	Script: CNETAPI.js
		Utility class for the CNET API (http://api.cnet.com) to help with remote retrieval of product data.
		
		Authors:
		Hunter Brown <hunter [dot] brown [at] cnet [dot] com>
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools 1.11 - <Core.js>, <Class.js>, <Class.Extras.js>, <Array.js>, <String.js>, <Function.js>, <Number.js>, <Element.js>,
			<Assets.js>, <Hash.js>
		CNET - <jsonp.js>
		
		Class: CNETAPI.Utils
		Holder for global CNET API partkey for a page (optional).
		
		Arguments: 
		devKey - (integer) your developer key; see http://api.cnet.com.
		url - (string) the url to the api; defaults to http://api.cnet.com/restApi/v1.0; useful for internal development here at CNET.
	*/
var CNETAPI = new Class({
	initialize : function(partKey, url){
		CNETAPI.url = url || CNETAPI.url;
		CNETAPI.partKey = null;
		CNETAPI.partTag = null;
		if(Number(partKey)) CNETAPI.partKey = partKey;
		else CNETAPI.partTag = partKey;
	}
});
CNETAPI.url = 'http://api.cnet.com/restApi/v1.0';
CNETAPI.Utils = {};

/*	Class: CNETAPI.Utils.Base
		Foundation class for all CNETAPIUtil lookup classes.
		
		Arguments:
		options - (object) key/value set of options.
		
		Options:
		jsonpOptions - (object) options object passed to jsonp; defaults to data.viewType = 'json' and data.partKey = CNETAPI.Utils.partKey (see <CNETAPI.Utils>);

		Events:
		onComplete - (function) callback executed whenever a request is complete; passed either an array of data or a string representing an error message.
		onSuccess - (function) callback executed when no error is returned; always passed an array, though it may be empty.
		onError - (function) callback executed when an error is returned; always passed a string (whatever the api service returned).
		
		Note:
		CNETAPI.Utils.Base is extended into specific object classes. For example, <CNETAPI.Utils.TechProduct> lets you look up tech products. The utils classes return <CNETAPI.Object> instances with the same namespace. So for example, <CNETAPI.Utils.TechProduct> will return instances of <CNETAPI.TechProduct>.

		Example:
(start code)
//you have to do this only once on your page
//this is my dev key; get your own!
new CNETAPI(19926949750937665684988687810562);
//now our request:
new CNETAPI.Utils.TechProduct({
  onSuccess: function(items){
    var ol = new Element('ol');
    items.each(function(item){
      ol.adopt(new Element('li').setHTML(item.data.Name));
    });
    $('apiResults').adopt(ol);
  }
}).search("Ipod");
(end)
	*/
CNETAPI.Utils.Base = new Class({
	options: {
		jsonpOptions: {
			data: {
				viewType : 'json'
			}
		},
		onComplete: Class.empty,
		onSuccess: Class.empty,
		onError: Class.empty,
		instantiateResults: false,
		resultClass: null,
		errorPath: 'CNETResponse.Error.ErrorMessage.$'
	},
	//easyToUseObjectBuilder : null,
	//apiObjectBuilder : null,
	//packageResults : null,

	initialize : function(options){
		this.setOptions($merge({
			jsonpOptions: {
				data: {
					partKey: CNETAPI.partKey,
					partTag:  CNETAPI.partTag
				}
			}
		}, options));
	},
	//internal
	//gets the query class; defaults to JSONP
	//url - url to hit for data
	//data - key/value options to pass in the query
	getQuery: function(url, options){
		options.data = options.data||{};
		$each(options.data, function(val, key) { 
			options.data[key] = $type(val)=="string"?unescape(val):val; 
		});
		var j = new JsonP(url||"", options);
		return j;
	},
	//internal
	//attempts to return the title of an object
	//data - the object to inspect for the title
	//key - the key or object to look for the $ property
/*		checkDefined : function (returnObject, data, key){
			if(data[key] && data[key].$) return data[key].$ || "";
			return key.$ || "";
	},	*/
	//internal
	//packages up results into a shallow array of results
	//results - the results from the CNET API
	packer : function(results){
		if($type(results) == "array") results = results.filter(function(result){return result});
		else if (results) results = [results];
		else results = [];
		if(this.options.instantiateResults && this.options.resultClass) {
			return results.map(function(obj){
				return new this.options.resultClass(obj);
			}, this);
		} else {
			return results;
		}
	},
	//internal
	//allows you to get food.fruit.apples.red if you have the string "fruit.apples.red"
	//getMemberByPath(food, "fruit.apples.red")
	getMemberByPath: function(obj, path){
		if (path === "" || path == "top" || !path) return obj;
		var member = obj;
		path.split(".").each(function(p){
			if (p === "") return;
			if (member[p]) member = member[p];
			else member = obj;
		}, this);
		return (member == obj)?false:member;	
	},
	//internal
	//handles returned results from the CNET API
	//obj - the json object returned
	handleApiResults : function(obj, path){
		//deal with server error
		var error = this.getMemberByPath(obj, this.options.errorPath);
		return (error) ? error : this.getMemberByPath(obj, path);
		//if the container is specified
	},
	//internal
	//executes a request to the API service
	//jsonData - object passed to jsonp, merged with the data in this.options.jsonpOptions (view & partner key by default)
	//urlSuffix - suffix added to the api url defined in the options
	//path - path to the desired data in the object returned; ex: CNETResponse.TechProducts.TechProduct
	request: function(jsonData, urlSuffix, path){
		var jsonpOptions = $merge(this.options.jsonpOptions, {
				data : jsonData
		});
		var query = this.getQuery(CNETAPI.url + urlSuffix, jsonpOptions);
		query.addEvent('onComplete',  function(results){
			results = this.handleApiResults(results, path);
			if ($type(results) == "string") {
				dbug.log('CNET API Error: ', results);
				this.fireEvent('onError', [results, query, this]);
			} else {
				this.fireEvent('onSuccess', [this.packer(results), query, this]);
			}
			this.fireEvent('onComplete', [this.packer(results), query, this]);
		}.bind(this));
		query.request();
		return this;
	},
/*	internal
		Throws a javascript error.
		
		Arguments:
		msg - (string) the message for the user
*/
	throwErr: function(msg){
		// Create an object type UserException
		function err (message)
		{
		  this.message=message;
		  this.name="CNETAPI.Utils Exception:";
		};
		
		// Make the exception convert to a pretty string when used as
		// a string (e.g. by the error console)
		err.prototype.toString = function ()
		{
		  return this.name + ': "' + this.message + '"';
		};
		
		// Create an instance of the object type and throw it
		throw new err(msg);
	}
});
CNETAPI.Utils.Base.implement(new Options, new Events);

/*	Class: CNETAPI.Object	
		Base class for all CNET API returned objects. Currently it just cleans 
		up the Objects and allows for inspection with its .type value.
		
		Arguments:
		item - (mixed: object or integer) if integer the class will attempt to get the object from the CNETAPI.Utils.* class.
						If object then the class will use this object as the data (making the assumption that it came from the API).
		options - (object) key/value map of options.
		
		Options:
		extraLookupData - (object) key/value pairs for additional parameters to be sent in API requests (siteId for example)
		type - (string) CNET Type of object. Must related to a CNETAPI.* class (i.e. "TechProduct" = CNETAPI.TechProduct

		Events:
		onSucceess - (function) callback executed whenever results are returned from the CNET API using the .get method.
			passed the instance of the object, the .data value, and the .json value as arguments.
		onError - (function) callback executed when there is an error retrieving data from the API. passed an error message as argument.
		
		Instance Values:
		ready - (boolean) true if there is data present in the class
		json - (object) the raw data passed in or returned by the API
		data - (object) the cleaned data derived from the JSON (no .$, @, etc.)
		
		Note:
		<CNETAPI.Object> is extended into specific object classes. So for example <CNETAPI.TechProduct> is a tech product. You can look up / instanciate individual objects by instanciating them or you can use the corresponding <CNETAPI.Utils.Base> class. So <CNETAPI.Utils.TechProduct> returns instances of <CNETAPI.TechProduct>.

		Example:
(start code)
//you have to do this only once on your page
//this is my dev key; get your own!
new CNETAPI(19926949750937665684988687810562);
//now our request:
new CNETAPI.TechProduct(32069546).chain(function(){
  dbug.log("got the Ipod, here's the data: ", this.data);
  alert(this.data.EditorsRating.$);
});
(end)		
		
*/
CNETAPI.Object = new Class({
	options: {
		onSuccess: Class.empty,
		onError: Class.empty,
		extraLookupData: {},
		type: ""
	},
	ready: false,
	initialize: function(item, options) {
		this.setOptions(options);
		this.type = this.options.type;
		item = ($type(item) == "array" && item.length == 1)?item[0]:item;
		if (!item) return;
		if($type(item) == "object") this.parseData(item);
		else if ($type(item) == "number") this.get(item);
		return;
	},
/*	Property: get
		Gets an item from the CNETAPI.
		
		Arguments
		id - (integer) the object id of the object	*/
	get: function(id){
		try {
			this.makeLookup().get(id);
		} catch(e){
			var msg = 'Error: error on GET: ';
			dbug.log(msg, e);
			this.fireEvent('onError', msg + e.message);
		}
	},
	process: function(obj){
		var data = {};
		$H(obj).each(function(value, key) {
			key = this.cleanKey(key);
			switch ($type(value)) {
				case "array":
					data[key] = value.map(function(v) {
						return this.clean(v, key, key);
					}, this);
					break;
				default:
					data[key] = this.clean(value, key, key);
			};
		}, this);
		return data;
	},
	cleanKey: function(key){
		return ($type(key) == "string" && key.test("^@"))?key.substring(1):key;
	},
	clean: function(value, name, path) {
		switch($type(value)) {
			case "string":
				if(value == "false") value = false;
				if(value == "true") value = true;
				if($chk(Number(value))) value = Number(value);
				return value;
			case "function":
				return value;
			case "array":
				return value.map(function(v, i) {
					return this.clean(v, i, path+'.'+name);
				}, this);
				break;
			default:
				var vhash = $H(value);
				if(value.$ && vhash.length == 1) {
					return value.$;
				} else {
					var cleaned = {};
					vhash.each(function(value, key){
						key = this.cleanKey(key);
						if($type(value) == "object" && value.$ && key.test("url", "i") && value.$.test("restApi")) {
							cleaned.walk = cleaned.walk || {};
							cleaned.walk[key] = this.follow.pass([value.$, key, path], this);
						}
						cleaned[key] = this.clean(value, key, path+'.'+name);
					}, this);
					return cleaned;
				}
			}
		return this;
	},
	makeLookup: function(){
		return new CNETAPI.Utils[this.options.type]($merge(this.options.extraLookupData, {
			instantiateResults: false,
			onError: this.handleError.bind(this),
			onSuccess: this.parseData.bind(this)
		}));
	},
	handleError: function(msg){
		this.fireEvent('onError', msg);
	},
	parseData: function(data){
		data = ($type(data) == "array" && data.length == 1)?data[0]:data;
		this.json = data;
		this.data = this.process(data);
		this.ready = true;
		this.callChain();
		this.fireEvent('onSuccess', [this, this.data, this.json]);
	},
	//TODO!
	follow: function(value, name, path) {
		dbug.log(name, value, path);
	}
});
CNETAPI.Object.implement(new Options, new Events, new Chain);

/*	Class: CNETAPI.TechProduct
		Extends <CNETAPI.Object> for type TechProduct	*/

CNETAPI.TechProduct = CNETAPI.Object.extend({
	options: {
		type: "TechProduct"
	}
});

/*	Class: CNETAPI.SoftwareProduct
		Extends <CNETAPI.Object> for type SoftwareProduct	*/

CNETAPI.SoftwareProduct = CNETAPI.Object.extend({
	options: {
		type: "SoftwareProduct"
	},
/*	Property: getSet
		Gets a product set from the CNET API.
		
		Arguments:
		id - (integer) the id of the set.
	*/
	getSet: function(id) {
		try {
			this.makeLookup().getSet(id);
		} catch(e){
			var msg = 'Error: error on getSet: ';
			dbug.log(msg, e);
			this.fireEvent('onError', msg + e.message);
		}
	}
});

/*	Class: CNETAPI.Category
		Extends <CNETAPI.Object> for type Category	*/

CNETAPI.Category = CNETAPI.Object.extend({
	options: {
		type: "Category",
		siteId: null
	},
	initialize: function(item, options) {
		this.children = [];
		if (options) this.setSiteId(options.siteId);
		this.parent(item, options);
	},
	setSiteId: function(id) {
		this.setOptions({
			extraLookupData: {
				siteId: $chk(id)?id:this.options.siteId
			}
		});
		return this.options.extraLookupData.siteId;
	},
	getChildren: function(options, data){
		var onSuccess = function(data) {
			this.children = data.map(function(d){
				d.options.siteId = siteId;
				return d;
			});
			this.callChain();
		}.bind(this);
		if (this.data.isLeaf) {
			onSuccess([]);
			return this;
		}

		options = options || {};
		var siteId = this.setSiteId(options.siteId);
		if(!$chk(siteId)) {
			var msg = 'Error: you must supply a site id for category lookups.';
			dbug.log(msg);
			this.fireEvent('onError', msg);
			return null;
		} else if(this.data.id) {
			var util = new CNETAPI.Utils[this.options.type]($merge({
					instantiateResults: true,
					resultClass: CNETAPI.Category
				}, options)).addEvent('onSuccess', onSuccess);
			util.getChildren(this.data.id, $merge(this.options.extraLookupData, data||{}));
			return this;
		} else {
			return null;
		}
		return this;
	}
});

/*	Class: CNETAPI.NewsStory
		Extends <CNETAPI.Object> for type NewsStory	*/

CNETAPI.NewsStory = CNETAPI.Object.extend({
	options: {
		type: "NewsStory"
	}
});

/*	Class: CNETAPI.NewsGallery
		Extends <CNETAPI.Object> for type NewsGallery	*/

CNETAPI.NewsGallery = CNETAPI.Object.extend({
	options: {
		type: "NewsGallery"
	}
});


CNETAPI.Utils.SearchPaths = {
	TechProduct: "/techProductSearch",
	NewsGallery: "/newsGallerySearch",
	NewsStory: "/newsStorySearch",
	SoftwareProduct: "/softwareProductSearch"
};


// Individual Implementations for each API Request type
/*	Class: CNETAPI.Utils.TechProduct
		Contains methods for getting tech products from the CNET API.
	*/
CNETAPI.Utils.TechProduct = CNETAPI.Utils.Base.extend({
		options: {
			resultClass: CNETAPI.TechProduct,
			instantiateResults: true,
			searchPath: CNETAPI.Utils.SearchPaths['TechProduct']
		},
/*	Property: search
		Retrieves a list of items based on a search string.
		
		Arguments:
		queryTerm - (string) required; the query to search on
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain query, partKey, and view.
	*/
		search : function(queryTerm, data){
			return this.request($merge({query: queryTerm}, data), this.options.searchPath, "CNETResponse.TechProducts.TechProduct");
		},
/*	Property: get
		Gets an individual tech product from the CNET API.
		
		Arguments:
		pid - (integer) the product id to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain productId, partKey, and view.
	*/
		get : function(id, data){
				return this.request($merge({productId: id}, data), "/techProduct", "CNETResponse.TechProduct");
		}
});

/*	Class: CNETAPI.Utils.SoftwareProduct
		Contains methods for getting software products from the CNET API.
	*/
CNETAPI.Utils.SoftwareProduct = CNETAPI.Utils.Base.extend({
		options: {
			resultClass: CNETAPI.SoftwareProduct,
			instantiateResults: true,
			searchPath: CNETAPI.Utils.SearchPaths['SoftwareProduct']
		},
/*	Property: search
		Retrieves a list of items based on a search string.
		
		Arguments:
		queryTerm - (string) required; the query to search on
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain query, partKey, and view.
	*/
		search : function(queryTerm, data){
				return this.request($merge({query: queryTerm}, data), this.options.searchPath, "CNETResponse.SoftwareProducts.SoftwareProduct");
		},
/*	Property: getSet
		Gets an individual software product *set* from the CNET API.
		
		Arguments:
		id - (integer) the product id of the set to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain productSetId, partKey, and view.
	*/
		getSet : function(id, data){
				return this.request($merge({productSetId: id}, data), "/softwareProduct", "CNETResponse.SoftwareProduct");
		},
/*	Property: get
		Gets an individual tech product from the CNET API.
		
		Arguments:
		pid - (integer) the product id to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain productId, partKey, and view.
	*/
		get: function(id, data) {
				return this.request($merge({productId: id}, data), "/softwareProduct", "CNETResponse.SoftwareProduct");
		}
});

/*	Class: CNETAPI.Utils.NewsStory
		Contains methods for getting news stories from the CNET API.
	*/
CNETAPI.Utils.NewsStory = CNETAPI.Utils.Base.extend({
		options: {
			resultClass: CNETAPI.NewsStory,
			instantiateResults: true,
			searchPath: CNETAPI.Utils.SearchPaths['NewsStory']
		},
/*	Property: search
		Retrieves a list of items based on a search string.
		
		Arguments:
		queryTerm - (string) required; the query to search on
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain query, partKey, and view.
	*/
		search : function(queryTerm, data){
			return this.request($merge({query: queryTerm}, data), this.options.searchPath, "CNETResponse.NewsStories.NewsStory");
		},
/*	Property: get
		Gets an individual news story from the CNET API.
		
		Arguments:
		id - (integer) the story id to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain storyId, partKey, and view.
	*/
		get: function(id, data){
			return this.request($merge({storyId: id}, data), this.options.searchPath, "CNETResponse.NewsStory");
		}
});

/*	Class: CNETAPI.Utils.NewsGallery
		Contains methods for getting news stories from the CNET API.
	*/
CNETAPI.Utils.NewsGallery = CNETAPI.Utils.Base.extend({
		options: {
			resultClass: CNETAPI.NewsGallery,
			instantiateResults: true,
			searchPath: CNETAPI.Utils.SearchPaths['NewsGallery']
		},
/*	Property: search
		Retrieves a list of items based on a search string.
		
		Arguments:
		queryTerm - (string) required; the query to search on
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain query, partKey, and view.
	*/
		search : function(queryTerm, data){
			return this.request($merge({query: queryTerm}, data), this.options.searchPath, "CNETResponse.NewsStories.NewsStory");
		},
/*	Property: get
		Gets an individual news gallery from the CNET API.
		
		Arguments:
		id - (integer) the gallery id to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain galleryId, partKey, and view.
	*/
		get: function(id, data){
			return this.request($merge({galleryId: id}, data), this.options.searchPath, "CNETResponse.NewsStory");
		}
});

/*	Class: CNETAPI.Utils.Category
		Contains methods for getting catgories from the CNET API.
		
		Note:
		For Categories, you must either pass in a siteId as an option on instantiation or pass in siteId in the data object on requests.

		Options:
		Parent options - everything in <CNETAPI.Utils.Base>
		siteId - (integer) required site id for category selection.
	*/
CNETAPI.Utils.Category = CNETAPI.Utils.Base.extend({
		options: {
			resultClass: CNETAPI.Category,
			instantiateResults: true,
			siteId: null,
			searchPath: CNETAPI.Utils.SearchPaths['TechProduct']
		},
		packer: function(results){
			results = this.parent(results);
			return results.map(function(cat){
				cat.options.siteId = this.options.siteId;
				return cat;
			}, this);
		},
/*	Property: get
		Gets an individual category from the CNET API.
		
		Arguments:
		id - (integer) the category id to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain categoryId, partKey, and view.
	*/
		get: function(id, data){
			data = data||{};
			data.siteId = data.siteId || this.options.siteId;
			if(!$chk(data.siteId)) {
				dbug.log("You must supply a site id for category lookups");
				this.throwErr("You must supply a site id for category lookups");
			}
			this.options.siteId = data.siteId;
			return this.request($merge({categoryId: id}, data), "/category", "CNETResponse.Category");
		},
/*	Property: getChildren
		Gets the children of a category from the CNET API.
		
		Arguments:
		id - (integer) the id of the parent category to retrieve its children
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain categoryId, partKey, and view.
	*/
		getChildren: function(id, data){
			data = data||{};
			data.siteId = data.siteId || this.options.siteId;
			if(!$chk(data.siteId)) {
				dbug.log("You must supply a site id for category lookups");
				this.throwErr("You must supply a site id for category lookups");
			}
			return this.request($chk(id)?$merge({categoryId: id}, data):data, "/childCategories", "CNETResponse.ChildCategories.Category");
		},
/*	Property: search
		Search for category by term.
		
		Arguments:
		queryTerm - (string) the query to search on
		type - (string) either TechProduct, SoftwareProduct, NewsStory, or NewsGallery; see below
		data - (object)  optional data passed on to JsonP.options.data. The data object will already contain results: 1, iod: relatedCast, and the query term.

		Note:
		Currently this search *only works for TechProducts*.	*/
		search : function(queryTerm, type, data){
			data = $merge({
				results: 1,
				iod:'relatedCats'
			}, data);
			return this.request($merge({query: queryTerm}, data), type||this.options.searchPath, "CNETResponse.RelatedCategories");
		}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/CNETAPI/CNETAPI.js,v $
$Log: CNETAPI.js,v $
Revision 1.13  2007/10/18 21:43:35  newtona
updating components that reference images and assets at www.cnet.com to allow for easy over-writing for that source url

Revision 1.12  2007/10/18 17:40:28  newtona
Adding getSet to CNETAPI.SoftwareProduct

Revision 1.11  2007/10/18 17:34:00  newtona
tweaking CNETAPI.Object instantiation
fixing a typo in the dl product picker

Revision 1.10  2007/10/18 00:53:09  newtona
adding error handler logic for CNETAPI.Object

Revision 1.9  2007/10/15 20:42:37  newtona
perhaps the last cnetapi docs update?

Revision 1.8  2007/10/15 20:40:48  newtona
still more cnetapi docs

Revision 1.7  2007/10/15 20:39:38  newtona
more cnetapi docs

Revision 1.6  2007/10/15 20:37:28  newtona
adding docs to cnetapi

Revision 1.5  2007/10/15 18:10:04  newtona
cleaning up javascript warnings in CNETAPI.js

Revision 1.4  2007/10/15 18:06:22  newtona
missed one

Revision 1.3  2007/10/15 18:03:43  newtona
cleaning up syntax in CNETAPI. semicolons and whatnot

Revision 1.2  2007/10/11 00:56:13  newtona
adding ontology picklet to redball common full
adding search to categories in CNETAPI.js
tweaking preview layout in download.product.picker
new file: ontology picklet

Revision 1.1  2007/10/05 20:59:13  newtona
hey, new files!
CNETAPI - Hunter's first work on an API handler
CNETAPI.Category.Browser.js - this is still very rough and not ready for primetime.
ObjectBrowser.js - also might have a few quirks; this is a tree browser for objects (kinda like in firebug)
element.position.js - fixed an issue with positioning.


*/
/*	Script: setAssetHref.js
		Overrides the location of assets referenced in CNET js framework files.
		
		You can download the assets at google
		http://code.google.com/p/cnetjavascript/downloads/list
		
		Function: setCNETAssetBaseHref
		Overrides the location of assets referenced in CNET js framework files.
		
		Arguments:
		baseHref - (string) the path to the assets directory for CNET JS files.
		
		Example:
		If the file "/tips/bubble.png" were at the url "http://mysite.com/cnetAssets/tips/bubble.png"
		you would execute:
		> setCNETAssetBaseHref('http://mysite.com/cnetAssets');
		
		You only need to do this once on the page and then all the asset requests will go to 
		your server instead of CNETs.
	*/
function setCNETAssetBaseHref(baseHref) {
	if (typeof stickyWinHtml != "undefined") {
		var CGFstickyWinHTML = stickyWinHTML.bind(window);
		stickyWinHTML = function(caption, body, options){
		    return CGFstickyWinHTML(caption, body, $merge({
		        baseHref: baseHref + '/stickyWinHTML/'
		    }, options));
		};
	}
	if (typeof TagMaker != "undefined") {
		TagMaker = TagMaker.extend({
		    options: {
		        baseHref: baseHref + '/tips/'
		    }
		});
	}

	if (typeof simpleErrorPopup != "undefined") {
		var CGFsimpleErrorPopup = simpleErrorPopup.bind(window);
		simpleErrorPopup = function(msghdr, msg, baseHref) {
		    return CGFsimpleErrorPopup(msghdr, msg, baseHref|| baseHref + "/simple.error.popup");
		};
	}
	
	if (typeof DatePicker != "undefined") {
		DatePicker = DatePicker.extend({
		    options: {
		        baseHref: baseHref
		    }
		});
	}
	
	if (typeof ProductPicker != "undefined") {
		ProductPicker = ProductPicker.extend({
		    options:{
		        baseHref: baseHref + '/Picker'
		    }
		});
	}
	
	if (typeof Autocompleter != "undefined") {
		Autocompleter.Base = Autocompleter.Base.extend({
		    options:{
		        baseHref: baseHref + '/autocompleter/'
		    }
		});
	}
	
	if (typeof Lightbox != "undefined") {
		Lightbox = Lightbox.extend({
		    options: {
		        assetBaseUrl: baseHref + '/slimbox/'
		    }
		});
	}
};
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/setAssetHref.js,v $
$Log: setAssetHref.js,v $
Revision 1.7  2007/11/02 18:15:38  newtona
fixing an issue with the image path in setAssetHref for the date picker
adding mms to url validator in form validator

Revision 1.6  2007/10/30 18:59:53  newtona
fixpng.js now supports background png images
doc typo in setAssetHref.js

Revision 1.5  2007/10/24 23:27:22  newtona
adding error catchers for setAssetHref.js

Revision 1.4  2007/10/24 17:26:20  newtona
typo in setAssetHref.js

Revision 1.3  2007/10/23 23:25:33  newtona
fixing a typo in setAssetHref.js

Revision 1.2  2007/10/23 23:11:55  newtona
tweaking setAssetHref.js

Revision 1.1  2007/10/23 23:10:24  newtona
added mootools debugger to cvs
new file: setAssetHref.js; enables you to quickly set the location of image assets contained in the framework so you don't use CNET's versions, which can sometimes be slow.


*/
