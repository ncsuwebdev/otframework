/*	Script: string.cnet.js
		These are mootools authored extensions designed to allow prototype.lite libraries run in this environment.

Dependancies:
	 mootools - <Moo.js>, <String.js>, <Array.js>

Author:
	Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
	

		Class: String
		This extends the <String> prototype.
	*/
String.extend({
/*	Property: stripTags
		Remove all html tags from a string.	*/
	stripTags: function() {
		return this.replace(/<\/?[^>]+>/gi, '');
  },
/*	Property: stripScripts
		Removes all script tags from an HTML string.
	*/
	stripScripts: function() {
		return this.replace(/<script[^>]*?>.*?<\/script>/img, '');
	},
/*	Property: evalScripts
		Executes scripts included in an HTML string.
	*/
	evalScripts: function() {
		var scripts = this.match(/<script[^>]*?>.*?<\/script>/g);
		if(scripts) scripts.each(function(script){
				eval(script.replace(/^<script[^>]*?>/, '').replace(/<\/script>$/, ''));
			});
	},
/*	Property: replaceAll
		Replaces all instances of a string with the specified value.
		
		Arguments:
		searchValue - the string you want to replace
		replaceValue - the string you want to insert in the searchValue's place
		regExOptions - defaults to "ig" but you can pass in your preference
		
		Example:
		>"I like cheese".replaceAll("cheese", "cookies");
		> > I like cookies
	*/
	replaceAll: function(searchValue, replaceValue, regExOptions) {
		return this.replace(new RegExp(searchValue, $pick(regExOptions,'gi')), replaceValue);
	},
/*	Property: urlEncode
		urlEncodes a string (if it is not already).
		
		Example:
		> "Mondays aren't that fun".urlEncode()
		> > Mondays%20aren%27t%20that%20fun
	*/
	urlEncode: function() {
		return (this.test('%'))?this:escape(this);
	},
/*	Property: parseQuery
		Turns a query string into an associative array of key/value pairs.
		
		Example:
(start code)
"this=that&what=something".parseQuery()
> { this: "that", what: "something" }

var values = "this=that&what=something".parseQuery();
> values.this > "that"
(end)
	*/

	parseQuery: function() {
		var vars = this.split(/[&;]/);
		var rs = {};
		if (vars.length) vars.each(function(val) {
			var keys = val.split('=');
			if (keys.length && keys.length == 2) rs[encodeURIComponent(keys[0])] = encodeURIComponent(keys[1]);
		});
		return rs;
	},
/*	Property: tidy
		Replaces common special characters with their ASCII counterparts (smart quotes, elipse characters, stuff from MS Word, etc.).
	*/
	tidy: function() {
		var txt = this.toString();
		$each({
			"[\xa0\u2002\u2003\u2009]": " ",
			"\xb7": "*",
			"[\u2018\u2019]": "'",
			"[\u201c\u201d]": '"',
			"\u2026": "...",
			"\u2013": "-",
			"\u2014": "--"
		}, function(value, key){
			txt = txt.replace(new RegExp(key, 'g'), value);
		});
		return txt;
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/string.cnet.js,v $
$Log: string.cnet.js,v $
Revision 1.3  2007/11/19 23:23:07  newtona
CNETAPI: added method "getMany" to all the CNETAPI.Utils.* classes so that you can get numerous items in one request.
ObjectBrowser: improved exclusion handling for child elements
jlogger.js, element.position.js: docs update
Fx.Sort: cleaning up tabbing
string.cnet: just reformatting the logic a little.

Revision 1.2  2007/07/17 20:38:44  newtona
Fx.SmoothShow - refactored the exploration of the element dimensions when hidden so that it isn't visible to the user
element.position - refactored to allow for more than just the previous 5 positions, now supports nine: all corners, all mid-points between those corners, and the center
string.cnet.js - fixed up the query string logic to decode values

Revision 1.1  2007/05/29 21:25:31  newtona
splitting up element.cnet.js (which had grown to be too unweildy); moving things into sub-directories

Revision 1.8  2007/03/16 00:23:24  newtona
added string.tidy and element.tidy

Revision 1.7  2007/03/09 20:14:47  newtona
strict javascript warnings cleaned up

Revision 1.6  2007/03/08 23:32:14  newtona
strict javascript warnings cleaned up

Revision 1.5  2007/02/06 18:14:01  newtona
re-implemented replaceAll because String.replace(new, old, "ig") doesn't work in IE. Ungh. IE.

Revision 1.4  2007/01/26 06:08:27  newtona
updated docs
refactored .replaceAll
removed dependency on Prototype.compatibility.js

Revision 1.3  2006/11/15 01:19:19  newtona
added String.parseQuery

Revision 1.2  2006/11/02 21:34:00  newtona
Added cvs footer


*/
