/*	Script: CNETAPI.js
		Utility class for the CNET API (http://api.cnet.com) to help with remote retrieval of product data.
		
		Authors:
		Hunter Brown <hunter [dot] brown [at] cnet [dot] com>
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools 1.11 - <Core.js>, <Class.js>, <Class.Extras.js>, <Array.js>, <String.js>, <Function.js>, <Number.js>, <Element.js>,
			<Assets.js>, <Hash.js>
		CNET - <jsonp.js>
		
		NameSpace: CNETAPI
		Holder for global CNET API url and (optional) partkey for a applications on page.
		
		
		Property: register
		Adds an application to the CNETAPI list for future requests.
		
		Arguments: 
		applicationName - (string) a unique key for your application that maps to the url and (optional) partKey/partTag arguments
		devKeyOrTag - (integer or string; optional) your developer key or partTag; see http://api.cnet.com.
		url - (string) the url to the api; defaults to http://api.cnet.com/restApi/v1.0; useful for internal development here at CNET.
		
		IMPORTANT:
		For front-end applications, we do not surface the partTag (and you shouldn't use your partKey for any front end 
		development at all). This means that your application should converse with a server-side app that then makes requests
		to the API, passing along your partTag. This means your partTag is not exposed anywhere in the javascript.
		
		For internal development:
		If you just want to fool around with the API, all you have to do is register the "default" app with your partKey or partTag:
		
		(start code)
		CNETAPI.register('default', 'myPartKey', 'http://api.cnet.com/restApi/v1.0');
		//the url is optional, so this does the same thing:
		CNETAPI.register('default', 'myPartKey');
		(end)
		
		For external development:
		You would register your app, no key, and a url pointing to your application server:
		(start code)
		CNETAPI.register('default', null, 'http://foo.cnet.com/myApp');
		(end)
		
		Then your application would insert the partKey and forward requests to the API.
		
	*/
var CNETAPI = {
	register: function(applicationName, devKeyOrTag, url) {
		CNETAPI.apps[applicationName] = {
			url: url||'http://api.cnet.com/restApi/v1.0'	
		};
		if (devKeyOrTag) {
			if(Number(devKeyOrTag)) CNETAPI.apps[applicationName].partKey = devKeyOrTag;
			else CNETAPI.apps[applicationName].partTag = devKeyOrTag;
		}
	},
	get: function(applicationName) {
		return CNETAPI.apps[applicationName];
	}
};
CNETAPI.apps = {};
CNETAPI.register('default')
CNETAPI.Utils = {};

/*	Class: CNETAPI.Utils.Base
		Foundation class for all CNETAPIUtil lookup classes.
		
		Arguments:
		options - (object) key/value set of options.
		
		Options:
		jsonpOptions - (object) options object passed to jsonp; defaults to data.viewType = 'json' and data.partKey = CNETAPI.Utils.partKey (see <CNETAPI.Utils>);

		Events:
		onComplete - (function) callback executed whenever a request is complete; passed either an array of data or a string representing an error message.
		onSuccess - (function) callback executed when no error is returned; always passed an array, though it may be empty.
		onError - (function) callback executed when an error is returned; always passed a string (whatever the api service returned).
		
		Note:
		CNETAPI.Utils.Base is extended into specific object classes. For example, <CNETAPI.Utils.TechProduct> lets you look up tech products. The utils classes return <CNETAPI.Object> instances with the same namespace. So for example, <CNETAPI.Utils.TechProduct> will return instances of <CNETAPI.TechProduct>.

		Example:
(start code)
//you have to do this only once on your page
//this is my dev key; get your own!
new CNETAPI(19926949750937665684988687810562);
//now our request:
new CNETAPI.Utils.TechProduct({
  onSuccess: function(items){
    var ol = new Element('ol');
    items.each(function(item){
      ol.adopt(new Element('li').setHTML(item.data.Name));
    });
    $('apiResults').adopt(ol);
  }
}).search("Ipod");
(end)
	*/
CNETAPI.Utils.Base = new Class({
	options: {
		applicationName: 'default',
		jsonpOptions: {
			data: {
				viewType : 'json'
			}
		},
		onComplete: Class.empty,
		onSuccess: Class.empty,
		onError: Class.empty,
		instantiateResults: false,
		resultClass: null,
		errorPath: 'CNETResponse.Error.ErrorMessage.$'
	},
	//easyToUseObjectBuilder : null,
	//apiObjectBuilder : null,
	//packageResults : null,

	initialize : function(options){
		var data = {};
		this.app = CNETAPI.get(options.applicationName || this.options.applicationName);
		if (this.app.partKey) data.partKey = this.app.partKey;
		if (this.app.partTag) data.partTag = this.app.partTag;
		this.setOptions($merge({
			jsonpOptions: {
				data: data
			}
		}, options));
	},
	//internal
	//gets the query class; defaults to JSONP
	//url - url to hit for data
	//data - key/value options to pass in the query
	getQuery: function(url, options){
		options.data = options.data||{};
		$each(options.data, function(val, key) { 
			options.data[key] = $type(val)=="string"?unescape(val):val; 
		});
		var j = new JsonP(url||"", options);
		return j;
	},
	//internal
	//attempts to return the title of an object
	//data - the object to inspect for the title
	//key - the key or object to look for the $ property
/*		checkDefined : function (returnObject, data, key){
			if(data[key] && data[key].$) return data[key].$ || "";
			return key.$ || "";
	},	*/
	//internal
	//packages up results into a shallow array of results
	//results - the results from the CNET API
	packer : function(results){
		if($type(results) == "array") results = results.filter(function(result){return result});
		else if (results) results = [results];
		else results = [];
		if(this.options.instantiateResults && this.options.resultClass) {
			return results.map(function(obj){
				return new this.options.resultClass(obj);
			}, this);
		} else {
			return results;
		}
	},
	//internal
	//allows you to get food.fruit.apples.red if you have the string "fruit.apples.red"
	//getMemberByPath(food, "fruit.apples.red")
	getMemberByPath: function(obj, path){
		if (path === "" || path == "top" || !path) return obj;
		var member = obj;
		path.split(".").each(function(p){
			if (p === "") return;
			if (member[p]) member = member[p];
			else member = obj;
		}, this);
		return (member == obj)?false:member;	
	},
	//internal
	//handles returned results from the CNET API
	//obj - the json object returned
	handleApiResults : function(obj, path){
		//deal with server error
		var error = this.getMemberByPath(obj, this.options.errorPath);
		return (error) ? error : this.getMemberByPath(obj, path);
		//if the container is specified
	},
	//internal
	//executes a request to the API service
	//jsonData - object passed to jsonp, merged with the data in this.options.jsonpOptions (view & partner key by default)
	//urlSuffix - suffix added to the api url defined in the options
	//path - path to the desired data in the object returned; ex: CNETResponse.TechProducts.TechProduct
	request: function(jsonData, urlSuffix, path){
		var jsonpOptions = $merge(this.options.jsonpOptions, {
				data : jsonData
		});
		var query = this.getQuery(this.app.url + urlSuffix, jsonpOptions);
		query.addEvent('onComplete',  function(results){
			results = this.handleApiResults(results, path);
			if ($type(results) == "string") {
				dbug.log('CNET API Error: ', results);
				this.fireEvent('onError', [results, query, this]);
			} else {
				this.fireEvent('onSuccess', [this.packer(results), query, this]);
			}
			this.fireEvent('onComplete', [this.packer(results), query, this]);
		}.bind(this));
		query.request();
		return this;
	},
/*	internal
		Throws a javascript error.
		
		Arguments:
		msg - (string) the message for the user
*/
	throwErr: function(msg){
		// Create an object type UserException
		function err (message)
		{
		  this.message=message;
		  this.name="CNETAPI.Utils Exception:";
		};
		
		// Make the exception convert to a pretty string when used as
		// a string (e.g. by the error console)
		err.prototype.toString = function ()
		{
		  return this.name + ': "' + this.message + '"';
		};
		
		// Create an instance of the object type and throw it
		throw new err(msg);
	}
});
CNETAPI.Utils.Base.implement(new Options, new Events);

/*	Class: CNETAPI.Object	
		Base class for all CNET API returned objects. Currently it just cleans 
		up the Objects and allows for inspection with its .type value.
		
		Arguments:
		item - (mixed: object or integer) if integer the class will attempt to get the object from the CNETAPI.Utils.* class.
						If object then the class will use this object as the data (making the assumption that it came from the API).
		options - (object) key/value map of options.
		
		Options:
		extraLookupData - (object) key/value pairs for additional parameters to be sent in API requests (siteId for example)
		type - (string) CNET Type of object. Must related to a CNETAPI.* class (i.e. "TechProduct" = CNETAPI.TechProduct

		Events:
		onSucceess - (function) callback executed whenever results are returned from the CNET API using the .get method.
			passed the instance of the object, the .data value, and the .json value as arguments.
		onError - (function) callback executed when there is an error retrieving data from the API. passed an error message as argument.
		
		Instance Values:
		ready - (boolean) true if there is data present in the class
		json - (object) the raw data passed in or returned by the API
		data - (object) the cleaned data derived from the JSON (no .$, @, etc.)
		
		Note:
		<CNETAPI.Object> is extended into specific object classes. So for example <CNETAPI.TechProduct> is a tech product. You can look up / instanciate individual objects by instanciating them or you can use the corresponding <CNETAPI.Utils.Base> class. So <CNETAPI.Utils.TechProduct> returns instances of <CNETAPI.TechProduct>.

		Example:
(start code)
//you have to do this only once on your page
//this is my dev key; get your own!
new CNETAPI(19926949750937665684988687810562);
//now our request:
new CNETAPI.TechProduct(32069546).chain(function(){
  dbug.log("got the Ipod, here's the data: ", this.data);
  alert(this.data.EditorsRating.$);
});
(end)		
		
*/
CNETAPI.Object = new Class({
	options: {
		applicationName: 'default',
		onSuccess: Class.empty,
		onError: Class.empty,
		extraLookupData: {},
		type: ""
	},
	ready: false,
	initialize: function(item, options) {
		this.setOptions(options);
		this.app = CNETAPI.get(this.options.applicationName);
		this.type = this.options.type;
		item = ($type(item) == "array" && item.length == 1)?item[0]:item;
		if (!item) return;
		if($type(item) == "object") this.parseData(item);
		else if ($type(item) == "number") this.get(item);
		return;
	},
/*	Property: get
		Gets an item from the CNETAPI.
		
		Arguments
		id - (integer) the object id of the object	*/
	get: function(id){
		try {
			this.makeLookup().get(id);
		} catch(e){
			var msg = 'Error: error on GET: ';
			dbug.log(msg, e);
			this.fireEvent('onError', msg + e.message);
		}
	},
	process: function(obj){
		var data = {};
		$H(obj).each(function(value, key) {
			key = this.cleanKey(key);
			switch ($type(value)) {
				case "array":
					data[key] = value.map(function(v) {
						return this.clean(v, key, key);
					}, this);
					break;
				default:
					data[key] = this.clean(value, key, key);
			};
		}, this);
		return data;
	},
	cleanKey: function(key){
		return ($type(key) == "string" && key.test("^@"))?key.substring(1):key;
	},
	clean: function(value, name, path) {
		switch($type(value)) {
			case "string":
				if(value == "false") value = false;
				if(value == "true") value = true;
				if($chk(Number(value))) value = Number(value);
				return value;
			case "function":
				return value;
			case "array":
				return value.map(function(v, i) {
					return this.clean(v, i, path+'.'+name);
				}, this);
				break;
			default:
				var vhash = $H(value);
				if(value.$ && vhash.length == 1) {
					return value.$;
				} else {
					var cleaned = {};
					vhash.each(function(value, key){
						key = this.cleanKey(key);
						if($type(value) == "object" && value.$ && key.test("url", "i") && value.$.test("restApi")) {
							cleaned.walk = cleaned.walk || {};
							cleaned.walk[key] = this.follow.pass([value.$, key, path], this);
						}
						cleaned[key] = this.clean(value, key, path+'.'+name);
					}, this);
					return cleaned;
				}
			}
		return this;
	},
	makeLookup: function(){
		return new CNETAPI.Utils[this.options.type]($merge(this.options.extraLookupData, {
			instantiateResults: false,
			onError: this.handleError.bind(this),
			onSuccess: this.parseData.bind(this),
			applicationName: this.app.applicationName
		}));
	},
	handleError: function(msg){
		this.fireEvent('onError', msg);
	},
	parseData: function(data){
		data = ($type(data) == "array" && data.length == 1)?data[0]:data;
		this.json = data;
		this.data = this.process(data);
		this.ready = true;
		this.callChain();
		this.fireEvent('onSuccess', [this, this.data, this.json]);
	},
	//TODO!
	follow: function(value, name, path) {
		dbug.log(name, value, path);
	}
});
CNETAPI.Object.implement(new Options, new Events, new Chain);

/*	Class: CNETAPI.TechProduct
		Extends <CNETAPI.Object> for type TechProduct	*/

CNETAPI.TechProduct = CNETAPI.Object.extend({
	options: {
		type: "TechProduct"
	}
});

/*	Class: CNETAPI.SoftwareProduct
		Extends <CNETAPI.Object> for type SoftwareProduct	*/

CNETAPI.SoftwareProduct = CNETAPI.Object.extend({
	options: {
		type: "SoftwareProduct"
	},
/*	Property: getSet
		Gets a product set from the CNET API.
		
		Arguments:
		id - (integer) the id of the set.
	*/
	getSet: function(id) {
		try {
			this.makeLookup().getSet(id);
		} catch(e){
			var msg = 'Error: error on getSet: ';
			dbug.log(msg, e);
			this.fireEvent('onError', msg + e.message);
		}
	}
});

/*	Class: CNETAPI.Category
		Extends <CNETAPI.Object> for type Category	*/

CNETAPI.Category = CNETAPI.Object.extend({
	options: {
		type: "Category",
		siteId: null
	},
	initialize: function(item, options) {
		this.children = [];
		if (options) this.setSiteId(options.siteId);
		this.parent(item, options);
	},
	setSiteId: function(id) {
		this.setOptions({
			extraLookupData: {
				siteId: $chk(id)?id:this.options.siteId
			}
		});
		return this.options.extraLookupData.siteId;
	},
	getChildren: function(options, data){
		var onSuccess = function(data) {
			this.children = data.map(function(d){
				d.options.siteId = siteId;
				return d;
			});
			this.callChain();
		}.bind(this);
		if (this.data.isLeaf) {
			onSuccess([]);
			return this;
		}

		options = options || {};
		var siteId = this.setSiteId(options.siteId);
		if(!$chk(siteId)) {
			var msg = 'Error: you must supply a site id for category lookups.';
			dbug.log(msg);
			this.fireEvent('onError', msg);
			return null;
		} else if(this.data.id) {
			var util = new CNETAPI.Utils[this.options.type]($merge({
					instantiateResults: true,
					resultClass: CNETAPI.Category,
					applicationName: this.app.applicationName
				}, options)).addEvent('onSuccess', onSuccess);
			util.getChildren(this.data.id, $merge(this.options.extraLookupData, data||{}));
			return this;
		} else {
			return null;
		}
		return this;
	}
});

/*	Class: CNETAPI.NewsStory
		Extends <CNETAPI.Object> for type NewsStory	*/

CNETAPI.NewsStory = CNETAPI.Object.extend({
	options: {
		type: "NewsStory"
	}
});

/*	Class: CNETAPI.NewsGallery
		Extends <CNETAPI.Object> for type NewsGallery	*/

CNETAPI.NewsGallery = CNETAPI.Object.extend({
	options: {
		type: "NewsGallery"
	}
});


CNETAPI.Utils.SearchPaths = {
	TechProduct: "/techProductSearch",
	NewsGallery: "/newsGallerySearch",
	NewsStory: "/newsStorySearch",
	SoftwareProduct: "/softwareProductSearch"
};


// Individual Implementations for each API Request type
/*	Class: CNETAPI.Utils.TechProduct
		Contains methods for getting tech products from the CNET API.
	*/
CNETAPI.Utils.TechProduct = CNETAPI.Utils.Base.extend({
		options: {
			resultClass: CNETAPI.TechProduct,
			instantiateResults: true,
			searchPath: CNETAPI.Utils.SearchPaths['TechProduct']
		},
/*	Property: search
		Retrieves a list of items based on a search string.
		
		Arguments:
		queryTerm - (string) required; the query to search on
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain query, partKey, and view.
	*/
		search : function(queryTerm, data){
			return this.request($merge({query: queryTerm}, data), this.options.searchPath, "CNETResponse.TechProducts.TechProduct");
		},
/*	Property: get
		Gets an individual tech product from the CNET API.
		
		Arguments:
		pid - (integer) the product id to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain productId, partKey, and view.
	*/
		get : function(id, data){
				return this.request($merge({productId: id}, data), "/techProduct", "CNETResponse.TechProduct");
		}
});

/*	Class: CNETAPI.Utils.SoftwareProduct
		Contains methods for getting software products from the CNET API.
	*/
CNETAPI.Utils.SoftwareProduct = CNETAPI.Utils.Base.extend({
		options: {
			resultClass: CNETAPI.SoftwareProduct,
			instantiateResults: true,
			searchPath: CNETAPI.Utils.SearchPaths['SoftwareProduct']
		},
/*	Property: search
		Retrieves a list of items based on a search string.
		
		Arguments:
		queryTerm - (string) required; the query to search on
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain query, partKey, and view.
	*/
		search : function(queryTerm, data){
				return this.request($merge({query: queryTerm}, data), this.options.searchPath, "CNETResponse.SoftwareProducts.SoftwareProduct");
		},
/*	Property: getSet
		Gets an individual software product *set* from the CNET API.
		
		Arguments:
		id - (integer) the product id of the set to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain productSetId, partKey, and view.
	*/
		getSet : function(id, data){
				return this.request($merge({productSetId: id}, data), "/softwareProduct", "CNETResponse.SoftwareProduct");
		},
/*	Property: get
		Gets an individual tech product from the CNET API.
		
		Arguments:
		pid - (integer) the product id to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain productId, partKey, and view.
	*/
		get: function(id, data) {
				return this.request($merge({productId: id}, data), "/softwareProduct", "CNETResponse.SoftwareProduct");
		}
});

/*	Class: CNETAPI.Utils.NewsStory
		Contains methods for getting news stories from the CNET API.
	*/
CNETAPI.Utils.NewsStory = CNETAPI.Utils.Base.extend({
		options: {
			resultClass: CNETAPI.NewsStory,
			instantiateResults: true,
			searchPath: CNETAPI.Utils.SearchPaths['NewsStory']
		},
/*	Property: search
		Retrieves a list of items based on a search string.
		
		Arguments:
		queryTerm - (string) required; the query to search on
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain query, partKey, and view.
	*/
		search : function(queryTerm, data){
			return this.request($merge({query: queryTerm}, data), this.options.searchPath, "CNETResponse.NewsStories.NewsStory");
		},
/*	Property: get
		Gets an individual news story from the CNET API.
		
		Arguments:
		id - (integer) the story id to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain storyId, partKey, and view.
	*/
		get: function(id, data){
			return this.request($merge({storyId: id}, data), this.options.searchPath, "CNETResponse.NewsStory");
		}
});

/*	Class: CNETAPI.Utils.NewsGallery
		Contains methods for getting news stories from the CNET API.
	*/
CNETAPI.Utils.NewsGallery = CNETAPI.Utils.Base.extend({
		options: {
			resultClass: CNETAPI.NewsGallery,
			instantiateResults: true,
			searchPath: CNETAPI.Utils.SearchPaths['NewsGallery']
		},
/*	Property: search
		Retrieves a list of items based on a search string.
		
		Arguments:
		queryTerm - (string) required; the query to search on
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain query, partKey, and view.
	*/
		search : function(queryTerm, data){
			return this.request($merge({query: queryTerm}, data), this.options.searchPath, "CNETResponse.NewsStories.NewsStory");
		},
/*	Property: get
		Gets an individual news gallery from the CNET API.
		
		Arguments:
		id - (integer) the gallery id to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain galleryId, partKey, and view.
	*/
		get: function(id, data){
			return this.request($merge({galleryId: id}, data), this.options.searchPath, "CNETResponse.NewsStory");
		}
});

/*	Class: CNETAPI.Utils.Category
		Contains methods for getting catgories from the CNET API.
		
		Note:
		For Categories, you must either pass in a siteId as an option on instantiation or pass in siteId in the data object on requests.

		Options:
		Parent options - everything in <CNETAPI.Utils.Base>
		siteId - (integer) required site id for category selection.
	*/
CNETAPI.Utils.Category = CNETAPI.Utils.Base.extend({
		options: {
			resultClass: CNETAPI.Category,
			instantiateResults: true,
			siteId: null,
			searchPath: CNETAPI.Utils.SearchPaths['TechProduct']
		},
		packer: function(results){
			results = this.parent(results);
			return results.map(function(cat){
				cat.options.siteId = this.options.siteId;
				return cat;
			}, this);
		},
/*	Property: get
		Gets an individual category from the CNET API.
		
		Arguments:
		id - (integer) the category id to retrieve
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain categoryId, partKey, and view.
	*/
		get: function(id, data){
			data = data||{};
			data.siteId = data.siteId || this.options.siteId;
			if(!$chk(data.siteId)) {
				dbug.log("You must supply a site id for category lookups");
				this.throwErr("You must supply a site id for category lookups");
			}
			this.options.siteId = data.siteId;
			return this.request($merge({categoryId: id}, data), "/category", "CNETResponse.Category");
		},
/*	Property: getChildren
		Gets the children of a category from the CNET API.
		
		Arguments:
		id - (integer) the id of the parent category to retrieve its children
		data - (object) optional data passed on to JsonP.options.data. The data object will already contain categoryId, partKey, and view.
	*/
		getChildren: function(id, data){
			data = data||{};
			data.siteId = data.siteId || this.options.siteId;
			if(!$chk(data.siteId)) {
				dbug.log("You must supply a site id for category lookups");
				this.throwErr("You must supply a site id for category lookups");
			}
			return this.request($chk(id)?$merge({categoryId: id}, data):data, "/childCategories", "CNETResponse.ChildCategories.Category");
		},
/*	Property: search
		Search for category by term.
		
		Arguments:
		queryTerm - (string) the query to search on
		type - (string) either TechProduct, SoftwareProduct, NewsStory, or NewsGallery; see below
		data - (object)  optional data passed on to JsonP.options.data. The data object will already contain results: 1, iod: relatedCast, and the query term.

		Note:
		Currently this search *only works for TechProducts*.	*/
		search : function(queryTerm, type, data){
			data = $merge({
				results: 1,
				iod:'relatedCats'
			}, data);
			return this.request($merge({query: queryTerm}, data), type||this.options.searchPath, "CNETResponse.RelatedCategories");
		}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/CNETAPI/CNETAPI.js,v $
$Log: CNETAPI.js,v $
Revision 1.13  2007/10/18 21:43:35  newtona
updating components that reference images and assets at www.cnet.com to allow for easy over-writing for that source url

Revision 1.12  2007/10/18 17:40:28  newtona
Adding getSet to CNETAPI.SoftwareProduct

Revision 1.11  2007/10/18 17:34:00  newtona
tweaking CNETAPI.Object instantiation
fixing a typo in the dl product picker

Revision 1.10  2007/10/18 00:53:09  newtona
adding error handler logic for CNETAPI.Object

Revision 1.9  2007/10/15 20:42:37  newtona
perhaps the last cnetapi docs update?

Revision 1.8  2007/10/15 20:40:48  newtona
still more cnetapi docs

Revision 1.7  2007/10/15 20:39:38  newtona
more cnetapi docs

Revision 1.6  2007/10/15 20:37:28  newtona
adding docs to cnetapi

Revision 1.5  2007/10/15 18:10:04  newtona
cleaning up javascript warnings in CNETAPI.js

Revision 1.4  2007/10/15 18:06:22  newtona
missed one

Revision 1.3  2007/10/15 18:03:43  newtona
cleaning up syntax in CNETAPI. semicolons and whatnot

Revision 1.2  2007/10/11 00:56:13  newtona
adding ontology picklet to redball common full
adding search to categories in CNETAPI.js
tweaking preview layout in download.product.picker
new file: ontology picklet

Revision 1.1  2007/10/05 20:59:13  newtona
hey, new files!
CNETAPI - Hunter's first work on an API handler
CNETAPI.Category.Browser.js - this is still very rough and not ready for primetime.
ObjectBrowser.js - also might have a few quirks; this is a tree browser for objects (kinda like in firebug)
element.position.js - fixed an issue with positioning.


*/
