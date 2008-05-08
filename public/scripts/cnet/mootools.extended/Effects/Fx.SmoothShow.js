/*	Script: Fx.Smoothshow.js
Extends the <Element> object.

Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
	
		Class: Fx.SmoothShow
		Transitions the height, opacity, padding, and margin (but not border) from and to their current height from and to zero, then set's display to none or block and resets the height, opacity, etc. back to their original values.

		Arguments:
		options - a key/value object of options
		
		Options:
		all the options passed along to <Fx.Base> (transition, duration, etc.); (optional); PLUS
		
		styles - (array; optional) css properties to transition in addition to width/height; 
							defaults to ['padding','border','margin']
		mode - (string; optional) 'vertical','horizontal', or 'both' to describe how the element should slide in.
							defaults to 'vertical'
		heightOverride - (integer; optional) height to open to; overrides the default offsetheight
		widthOverride -  (integer; optional) width to open to; overrides the default offsetwidth
	*/
Fx.SmoothShow = Fx.Styles.extend({
	options: {
		styles: ['padding','border','margin'],
		transitionOpacity: true,
		mode:'vertical',
		heightOverride: null,
		widthOverride: null
	},
	//Mootools 1.0 compatability; CNET needs this for now
	//just adds "px" to integer values
	fixStyle: function(style, name){
		if(!$type(style)=="number") return style;
		var fix = ['margin', 'padding', 'width', 'height'].some(function(st){return name.test(st, 'i')});
		return (fix)?style+'px':style;
	},
/*	Property: hide
		Transitions the height, opacity, padding, and margin (but not border) from their current height to zero, then set's display to none and resets the height, opacity, etc. back to their original values.	
		*/
	hide: function(){
		try {
			if(!this.hiding && !this.showing) {
				if(this.element.getStyle('display') != 'none'){
					this.hiding = true;
					this.showing = false;
					this.hidden = true;
					var startStyles = this.element.getComputedSize({
						styles: this.options.styles,
						mode: this.options.mode
					});
					if (this.element.fxOpacityOk() && this.options.transitionOpacity) startStyles.opacity = 1;
					var zero = {};
					$each(startStyles, function(style, name){
						zero[name] = this.fixStyle(0, name); 
					}, this);
					this.chain(function(){
						if(this.hidden) {
							this.hiding = false;
							$each(startStyles, function(style, name) {
								startStyles[name] = this.fixStyle(style, name);
							}, this);
							this.element.setStyles(startStyles).setStyle('display','none');
						}
						this.callChain();
					}.bind(this));
					this.start(zero);
				} else {
					this.callChain.delay(10, this);
					this.fireEvent('onComplete', this.element);
				}
			}
		} catch(e) {
			this.element.hide();
			this.callChain.delay(10, this);
			this.fireEvent('onComplete', this.element);
		}
		return this;
	},
/*	Property: show
		Sets the display of the element to opacity: 0 and display: block, then transitions the height, opacity, padding, and margin (but not border) from zero to their proper height.
	*/
	show: function(){
		try {
			if(!this.showing && !this.hiding) {
				//if(arguments[1]) options.heightOverride = arguments[1];
				if(this.element.getStyle('display') == "none" || 
					 this.element.getStyle('visiblity') == "hidden" || 
					 this.element.getStyle('opacity')==0){
					this.showing = true;
					this.hiding = false;
					this.hidden = false;
					//toggle display, but hide it
					var before = this.element.getStyles('visibility', 'display', 'position');
					this.element.setStyles({
						visibility: 'hidden',
						display: 'block',
						position:'absolute'
					});
					//enable opacity effects
					if(this.element.fxOpacityOk() && this.options.transitionOpacity) this.element.setStyle('opacity',0);
					//create the styles for the opened/visible state
					var startStyles = this.element.getComputedSize({
						styles: this.options.styles,
						mode: this.options.mode
					});
					//reset the styles back to hidden now
					this.element.setStyles(before);
					$each(startStyles, function(style, name) {
						startStyles[name] = this.fixStyle(style, name);
					}, this);
					//if we're overridding height/width
					if($chk(this.options.heightOverride)) startStyles['height'] = this.options.heightOverride.toInt()+'px';
					if($chk(this.options.widthOverride)) startStyles['width'] = this.options.widthOverride.toInt()+'px';
					if(this.element.fxOpacityOk() && this.options.transitionOpacity) startStyles.opacity = 1;
					//create the zero state for the beginning of the transition
					var zero = { 
						height: '0px',
						display: 'block'
					};
					$each(startStyles, function(style, name){ zero[name] = this.fixStyle(0, name); }, this);
					//set to zero
					this.element.setStyles(zero);
					//start the effect
					this.start(startStyles);
					this.chain(function(){
						if(!this.hidden) this.showing = false;
						this.callChain();
					}.bind(this));
				} else {
					this.callChain();
					this.fireEvent('onComplete', this.element);
				}
			}
		} catch(e) {
			this.element.setStyles({
				display: 'block',
				visiblity: 'visible',
				opacity: 1
			});
			this.callChain.delay(10, this);
			this.fireEvent('onComplete', this.element);
		}
		return this;
	},
/*	Property: toggle
		Toggles the element from shown to hidden.
	*/
	toggle: function(){
		try {
			if(this.element.getStyle('display') == "none" || 
				 this.element.getStyle('visiblity') == "hidden" || 
				 this.element.getStyle('opacity')==0){
				this.show();
		 	} else {
				this.hide();
			}
		} catch(e) { this.show(); }
	 return this;
	}
});
Fx.SmoothShow.implement(new Options);
Fx.SmoothShow.implement(new Events);


/*	Class: Element
		Adds <Fx.SmoothShow> shortcuts to the <Element> class.
	*/
Element.extend({
/*	Property: smoothShow
		Creates a new instance of <Fx.SmoothShow> and calls its *show* method. Returns the instance of <Fx.SmoothShow>.

		Arguments: 
		options	- see <Fx.SmoothShow> options.
	*/
	smoothShow: function(options){
 		if (arguments[1]) { options.heightOverride = arguments[1]; }
		return new Fx.SmoothShow(this, options).show();
	},
/*	Property: smoothHide
		Creates a new instance of <Fx.SmoothShow> and calls its *hide* method. Returns the instance of <Fx.SmoothShow>.

		Arguments: 
		options	- see <Fx.SmoothShow> options.
	*/
	smoothHide: function(options){
 		if (arguments[1]) { options.heightOverride = arguments[1]; }
		return new Fx.SmoothShow(this, options).hide();
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Effects/Fx.SmoothShow.js,v $
$Log: Fx.SmoothShow.js,v $
Revision 1.16  2007/10/26 18:41:07  newtona
damned semi-colons

Revision 1.15  2007/10/26 18:39:06  newtona
smoothShow/Hide had issues with Safari < 3; it now degrades to just toggle the style (no transitions)

Revision 1.14  2007/09/07 23:15:14  newtona
fixed a race-condition like issue with Fx.SmoothShow and chaining

Revision 1.13  2007/09/05 18:37:07  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.12  2007/08/09 02:06:39  newtona
modified Fx.SmoothShow to prevent it flickering

Revision 1.11  2007/07/20 20:05:51  newtona
Fx.Smoothshow: that last fix didn't quite do it.

Revision 1.10  2007/07/20 17:56:24  newtona
Fixed a bug in Fx.SmoothShow that prevented it from getting the dimensions of hidden elements (to show them)

Revision 1.9  2007/07/17 20:38:44  newtona
Fx.SmoothShow - refactored the exploration of the element dimensions when hidden so that it isn't visible to the user
element.position - refactored to allow for more than just the previous 5 positions, now supports nine: all corners, all mid-points between those corners, and the center
string.cnet.js - fixed up the query string logic to decode values

Revision 1.8  2007/06/28 01:28:08  newtona
adding an option for opacity fading to Fx.SmoothShow

Revision 1.7  2007/06/12 20:46:21  newtona
added tbody to html.table.js
added legacy argument support to Fx.SmoothShow

Revision 1.6  2007/05/31 23:57:49  newtona
slight tweak to last checkin

Revision 1.5  2007/05/31 23:55:30  newtona
chaining now works properly; added logic to handle double-click behavior

Revision 1.4  2007/05/31 21:33:42  newtona
.toggle returns the effect

Revision 1.3  2007/05/30 20:32:33  newtona
doc updates

Revision 1.2  2007/05/29 22:46:19  newtona
syntax fix in Fx.SmoothShow; docs update, too.

Revision 1.1  2007/05/29 22:27:02  newtona
rebuilt cat libs, added Fx.SmoothShow.js


*/


