/*	Script: date.js
		Extends the Date native to include more powerful parsing and formatting functions.
		
		Authors:
		Nicholas Barthelemy - https://svn.nbarthelemy.com/date-js/
		Harald Kirshner - mail [at] digitarald.de ; http://digitarald.de
		Aaron Newton - aaron [dot] newton [at] cnet [dot] com

		License:
		MIT-style license
		
		Class: Date
			*/
$native(Date);

Date.extend({
/*	Property: clone
		Returns a copy of the date.
	*/
	clone: function() {
		return new Date(this.getTime());
	},
/*	Property: increment
		Increments a value in the date.
		
		Arguments:
		interval - (string) "day", "month", etc. (optional; defaults to 'day')
		times - (integer) the number of times to increment (optional; defaults to 1)
		
		Example:
		>new Date().increment('day', 4); //four days from now
	*/
	increment: function(interval, times) {
		return this.multiply(interval, times);
	},
/*	Property: decrement
		Decrements a value in the date. See <increment>.	*/
	decrement: function(interval, times) { //definitely, incremenet with third param.
		return this.multiply(interval, times, false);
	},
	multiply: function(interval, times, increment){
		interval = interval || 'day';
		times = $pick(times, 1);
		increment = $pick(increment, true);
		var multiplier = increment?1:-1
		
		var month = this.format("%m").toInt()-1;
		var year = this.format("%Y").toInt();
		var time = this.getTime();
		var offset = 0;
		switch (interval) {
				case 'year':
					times.times(function(val) {
						if (Date.isLeapYear(year+val) && month > 1 && multiplier > 0) val++;
						if (Date.isLeapYear(year+val) && month <= 1 && multiplier < 0) val--;
						offset += Date.$units.year(year+val);
					});
					break;
				case 'month':
					times.times(function(val){
						var mo = month+(val*multiplier);
						var yr = year;
						if (mo < 0) {
							yr--;
							mo = 12+mo;
						}
						if (mo > 11 || mo < 0) {
							yr += (mo/12).toInt()*multiplier;
							mo = mo%12;
						}
						offset += Date.$units.month(mo, yr);
					});
					break
				default:
					offset = Date.$units[interval]()*times;
					break;
		}
		this.setTime(time+(offset*multiplier));
		return this;
	},
/*	Property: isLeapYear
		Returns true if the date is for a leap year.
	*/
	isLeapYear: function() {
		return Date.isLeapYear(this.getYear());
	},
/*	Property: clearTime
		Sets the hours, minutes, seconds, and milliseconds to zero.
	*/
	clearTime: function() {
		this.setHours(0);
		this.setMinutes(0);
		this.setSeconds(0);
		this.setMilliseconds(0);
		return this;
	},
/*	Property: diff
		Compares two dates.
		
		Arguments:
		d - (date) the other date to compare this one to.
		resolution - (string; optional) how fine a comparision to make; 'day', 'month', etc. defaults to 'day'

	*/
	diff: function(d, resolution) {
		resolution = resolution || 'day';
		if($type(d) == 'string') d = Date.parse(d);
		switch (resolution) {
			case 'year':
				return d.format("%Y").toInt() - this.format("%Y").toInt();
				break;
			case 'month':
				var months = (d.format("%Y").toInt() - this.format("%Y").toInt())*12;
				return months + d.format("%m").toInt() - this.format("%m").toInt();
				break;
			default:
				var diff = d.getTime() - this.getTime();
				if (diff < 0 && Date.$units[resolution]() > (-1*(diff))) return 0;
				else if (diff >= 0 && diff < Date.$units[resolution]()) return 0;
				return ((d.getTime() - this.getTime()) / Date.$units[resolution]()).round();
		}
	},	
	
/*	Property: getTimezone
		Returns the time zone for the date. Example: "GMT".
	*/
	getTimezone: function() {
		return this.toString()
			.replace(/^.*? ([A-Z]{3}).[0-9]{4}.*$/, '$1')
			.replace(/^.*?\(([A-Z])[a-z]+ ([A-Z])[a-z]+ ([A-Z])[a-z]+\)$/, '$1$2$3');
	},
/*	Property: getGMTOffset
		Returns the offset to GMT *as a string*. Example: "-0800".
	*/
	getGMTOffset: function() {
		var off = this.getTimezoneOffset();
		return ((off > 0) ? '-' : '+')
			+ Math.floor(Math.abs(off) / 60).zeroise(2)
			+ (off % 60).zeroise(2);
	},
/*	Property: parse
		Parses a string to a date. In the examples below, parsing works with dates using /, -, or . (12.31.2007, 12-31-2007, 12/31/2007).
		
		Example:
		(start code)
		Date.parse('10/12/1982') = "Tue Oct 12 1982 11:53:25 GMT-0700 (Pacific Daylight Time)"
		Date.parse('10/12/1982 10:45pm') = "Tue Oct 12 1982 10:45:25 GMT-0700 (Pacific Daylight Time)"
		(end)
		
		Note:
		More parsers are available if you include date.extras.js.
	*/
	parse: function(str) {
		this.setTime(Date.parse(str));
		return this;
	},
/*	Property: format
		Outputs the date into a specific format.
		
		Arguments:
		f - (string) a string format for the output. Use the keys below with percent signs to get a desired output. See example below. Defaults to "%x %X", which renders "12/31/2007 03:45PM"

		Keys:
		a - short day ("Mon", "Tue")
		A - full day ("Monday")
		b - short month ("Jan", "Feb")
		B - full month ("Janurary")
		c - the full date to string ("Mon Dec 10 2007 14:35:42 GMT-0800 (Pacific Standard Time)"; same as .toString() method.
		d - the date to two digits (01, 05, etc)
		H - the hour to two digits in military time (24 hr mode) (01, 11, 14, etc)
		I - the hour in 12 hour time (01, 11, 2, etc)
		j - the day of the year to three digits (001 is Jan 1st)
		m - the numerical month to two digits (01 is Jan, 12 is Dec)
		M - the minuts to two digits (01, 40, 59)
		p - 'AM' or 'PM'
		S - the seconds to two digits (01, 40, 59)
		U - the week to two digits (01 is the week of Jan 1, 52 is the week of Dec 31)
		W - not yet supported
		w - the numerical day of the week, one digit (0 is Sunday, 1 is Monday)
		x - returns the format %m/%d/%Y (12/10/2007)
		X - returns %I:%M%p (02:45PM)
		y - the short year (to digits; "07")
		Y - the four digit year
		T - the GMT offset ("-0800")
		Z - the time zone ("GMT")
		% - returns % (example: %y%% = 07%)
		
		Shortcuts:
		These keys are NOT preceded by the percent sign.
		
		db - "%Y-%m-%d %H:%M:%S",
		compact - "%Y%m%dT%H%M%S",
		iso8601 - "%Y-%m-%dT%H:%M:%S%T",
		rfc822 - "%a, %d %b %Y %H:%M:%S %Z",
		short - "%d %b %H:%M",
		long - "%B %d, %Y %H:%M"
		
		Example:
		>new Date().format("db"); //"2007-12-10 15:01:53"
	*/
	format: function(f) {
		f = f || "%x %X";
		if (!this.valueOf()) return 'invalid date';
		//replace short-hand with actual format
		if (Date.$formats[f.toLowerCase()]) f = Date.$formats[f.toLowerCase()];
		var d = this;
		return f.replace(/\%([aAbBcdHIjmMpSUWwxXyYTZ])/g,
			function($1, $2) {
				switch ($2) {
					case 'a': return Date.$days[d.getDay()].substr(0, 3);
					case 'A': return Date.$days[d.getDay()];
					case 'b': return Date.$months[d.getMonth()].substr(0, 3);
					case 'B': return Date.$months[d.getMonth()];
					case 'c': return d.toString();
					case 'd': return d.getDate().zeroise(2);
					case 'H': return d.getHours().zeroise(2);
					case 'I': return ((d.getHours() % 12) || 12).zeroise(2);
					case 'j': return d.getDayOfYear().zeroise(3);
					case 'm': return (d.getMonth() + 1).zeroise(2);
					case 'M': return d.getMinutes().zeroise(2);
					case 'p': return d.getHours() < 12 ? 'AM' : 'PM';
					case 'S': return d.getSeconds().zeroise(2);
					case 'U': return d.getWeek().zeroise(2);
					case 'W': throw new Error('%W is not supported yet');
					case 'w': return d.getDay();
					case 'x': return d.format('%m/%d/%Y');
					case 'X': return d.format('%I:%M%p');
					case 'y': return d.getFullYear().toString().substr(2);
					case 'Y': return d.getFullYear();
					case 'T': return d.getGMTOffset();
					case 'Z': return d.getTimezone();
					case '%': return '%';
				}
				return $2;
			}
		);
	},
	setAMPM: function(ampm){
		ampm = ampm.toUpperCase();
		if (this.format("%H").toInt() > 11 && ampm == "AM") 
			return this.decrement('hour', 12);
		else if (this.format("%H").toInt() < 12 && ampm == "PM")
			return this.increment('hour', 12);
		return this;
	}
});

Date.prototype.compare = Date.prototype.diff;
Date.prototype.strftime = Date.prototype.format;

Date.$nativeParse = Date.parse;

$extend(Date, {
	$months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
	$days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
	$daysInMonth: function(monthIndex, year) {
		if (Date.isLeapYear(year.toInt()) && monthIndex === 1) return 29;
		return [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][monthIndex];
	},
	$epoch: -1,
	$era: -2,
	$units: {
		ms: function(){return 1},
		second: function(){return 1000},
		minute: function(){return 60000},
		hour: function(){return 3600000},
		day: function(){return 86400000},
		week: function(){return 608400000},
		month: function(monthIndex, year) {
			var d = new Date();
			return Date.$daysInMonth($pick(monthIndex,d.format("%m").toInt()), $pick(year,d.format("%Y").toInt())) * 86400000;
		},
		year: function(year){
			year = year || new Date().format("%Y").toInt();
			return Date.isLeapYear(year.toInt())?31622400000:31536000000;
		}
	},
	$formats: {
		db: '%Y-%m-%d %H:%M:%S',
		iso8601: '%Y-%m-%dT%H:%M:%S%T',
		rfc822: '%a, %d %b %Y %H:%M:%S %Z',
		'short': '%d %b %H:%M',
		'long': '%B %d, %Y %H:%M'
	},
	
	isLeapYear: function(year) {
		return (((year % 4) === 0) && ((year % 1000) !== 0) || ((year % 4000) === 0));
	},

	parseUTC: function(value){
	  var localDate = new Date(value);
	  var utcSeconds = Date.UTC(localDate.getFullYear(), localDate.getMonth(),
		localDate.getDate(), localDate.getHours(), localDate.getMinutes(), localDate.getSeconds())
	  return new Date(utcSeconds);
	},
	

	parse: function(from) {
		var type = $type(from);
		if (type == 'number') return new Date(str);
		if (type != 'string') return from;
		if (!from.length) return null;
		for (var i = 0, j = Date.$parsePatterns.length; i < j; i++) {
			var r = Date.$parsePatterns[i].re.exec(from);
			if (r) {
				try {
					return Date.$parsePatterns[i].handler(r);
				} catch(e) {
					dbug.log('date parse error: ', e);
					return null;
				}
			}
		}
		return new Date(Date.$nativeParse(from));
	},

	parseMonth: function(month, num) {
		var ret = -1;
		switch ($type(month)) {
			case 'object':
				ret = Date.$months[month.getMonth()];
				break;
			case 'number':
				ret = Date.$months[month - 1] || false;
				if (!ret) throw new Error('Invalid month index value must be between 1 and 12:' + index);
				break;
			case 'string':
				var match = Date.$months.filter(function(name) {
					return this.test(name);
				}, new RegExp('^' + month, 'i'));
				if (!match.length) throw new Error('Invalid month string');
				if (match.length > 1) throw new Error('Ambiguous month');
				ret = match[0];
		}
		return (num) ? Date.$months.indexOf(ret) : ret;
	},

	parseDay: function(day, num) {
		var ret = -1;
		switch ($type(day)) {
			case 'number':
				ret = Date.$days[day - 1] || false;
				if (!ret) throw new Error('Invalid day index value must be between 1 and 7');
				break;
			case 'string':
				var match = Date.$days.filter(function(name) {
					return this.test(name);
				}, new RegExp('^' + day, 'i'));
				if (!match.length) throw new Error('Invalid day string');
				if (match.length > 1) throw new Error('Ambiguous day');
				ret = match[0];
		}
		return (num) ? Date.$days.indexOf(ret) : ret;
	},
	
	fixY2K: function(d){
		if (!isNaN(d)) {
			var newDate = new Date(d);
			if (newDate.getFullYear() < 2000 && d.toString().indexOf(newDate.getFullYear()) < 0) {
				newDate.increment('year', 100);
			}
			return newDate;
		} else return d;
	},

	$parsePatterns: [
		{
			//"12.31.08", "12-31-08", "12/31/08", "12.31.2008", "12-31-2008", "12/31/2008"
			re: /^(\d{1,2})[\.\-\/](\d{1,2})[\.\-\/](\d{2,4})$/,
			handler: function(bits){
				var d = new Date();
				d.setYear(bits[3]);
				d.setMonth(bits[1].toInt() - 1, bits[2].toInt());
				return Date.fixY2K(d);
			}
		},
		//"12.31.08", "12-31-08", "12/31/08", "12.31.2008", "12-31-2008", "12/31/2008"
		//above plus "10:45pm" ex: 12.31.08 10:45pm
		{
			re: /^(\d{1,2})[\.\-\/](\d{1,2})[\.\-\/](\d{2,4})\s(\d{1,2}):(\d{1,2})(\w{2})$/,
			handler: function(bits){
				var d = new Date();
				d.setYear(bits[3]);
				d.setMonth(bits[1] - 1);
				d.setDate(bits[2]);
				d.setHours(bits[4]);
				d.setMinutes(bits[5]);
				d.setAMPM(bits[6]);
				return Date.fixY2K(d);
			}
		}
	]
});

/*	Class: Number
		Extends the native Number class.
	*/

Number.extend({
/*	Property: zeroise
		Returns a value with as many digits as specified.
		
		Arguments:
		length - (integer) the number of digits.
		
		Example:
		>(12).zeroise(3); //012
			*/
	zeroise: function(length) {
		return String(this).zeroise(length);
	}

});

/*	Class: String
		Extends the native String Class.
	*/
String.extend({
/*	Property: repeat
		Repeats a string as many times as specified.
		
		Arguments:
		times - (integer) the number of times to repeat the string.
		
		Example:
		>"foo".repeat(3); //returns "foofoofoo"
	*/
	repeat: function(times) {
		var ret = [];
		for (var i = 0; i < times; i++) ret.push(this);
		return ret.join('');
	},
/*	Property: zeroise
		Returns a value with as many digits as specified.
		
		Arguments:
		length - (integer) the number of digits.
		
		Example:
		>"12".zeroise(3); //"012"
			*/
	zeroise: function(length) {
		return '0'.repeat(length - this.length) + this;
	}

});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/mootools.extended/Native/date.js,v $
$Log: date.js,v $
Revision 1.5  2008/01/12 01:48:54  newtona
fixing some of the date math methods
added some error checking to Waiter and fixed a bug with the ajax integration

Revision 1.4  2008/01/10 22:49:37  newtona
Fixed a typo in setAssetHref.js
OverText repositioning bug fixed
Fixed an issue with date picker that forced you to pass in options
Fixing leap year issues in date.js and date.extras.js

Revision 1.3  2008/01/07 20:02:38  newtona
tweaking date parsing a bit

Revision 1.2  2008/01/07 19:39:10  newtona
restoring handle option in stickyWinHTML
date.js: fixed y2k method
date.picker.js: fixed popup location bug
form.validator: uses date.js if it's present

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
