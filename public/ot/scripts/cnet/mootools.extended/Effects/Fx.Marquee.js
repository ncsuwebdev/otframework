/*	Script: Fx.Marquee.js
		A simple marquee effect for fading in and out messages.
		
		Author:
		Aaron Newton
		
		Dependencies:
		Mootools 1.11 - <Fx.Styles.js> and all its dependencies
		
		Class: Fx.Marquee
		A simple marquee effect for fading in and out messages.
		
		Arguments:
		container - (DOM element or id) the item that contains the message
		options - (object) key/value set of options
		
		Note:
		All options specified can be specified at initialization and also at
		invocation (so the same effect can be used for numerous messages).
		
		Options:
		mode - (string) "horizontal" or "vertical" - which way the marquee goes
		message - (string) the message to display; can also be specified at run time
		revert - (boolean) revert back to the initial message after a delay; defaults to true
		delay - (integer) duration (in milliseconds) to wait before reverting
		cssClass - (string) the css class name to add to the message
		showEffect - (object) an object passed to Fx.Styles for the transition in; defaults to {opcaity: 1}
		hideEffect - (object) an object passed to Fx.Styles for the transition out; defaults to {opcaity: 0}
		revertEffect - (object) an object passed to Fx.Styles for the transition on revert; defaults to {opcaity: [0,1]}
		currentMessage - (dom element or id) the container of the currently displayed message; defaults to the first
					child of the container
	*/
Fx.Marquee = Fx.Styles.extend({
	options: {
		mode: 'horizontal', //or vertical
		message: '', //the message to display
		revert: true, //revert back to the previous message after a specified time
		delay: 5000, //how long to wait before reverting
		cssClass: 'msg', //the css class to apply to that message
		showEffect: {
			opacity: 1
		},
		hideEffect: {opacity: 0},
		revertEffect: {
			opacity: [0,1]
		},
		currentMessage: null,
		onRevert: Class.empty,
		onMessage: Class.empty
	},
	initialize: function(container, options){
		container = $(container); //make sure the container is an extended DOM element
		//get the message from the options
		var msg = this.options.currentMessage || (container.getChildren().length == 1)?container.getFirst():''; 
		//create a wrapper to hold the messages
		var wrapper = new Element('div', {	
				styles: { position: 'relative' },
				'class':'fxMarqueeWrapper'
			}).injectInside(container); //inject it in the container
		//set up the Fx.Styles effect
		this.parent(wrapper, options);
		//store the current message
		this.current = this.wrapMessage(msg);
	},
/*	Property: wrapMessage
		Internal; wraps the message in a span element.
		
		Arguments:
		msg - (string or DOM element) the message element
 */
	//internal; wraps a message in a span element
	wrapMessage: function(msg){
		if($(msg) && $(msg).hasClass('fxMarquee')) { //already set up
			var wrapper = $(msg);
		} else {
			//create the wrapper
			var wrapper = new Element('span', {
				'class':'fxMarquee',
				styles: {
					position: 'relative'
				}
			});
			if($(msg)) wrapper.adopt($(msg)); //if the message is a dom element, inject it inside the wrapper
			else if ($type(msg) == "string") wrapper.setHTML(msg); //else set it's value as the inner html
		}
		return wrapper.injectInside(this.element); //insert it into the container
	},
/*	Property: announce
		Shows the message, hiding the old one.

		Arguments:
		options - (object) a key/value set of options
		
		Options:
		These are identical to the optoins for the class. This way you can use the instance for numerous messages.
	*/
	announce: function(options) {
		this.setOptions(options).showMessage();
		return this;
	},
/*	Property: showMessage
		Internal; shows the message, hiding the old one; reverts if it's supposed to based on the options passed in
		
		Arguments:
		reverting - (boolean) true if this method has called itself to revert to previous state.
 */
	showMessage: function(reverting){
		//delay the fuction if we're reverting
		(function(){
			//store a copy of the current chained functions
			var chain = this.chains?this.chains.copy():[];
			//clear teh chain
			this.clearChain();
			this.element = $(this.element);
			this.current = $(this.current);
			this.message = $(this.message);
			//execute the hide effect
			this.start(this.options.hideEffect).chain(function(){
				//if we're reverting, hide the message and show the original
				if(reverting) {
					this.message.hide();
					if(this.current) this.current.show();
				} else {
					//else we're showing; remove the current message
					if(this.message) this.message.remove();
					//create a new one with the message supplied
					this.message = this.wrapMessage(this.options.message);
					//hide the current message
					if(this.current) this.current.hide();
				}
				//if we're reverting, execute the revert effect, else the show effect
				this.start((reverting)?this.options.revertEffect:this.options.showEffect).chain(function(){
					//merge the chains we set aside back into this.chains
					this.chains.merge(chain);
					this.fireEvent((reverting)?'onRevert':'onMessage');
					//then, if we're reverting, show the original message
					if(!reverting && this.options.revert) this.showMessage(true);
					//if we're done, call the chain stack
					else this.callChain.delay(this.options.delay, this);
				}.bind(this));
			}.bind(this));
		}).delay((reverting)?this.options.delay:10, this);
		return this;
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Effects/Fx.Marquee.js,v $
$Log: Fx.Marquee.js,v $
Revision 1.3  2007/08/31 00:26:53  newtona
a little more tweaking for chaining in Fx.Marquee

Revision 1.2  2007/08/30 23:59:33  newtona
fixed chaining in Fx.Marquee; added to redball.common.full
tweaked docs in IconMenu

Revision 1.1  2007/08/20 21:20:46  newtona
first big check in for RTSS History


*/
