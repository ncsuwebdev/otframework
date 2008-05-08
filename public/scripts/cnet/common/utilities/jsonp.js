/*	Script: jsonp.js
		Creates a Json request using a script tag include and handles the callbacks for you.
		
		Dependencies:
		Mootools - <Moo.js>, <Array.js>, <String.js>, <Function.js>, <Utility.js>, <Element.js>, <Common.js>, <Assets.js>
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Class: JsonP
		Creates a Json request using a script tag include and handles the callbacks for you.
		
		Arguments:
		url - the url to get the json data
		options - an object with key/value options
		
		Options:
		onComplete - (optional) function to execute when the data returns; it will be passed the data and the 
			instance of jsonp that requested it.
		callBackKey - (string) the key in the url that the server uses to wrap the Json results. 
				So, for example, if you used "callBackKey: 'callback'" then the server is expecting
				something like http://..../?q=search+term&callback=myFunction
				defaults to "callback". This must be defined correctly.
		queryString - (string, optional) additional query string values to append to the url
		data - (object, optional) additional key/value data to append to the url
		
		Example:
(start code)
new JsonP('http://api.cnet.com/restApi/v1.0/techProductSearch', {
	data: {
		partTag: 'mtvo',
		iod: 'hlPrice',
		iewType: 'json',
		results: '100',
		query: 'ipod'
	},
	onComplete: myFunction.bind(someObject)
}).request();
(end)

		The above example would generate this url:
(start code) http://api.cnet.com/restApi/v1.0/techProductSearch?partTag=mtvo&iod=hlPrice&viewType=json&results=100&query=ipod&callback=JsonP.requestors[0].handleResults&
(end)

		It would embed this script tag (in the head of the document) and, when it loaded, execute the "myFunction"
		callback defined.
	*/
var JsonP = new Class({
	options: {
		onComplete: Class.empty,
		callBackKey: "callback",
		queryString: "",
		data: {},
		timeout: 5000,
		retries: 0
	},
	initialize: function(url, options){
		this.setOptions(options);
		this.url = this.makeUrl(url).url;
		this.fired = false;
		this.scripts = [];
		this.requests = 0;
		this.triesRemaining = [];
	},
/*	Property: request
		Executes the Json request.
	*/
	request: function(url, requestIndex){
		var u = this.makeUrl(url);
		if(!$chk(requestIndex)) {
			requestIndex = this.requests;
			this.requests++;
		}
		if(!$chk(this.triesRemaining[requestIndex])) this.triesRemaining[requestIndex] = this.options.retries;
		var remaining = this.triesRemaining[requestIndex]; //saving bytes
		dbug.log('retrieving by json script method: %s', u.url);
		var dl = (window.ie)?50:0; //for some reason, IE needs a moment here...
		(function(){
			var script = new Asset.javascript(u.url, {id: 'jsonp_'+u.index+'_'+requestIndex});
			this.fired = true;
			this.addEvent('onComplete', function(){
				try {script.remove();}catch(e){}
			}.bind(this));

			if(remaining) {
				(function(){
					this.triesRemaining[requestIndex] = remaining - 1;
					if(script.getParent() && remaining) {
						dbug.log('removing script (%o) and retrying: try: %s, remaining: %s', requestIndex, remaining);
						script.remove();
						this.request(url, requestIndex);
					}
				}).delay(this.options.timeout, this);
			}
		}.bind(this)).delay(dl);
		return this;
	},
	makeUrl: function(url){
		var index = (JsonP.requestors.contains(this))?
								JsonP.requestors.indexOf(this):
								JsonP.requestors.push(this) - 1;
		if(url) {
			var separator = (url.test('\\?'))?'&':'?';
			var jurl = url + separator + this.options.callBackKey + "=JsonP.requestors[" +
				index+"].handleResults";
			if(this.options.queryString) jurl += "&"+this.options.queryString;
			jurl += "&"+Object.toQueryString(this.options.data);
		} else var jurl = this.url;
		return {url: jurl, index: index};
	},
	handleResults: function(data){
		dbug.log('jsonp received: ', data);
		this.fireEvent('onComplete', [data, this]);
	}
});
JsonP.requestors = [];
JsonP.implement(new Options);
JsonP.implement(new Events);

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/utilities/jsonp.js,v $
$Log: jsonp.js,v $
Revision 1.15  2007/10/02 00:58:14  newtona
form.validator: fixed a bug where validation errors weren't removed under certain situations
tabswapper: .recall() no longer fails if no cookie name is set
jsonp: adding a 'return this'

Revision 1.14  2007/09/27 21:50:51  newtona
tagmaker: hide tooltips when the popup is hidden
jsonp: return the data AND the instance of jsonp oncomplete.

Revision 1.13  2007/08/30 17:52:11  newtona
removing some errant dbug lines; added one to jsonp

Revision 1.12  2007/08/28 20:38:51  newtona
doc update in jsonp
element.setPosition now accounts for fixed position relativeTo elements

Revision 1.11  2007/08/21 00:58:09  newtona
RTSS.History, RTSS.JsonP: added events for add, remove, empty, etc.
RTSS.js: most methods now return the remote class (XHR or JsonP)
UserHistory: add logic to announce actions (add, remove, etc.)
ProductToolbar: implemented place-holder compare function; removed some dbug lines
JsonP: tweaking retry logic

Revision 1.10  2007/08/20 21:14:31  newtona
tweaking jsonp timeout logic

Revision 1.9  2007/08/20 21:05:21  newtona
jsonp: added a timeout/retry system (defaults to not retry)

Revision 1.8  2007/08/20 18:11:49  newtona
Iframeshim: better handling for methods when the shim isn't ready
stickyWin: fixed a bug for the cssClass option in stickyWinHTML
html.table: push now returns the dom elements it creates
jsonp: fixed a bug; request no longer requires a url argument (my bad)
Fx.SmoothMove: just tidying up some syntax.
element.dimensions: updated getDimensions method for computing size of elements that are hidden; no longer clones element

Revision 1.7  2007/08/17 17:24:28  newtona
fixed a bug in jsonp; url is no longer a required argument for the request method

Revision 1.6  2007/08/15 01:03:32  newtona
Added more event info for Autocompleter.js
Slimbox no longer adds css to the page if there aren't any images found for the instance
Iframeshim now exits quietly if you try and position it before the dom is ready
jsonp now handles having more than one request open at a time
removed a console.log statement from window.cnet.js (shame on me for leaving it there)

Revision 1.5  2007/06/21 17:44:04  newtona
fixed a typo; same line was duplicated and I removed the errant one.

Revision 1.4  2007/03/05 19:30:46  newtona
added a short (50ms) delay for IE

Revision 1.3  2007/02/27 21:46:43  newtona
docs update; fixing references

Revision 1.2  2007/02/22 23:58:33  newtona
fixed a bug with the queryString option

Revision 1.1  2007/02/21 00:30:59  newtona
first commit


*/
