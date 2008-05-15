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

*/