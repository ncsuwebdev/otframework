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
