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
