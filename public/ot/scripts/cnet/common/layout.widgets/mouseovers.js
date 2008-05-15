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

*/