/*
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

*/