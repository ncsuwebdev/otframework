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
