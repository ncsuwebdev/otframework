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
