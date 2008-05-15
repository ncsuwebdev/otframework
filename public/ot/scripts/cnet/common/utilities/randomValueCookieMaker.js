/*	Script: randomValueCookieMaker.js
		This script assigns a user a cookie with a random value within a specified range; useful for a/b testing.
		
		Dependancies:
			 mootools - 	<Moo.js>, <String.js>, <Cookie.js>, <Common.js>, <Utilities.js>, <Function.js>
	
		Author:
			Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
		
		Class: RandomValueCookieMaker
		Assigns a user a cookie with a random value within a specified range; useful for a/b testing
		
		Arguments:
		options - the options object of key/value options

		Options:
		cookieName - (string, required) a unique name for the cookie.
		limit - (integer) the highest random number to generate; defaults to 10.
		days - (integer) how long to store the cookie; defaults to 999.
		domain - (string) the domain to assign to the cookie; optional.		
		
		Property:
		val - the value of the random cookie
		
		Example:
(start code)
var myRndTest = new RandomValueCookieMaker({
	cookieName: 'myRandomCookie', //a unique name for this cookie.
	limit: 99, //give me 0 through 99
	days: 1, //let's only save it for a day
	domain: 'cnet.com' //let's set it to cnet.com 
										 //so subdomains can get the cookie
});

if(myRndTest.val > 90) //only do this for 10% of users...
(end)
	*/

	var RandomValueCookieMaker = new Class({
		options: {
			cookieName: false,
			limit: 10,
			days: 999,
			domain: false
		},
		initialize: function(options) {
			this.setOptions(options);
			if(this.options.cookieName) this.verify();
			else {
				dbug.log('you must specify a cookie name.');
				return;
			}
		},
		verify: function() {
			this.val = Cookie.get(this.options.cookieName);
			if (!$chk(parseInt(this.val))) {
				this.val = this.makeRand();
				this.saveVal();
			}
		},
/*	Property: setVal
		Sets the cookie to a specified value.
		
		Arguments:
		val - (integer) the value to set the cookie to	*/
		saveVal: function(val) {
			this.val = $pick(val, this.val);
			if (this.options.domain) Cookie.set(this.options.cookieName, this.val, {duration:this.options.days, domain:this.options.domain});
			else Cookie.set(this.options.cookieName, this.val, this.options.days);
		},
		makeSeed: function() {
	     return ((new Date().getTime()*9301+49297) % 233280)/(233280.0);
		},
/*	Property: makeRand
		Returns a random number between 0 and the limit set in the options.	*/
		makeRand: function() {
	     return Math.ceil(this.makeSeed()*this.options.limit);
		}
	});
	RandomValueCookieMaker.implement(new Options);
	/*	legacy namespace	*/
	var randomValueCookieMaker = RandomValueCookieMaker;
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/utilities/randomValueCookieMaker.js,v $
$Log: randomValueCookieMaker.js,v $
Revision 1.8  2007/04/13 19:06:11  newtona
dependency update in the docs

Revision 1.7  2007/03/28 18:09:03  newtona
removing $type.isNumber dependencies

Revision 1.6  2007/03/20 19:23:25  newtona
fixing javascript strict warnings

Revision 1.5  2007/03/08 23:31:22  newtona
strict javascript warnings cleaned up
removed deprecated dbug loadtimers
dbug enables on debug.cookie()

Revision 1.4  2007/01/26 05:56:03  newtona
syntax update for mootools 1.0
docs update

Revision 1.3  2007/01/22 22:50:25  newtona
updated cookie.set syntax

Revision 1.2  2007/01/22 21:54:46  newtona
updated docs to require cookieName

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.4  2006/11/14 02:06:23  newtona
fixed some syntax bugs

Revision 1.3  2006/11/13 23:53:04  newtona
added cvs footer


*/
