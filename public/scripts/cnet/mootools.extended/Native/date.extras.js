/*	Script: date.extras.js
		Extends the Date native to include more powerful parsing and formatting functions; this is a further extention and depends on date.js.
		
		Authors:
		Nicholas Barthelemy - https://svn.nbarthelemy.com/date-js/
		Harald Kirshner - mail [at] digitarald.de ; http://digitarald.de
		Aaron Newton - aaron [dot] newton [at] cnet [dot] com

		License:
		MIT-style license
		
		Class: Date
		
		Property: parse
		Parses a string to a date. In the examples below, parsing works with dates using /, -, or . (12.31.2007, 12-31-2007, 12/31/2007).
		
		Note: this extends the parsers available in date.js.
		
		Examples:
		(start code)
		Date.parse('2007-06-08 16:34:52') = "Fri Jun 08 2007 09:34:52 GMT-0700 (Pacific Daylight Time)"
		Date.parse('2007-06-08T16:34:52+0200') = "Fri Jun 08 2007 07:34:52 GMT-0700 (Pacific Daylight Time)"
		Date.parse('today') = "Mon Dec 10 2007 11:53:25 GMT-0800 (Pacific Standard Time)"
		Date.parse('tomorrow') = "Tue Dec 11 2007 11:53:25 GMT-0800 (Pacific Standard Time)"
		Date.parse('yesterday') = "Sun Dec 09 2007 11:53:25 GMT-0800 (Pacific Standard Time)"
		Date.parse('next monday') = "Mon Dec 17 2007 11:53:25 GMT-0800 (Pacific Standard Time)"
		Date.parse('1st') = "Sat Dec 01 2007 11:53:25 GMT-0800 (Pacific Standard Time)"
		Date.parse('14th October') = "Sun Oct 14 2007 11:53:25 GMT-0700 (Pacific Daylight Time)"
		Date.parse('24th May, 2007') = "Thu May 24 2007 11:53:25 GMT-0700 (Pacific Daylight Time)"
		Date.parse('May 3rd 2006') = "Wed May 03 2006 11:53:25 GMT-0700 (Pacific Daylight Time)"
		(end)		
			*/
Date.extend({
/*	Property: timeAgoInWords
		Returns the duration of time between the date and now.
		
		Example:
		(start code)
		var example = new Date();
		example.timeAgoInWords(); //less than a minute ago
		example.decrement('hour');
		example.timeAgoInWords(); //about an hour ago
		(end)
	*/
	timeAgoInWords: function(){
		var relative_to = (arguments.length > 0) ? arguments[1] : new Date();
		return Date.distanceOfTimeInWords(this, relative_to, arguments[2]);
	},
/*	Property: getOrdinal
		Returns the ordinal for the day ('th', 'st', 'nd', etc).
	*/
	getOrdinal: function() {
		var str = this.toString();
		var test = str.substr(-(Math.min(str.length, 2)));
		return (test > 3 && test < 21) ? 'th' : ['th', 'st', 'nd', 'rd', 'th'][Math.min(this % 10, 4)];
	},
/*	Property: getDayOfYear
		Returns the day of the year (i.e. for Dec. 10, you'll get 344 in a non-leap year).
	*/
	getDayOfYear: function() {
		return ((Date.UTC(this.getFullYear(), this.getMonth(), this.getDate() + 1, 0, 0, 0)
			- Date.UTC(this.getFullYear(), 0, 1, 0, 0, 0) ) / Date.$units.day());
	},
/*	Property: getLastDayOfMonth
		Returns the last day of the month (i.e., for Dec, you'll get 31).
	*/
	getLastDayOfMonth: function() {
		var ret = this.clone();
		ret.setMonth(ret.getMonth() + 1, 0);
		return ret.getDate();
	},
/*	Property: getWeek
		Returns the week of the year for the date (i.e. 1 - 52).
	*/
	getWeek: function() {
		var day = (new Date(this.getFullYear(), 0, 1)).getDate();
		return Math.round((this.getDayOfYear() + (day > 3 ? day - 4 : day + 3)) / 7);
	}
});

$extend(Date, {
// http://twitter.pbwiki.com/RelativeTimeScripts	
	distanceOfTimeInWords: function(fromTime, toTime, includeTime) {
		var delta = parseInt((toTime.getTime() - fromTime.getTime()) / 1000);
		if(delta < 60) {
			return 'less than a minute ago';
		} else if(delta < 120) {
			return 'about a minute ago';
		} else if(delta < (45*60)) {
			return (parseInt(delta / 60)).toString() + ' minutes ago';
		} else if(delta < (90*60)) {
			return 'about an hour ago';
		} else if(delta < (24*60*60)) {
			return 'about ' + (parseInt(delta / 3600)).toString() + ' hours ago';
		} else if(delta < (48*60*60)) {
			return '1 day ago';
		} else {
			var days = (parseInt(delta / 86400)).toString();
			if(days > 30) {
				var fmt  = '%B %d';
				if(toTime.getYear() != fromTime.getYear()) { fmt += ', %Y'; }
				if(includeTime) fmt += ' %I:%M %p';
				return fromTime.strftime(fmt);
			} else {
			return days + " days ago";
			}
		}
	}
});
Date.$parsePatterns.extend([
	{
		re: /^(\d{4})(?:-?(\d{2})(?:-?(\d{2})(?:[T ](\d{2})(?::?(\d{2})(?::?(\d{2})(?:\.(\d+))?)?)?(?:Z|(?:([-+])(\d{2})(?::?(\d{2}))?)?)?)?)?)?$/,
		handler: function(bits) {
			var offset = 0;
			var d = new Date(bits[1], 0, 1);
			if (bits[2]) d.setMonth(bits[2] - 1);
			if (bits[3]) d.setDate(bits[3]);
			if (bits[4]) d.setHours(bits[4]);
			if (bits[5]) d.setMinutes(bits[5]);
			if (bits[6]) d.setSeconds(bits[6]);
			if (bits[7]) d.setMilliseconds(('0.' + bits[7]).toInt() * 1000);
			if (bits[9]) {
				offset = (bits[9].toInt() * 60) + bits[10].toInt();
				offset *= ((bits[8] == '-') ? 1 : -1);
			}
			offset -= d.getTimezoneOffset();
			d.setTime((d * 1) + (offset * 60 * 1000).toInt())
			return d;
		}
	}, {
		re: /^tod/i,
		handler: function() {
			return new Date();
		}
	}, {
		re: /^tom/i,
		handler: function() {
			return new Date().increment();
		}
	}, {
		re: /^yes/i,
		handler: function() {
			return new Date().decrement();
		}
	}, {
		re: /^(\d{1,2})(st|nd|rd|th)?$/i,
		handler: function(bits) {
			var d = new Date();
			d.setDate(bits[1].toInt());
			return d;
		}
	}, {
		re: /^(\d{1,2})(?:st|nd|rd|th)? (\w+)$/i,
		handler: function(bits) {
			var d = new Date();
			d.setMonth(Date.parseMonth(bits[2], true), bits[1].toInt());
			return d;
		}
	}, {
		re: /^(\d{1,2})(?:st|nd|rd|th)? (\w+),? (\d{4})$/i,
		handler: function(bits) {
			var d = new Date();
			d.setMonth(Date.parseMonth(bits[2], true), bits[1].toInt());
			d.setYear(bits[3]);
			return d;
		}
	}, {
		re: /^(\w+) (\d{1,2})(?:st|nd|rd|th)?,? (\d{4})$/i,
		handler: function(bits) {
			var d = new Date();
			d.setMonth(Date.parseMonth(bits[1], true), bits[2].toInt());
			d.setYear(bits[3]);
			return d;
		}
	}, {
		re: /^next (\w+)$/i,
		handler: function(bits) {
			var d = new Date();
			var day = d.getDay();
			var newDay = Date.parseDay(bits[1], true);
			var addDays = newDay - day;
			if (newDay <= day) {
				addDays += 7;
			}
			d.setDate(d.getDate() + addDays);
			return d;
		}
	}, {
		re: /^last (\w+)$/i,
		handler: function(bits) {
			throw new Error('Not yet implemented');
		}
	}
]);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/date.extras.js,v $
$Log: date.extras.js,v $
Revision 1.3  2008/01/12 01:48:54  newtona
fixing some of the date math methods
added some error checking to Waiter and fixed a bug with the ajax integration

Revision 1.2  2008/01/10 22:49:37  newtona
Fixed a typo in setAssetHref.js
OverText repositioning bug fixed
Fixed an issue with date picker that forced you to pass in options
Fixing leap year issues in date.js and date.extras.js

Revision 1.1  2008/01/04 00:49:08  newtona
date.picker: rewrote class to make use of new native date.js
date.picker.plus: allows for time and date range options
stickyWin.default.layout now has a handle option
stickyWinFx now uses the handle reference in stickyWin.default.layout by default
fixed some Fx.Sort array link issues
added datepicker assets to setAssetHref
Waiter.js: new class
OverText.js: new class
Native: date and date.extras - extends the native Date object greatly
updated make mootools 1.11 redball.common.full.js.bat to include new files
fixed a few syntax issues (semi colons) with previous commits


*/
