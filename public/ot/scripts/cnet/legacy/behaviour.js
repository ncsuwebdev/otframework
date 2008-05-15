/*	Script: behaviour.js
		Mootools version of the (now somewhat old) behaviour.js. See: http://bennolan.com/behaviour/
		
		Note:
		This script isn't really meant to be used. It's better to just write these rules yourself 
		than use this class. It's provided here only for legacy support. These three lines here:
		
		Dependencies:
		Moo - <Moo.js>, <Utility.js>, <Dom.js>, <Array.js>
		
		Author: 
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)

Nutshell:		
(start code)
$$(bhvr).each(function(el){ //get all the elements for this action
	bhvrs[bhvr](el); //executes the registered behavior on that element
});
(end)

		Is really all this class does. The rest of it just handles registering things to apply and whatnot.
		Using behavior.js you'd write this:
(start code)
var myrules = {
	'#example li' : function(el){
		el.onclick = function(){
			this.parentNode.removeChild(this);

		}
	}
};
Behaviour.register(myrules);
(end)

		To do this with the Mootools syntax you'd write:
(start code)
window.onDomReady(function(){
	$$('#example li').each(function(el){
		el.onclick = function(){
			this.parentNode.removeChild(this);
		}
	});
});
(end)

		In otherwords, this library doesn't save you any time (though it did in its day).
		
		Class: BehaviourBaseClass
		The functionality of the behaviour.js library (http://bennolan.com/behaviour/).
	*/

var BehaviourBaseClass = new Class({
	initialize: function(){
		this.behaviours = [];
		var bhvr = this;
		window.onDomReady(function(){bhvr.apply()});
	},
/*	Property: register
		Registers rules to be applied when the window is ready.
		
		Arguments:
		actions - an object with actions.
		
		Example:
(start code)
var myrules = {
	'#example li' : function(el){
		el.onclick = function(){
			this.parentNode.removeChild(this);

		}
	}
};
Behaviour.register(myrules);
//all elements matching #example li will be removed when clicked(end)
	*/
	register: function(actions){
		if(! this.behaviours.test(actions))
			this.behaviours.push(actions);
	},
/*	Property: apply
		Applies the actions registered (see <register>). These will only work when the DOM is ready.
		
		Arguments:
		actions - an optional object to apply; otherwise all the registered actions are applied.
	*/
	apply: function(actions) {
		if ($type(actions)!='array')
			actions = this.behaviours;
		actions.each(function(bhvrs){
			for (bhvr in bhvrs){
				try {
					if($type(bhvrs[bhvr])=='function') {
						$$(bhvr).each(function(el){
							bhvrs[bhvr](el);
						});
					}
				} catch(e){}
			}
		});
	}
});
/*	Class: Behaviour
		An instance of <BehaviourBaseClass> for legacy support.
	*/
var Behaviour = new BehaviourBaseClass(); 

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/legacy/behaviour.js,v $
$Log: behaviour.js,v $
Revision 1.6  2007/01/26 05:56:14  newtona
syntax update for mootools 1.0

Revision 1.5  2007/01/11 22:27:31  newtona
docs fixes

Revision 1.4  2007/01/11 20:45:16  newtona
fixed syntax error with Window.onDomReady

Revision 1.3  2007/01/09 01:27:13  newtona
docs fixes

Revision 1.2  2007/01/05 19:43:53  newtona
added docs


*/

