/*	Script: date.picker.js
		Allows the user to enter a date in any popuplar format or choose from a calendar.
		
		Dependencies:
		mootools - <Moo.js>, <Utility.js>, <Common.js>, <Function.js>, <Element.js>, <Array.js>, <String.js>, <Event.js>
		cnet - <stickyWin.js> and all its dependencies
		optional - <Drag.Base.js>, <stickyWinFx.js>
		
		Authors:
		Paul Anderson
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Class: DatePicker
		Allows the user to enter a date in any popuplar format or choose from a calendar.
		
		Arguments:
		input - the id of a text input, or a reference to the element itself
		options - an object with key/value settings
		
		Options:
		calendarId - (string) the id of the calendar to show; defaults to "popupCalendar" + the date (so it’s unique)
		months - (array) the months of the year. Defaults to ["Janurary", "February", etc.]
		days - (array) the days of the week. Defaults to ["Su", "Mo", "Tu", etc.]
		stickyWinOptions - (object) options to pass along to the stickyWin popup object. Defaults to {position: 'bottomLeft', offset: {x:10, y:10}}
		stickyWinToUse - which <StickyWin> class to use (<StickyWin>, <StickyWinFx>, etc.)
		draggable: (boolean) whether or not the popup is draggable. Requires <Drag.Base.js>. Defautls to true (if <Drag.Base.js> is not present, the element won't be draggable, but it won't throw an error.
		dragOptions: (object) options to pass on to <Drag.Base>
		additionalShowLinks - (array) collection of dom elements (or ids) that should show the calendar for the input
		showOnInputFocus - (boolean) show the calendar when the input is focused. Defaults to true. If set to false, you must specify at least one object in additionalShowLinks if you want the calendar to be accessible. **NOTE: you can set this to false and specy no additional show links that this class will still auto-format date inputs for you**
		useDefaultCss - (boolean) use the default css described in this class. If false, you must define your own css. Defaults to true.
		hideCalendarOnPick - (boolean) hide the calendar when the user chooses a date. Defaults to true.
		onPick - function to execute when the user choose a date
		onShow - function to execute when the calendar appears
		onHide - function to execute when the calendar is hidden
		CSS:
		The calendar popup builds a table with all the dates and months and whatnot. You may style this table using the following descriptors:

		div.calendarHolder - the div containing the calendar table.
		table.datePicker - the table with the calendar values
		tr.dateNav - the row containing the forward, back, and close buttons, and the month name
		tr.dayNames - the row containing the names of the days of the week
		tr.dayRow - one of the rows containing actual dates
		td.today - the td that contains today's date
		td.selectedDate - the td that contains the user's selection
		td.otherMonthDate - tds that contain dates before or after the current selected month
		
		Autoformatting and Date format: 
		This class will take a user's input of a date value and convert it into MM/DD/YYYY. If the user inputs 01.02.03,
		this class will update it to 01/02/2003 on the blur event of the field. The same is true for 01.02.2003, 01/02/03,
		01 02 2003, 2003.02.01, and so on.
		
		If you need this class to return a different format, you can use <Class.implement> to create your own formatter. If
		javascript had a better date object, we wouldn't have to do it like this, but what are ya gonna do?
		
		Example:
(start code)
<input type="text" name="date" id="dateInput"> <img src="calendar.gif" id="calendarImg">
<script>
new DatePicker('dateInput', {
	additionalShowLinks: ['calendarImg'],
	showOnInputFocus: false
});
(end)
	*/
	var DatePicker = new Class({
		options: {
			baseHref: 'http://www.cnet.com/html/rb/assets/global/',
			defaultCss: 'div.calendarHolder{width:210px; height:182px; padding-left:8px; padding-top:1px; '+
				'background:url(%baseHref%/datePicker/calendar.back.png) no-repeat} '+
			  '* html div.calendarHolder {background:url(%baseHref%/datePicker/calendar.back.gif) no-repeat}'+
				'table.datePicker * {font-size:11px; line-height:16px;} '+
				'table.datePicker{margin:6px 0px 0px 0px; width:190px; padding:0px 5px 0px 5px} '+
				'table.datePicker td{cursor:pointer; text-align:center} '+
				'table.datePicker img.closebtn{margin-top:2px} '+
				'tr.dateNav{height:22px; margin-top:8px} '+
				'tr.dayNames td{color:#666; font-weight:bold; border-bottom:1px solid #ddd} '+
				'table.datePicker tr.dayRow td:hover{background:#ccc} '+
				'td.today{color:#bb0904} '+
				'td.otherMonthDate{border:1px solid #fff; color:#666; background:#f3f3f3} '+
				'td.selectedDate{border:1px solid #20397b; background:#dcddef}',
			fullDay: 86400000,
			calendarId: false,
			stickyWinOptions: {
				position: "bottomLeft",
				offset: {x:10, y:10},
				fadeDuration: 400
			},
			draggable: true,
			dragOptions: {},
			showOnInputFocus: true,
			useDefaultCss: true,
			hideCalendarOnPick: true,
			onPick: Class.empty,
			onShow: Class.empty,
			onHide: Class.empty
		},
			
		initialize: function(input, options){
			//gotta declare array options here to avoid inheritance corruption
			this.options.months = ["January","February","March","April","May","June","July",
														 "August","September","October","November","December"];
			this.options.days = ["Su","Mo","Tu","We","Th","Fr","Sa"];
			this.options.additionalShowLinks = [];

			var StickyWinToUse = (typeof StickyWinFx == "undefined")?StickyWin:StickyWinFx;
			this.setOptions({
				stickyWinToUse: StickyWinToUse
			}, options);
			if(!this.options.calendarId) this.options.calendarId = "popupCalendar" + new Date().getTime();
			this.input = $(input);
			if(this.options.useDefaultCss)this.writeCss();
			this.setUpObservers();
			this.getCalendar();
		},
		setUpObservers: function(){
			if (this.options.showOnInputFocus) this.input.addEvent('focus', this.show.bind(this));
			try {this.input.addEvent('blur', this.updateInput.bind(this));}catch(e){} //ie sometimes doesn't like this.
			this.options.additionalShowLinks.each(function(lnk){$(lnk).addEvent('click', this.show.bind(this))}, this);
		},
		writeCss: function(css) {
			css = $pick(css,this.options.defaultCss).replace("%baseHref%", this.options.baseHref, "g");
			window.addEvent('domready', function(){
				try {
					if(!$('datePickerStyle')) {
						var style = new Element('style').setProperty('id','datePickerStyle').injectInside($$('head')[0]);
						if (!style.setText.attempt(css, style)) style.appendText(css);
					}
				}catch(e){dbug.log('error: %s',e);}
			});
		},
/*	Property: updateInput
		Takes a given date and updates the input field with its value.
		
		Arguments:
		date - a date or a string that is parsable as a date (see <validDate>)
	*/
		updateInput: function(date){
			if(!$type(date) == "string" || (date && !date.getTime)) date = this.input.getValue();
			var dateStr = this.formatDate(this.validDate(date));
			if($type(dateStr) == "string") {
				this.input.value = dateStr;
				return dateStr;
			}
			return date;
		},
/*	Property: validDate
		Parses a string into a Date object and returns it.
		
		Arguments:
		val - (optional) the date to parse. a string or a date object. If no value is specified, the input 
			value will be used instead.
		
		Accepted formats:
		01.02.03, 01.02.2003, 01/02/03, 01 02 2003, 2003.02.01, and so on.
	*/		
		validDate: function(val) {
			val = $pick(val, this.input.getValue());
			val = val.replace(/^\s+|\s+$/g,"");
			var asDate = Date.parse(val);
			if (isNaN(asDate)) asDate = Date.parse(val.replace(/[^\w\s]/g,"/"));
			if (isNaN(asDate)) asDate = Date.parse(val.replace(/[^\w\s]/g,"/") + "/" + new Date().getFullYear());
			if (!isNaN(asDate)) {
				var newDate = new Date(asDate);
				if (newDate.getFullYear() < 2000 && val.indexOf(newDate.getFullYear()) < 0) {
					newDate.setFullYear(newDate.getFullYear() + 100);
				}
				return newDate;
			} else return asDate;
		},
/*	Property: formatDate
		formats a date object into MM/DD/YYYY.
		
		Arguments:
		date - (Date object) the date to format.
	*/
		formatDate: function (date) {
			try {
				// always "get" as UTC, without timezone, so there's no confusion over the calendar day
					var fd = ((date.getUTCMonth() < 9) ? "0" : "") + (date.getUTCMonth()+1) + "/";
					fd += ((date.getUTCDate() < 10) ? "0" : "") + date.getUTCDate() + "/";
					fd += date.getUTCFullYear();
					return fd;
			} catch(e){return date}
		},
		
		zeroHourGMT: function(date) {
			date.setTime(date.getTime() - date.getTime() % 86400000);
			return date;
		},
		
		getCalendar: function() {
			if(!this.calendar) {
				var cal = new Element("table").setProperties({
					'id': this.options.calendarId,
					'border':'0',
					'cellpadding':'0',
					'cellspacing':'0'
				});
				cal.addClass('datePicker');
		    $(cal.insertRow(0).insertCell(0)).appendText("x");
				for (var c=0;c<6;c++) $(cal.rows[0]).adopt(cal.rows[0].cells[0].cloneNode(true));
				for (var r=0;r<7;r++) $(cal.rows[0].parentNode).adopt(cal.rows[0].cloneNode(true));
				$(cal.rows[1]).addClass('dayNames');
				for (var r=2;r<8;r++) $(cal.rows[r]).addClass('dayRow');
				for (var d=0;d<7;d++) cal.rows[1].cells[d].firstChild.data = this.options.days[d];
				for (var t=6;t>3;t--) cal.rows[0].deleteCell(t);
				$(cal.rows[0]).addClass('dateNav');
				if(!window.ie6)cal.rows[0].cells[0].firstChild.data=String.fromCharCode(9668);
				else cal.rows[0].cells[0].firstChild.data="<";
				cal.rows[0].cells[1].colSpan=4;
				if(!window.ie6) cal.rows[0].cells[2].firstChild.data=String.fromCharCode(9658);
				else cal.rows[0].cells[2].firstChild.data=">";
				cal.rows[0].cells[3].firstChild.data=String.fromCharCode(215);
				$(cal.rows[0].cells[3].empty()).adopt(this.getCloseImg());
					//xb.adopt(xb.previousSibling);
				cal.addEvent('click', this.clickCalendar.bind(this));
				this.calendar = cal;
				this.container = new Element('div').adopt(cal).addClass('calendarHolder');
				//make stickywin
				this.options.stickyWinOptions.content = this.container;
				this.options.stickyWinOptions.showNow = false;
				this.options.stickyWinOptions.relativeTo = this.input;
				this.stickyWin = new this.options.stickyWinToUse(this.options.stickyWinOptions);
				if(this.options.draggable) {
					try {
						this.stickyWin.win.makeDraggable(Object.extend(this.options.dragOptions, {
							handle:cal.rows[0].cells[1],
							onDrag:function(){
								if(this.stickyWin.shim) this.stickyWin.shim.show.bind(this.stickyWin)
							}.bind(this)
						}));
						cal.rows[0].cells[1].setStyle('cursor', 'move');
					} catch(e) {}//drag isn't available
				}
			}
			return this.calendar;
		},
/*	Properties: getCloseImg
		Returns an img object to use for the close funciton.
		
		You can use <Class.implement> to redefine this so that it returns a dom element of your choosing.
		You will need to add your own call to <DatePicker.hide>.
		
		Arguments:
		url - the url to the image
	*/
		getCloseImg: function(url){
      url = url||this.options.baseHref + "/simple.error.popup/closebtn.gif";
			var closer = new Element("img").setProperty('src', url);
			closer.addEvents({
				'mouseover': function(){
					closer.src = closer.src.replace('.gif', '_over.gif');
				},
				'mouseout':function(){
					closer.src = closer.src.replace('_over.gif', '.gif');
				},
				'click': this.hide.bind(this)
			}).setStyles({
				width: '13px',
				height: '13px'
			}).addClass('closebtn');
			return closer;
		},
		
/*	Property: hide
		Hides the calendar popup.
	*/
		hide: function(){
			this.stickyWin.hide();
			this.fireEvent('onHide');
		},
/*	Property: show
		Shows the calendar popup. This will reposition the popup and display the date that the user has entered or today's date if they have not entered anything.
	*/
		show: function(){
	    this.today = this.zeroHourGMT(new Date());
			this.inputDate = new Date(this.updateInput());
	    this.refDate = isNaN(this.inputDate) ? this.today : this.zeroHourGMT(new Date(this.inputDate));
			this.getCalendar();
	    this.fillCalendar(this.refDate);
			this.stickyWin.show();
			this.fireEvent('onShow');
		},
		clickCalendar: function(e) {
			e = new Event(e);
			if (!e.target.firstChild || !e.target.firstChild.data) return;
			var val = e.target.firstChild.data;
			if (val.charCodeAt(0) > 9600 || val == "<" || val == ">") {
				var newRef = this.calendar.rows[2].cells[0].refDate - this.options.fullDay;
				if (val.charCodeAt(0) != 9668 && val != "<") newRef = this.calendar.rows[7].cells[6].refDate + this.options.fullDay;
				this.fillCalendar(new Date(newRef));
				return;
			}
			if (e.target.refDate) {
				var newDate = new Date(e.target.refDate);
				this.input.value = this.formatDate(newDate);
				/* trip onchange events in text field */
				this.input.fireEvent("change");
				this.input.fireEvent("blur");
				this.fireEvent('onPick');
				if(this.options.hideCalendarOnPick) this.hide();
			}
		},
		fillCalendar: function (forDate) {
			var startDate = new Date(forDate.getTime());
			startDate.setUTCDate(1);
			startDate.setTime(startDate.getTime() - (this.options.fullDay * startDate.getUTCDay()));
			this.calendar.rows[0].cells[1].firstChild.data = this.options.months[forDate.getUTCMonth()] + " " + forDate.getUTCFullYear();
			var atDate = startDate;
			this.calendar.getElements('td').each(function (el){
				el.removeClass('selectedDate').removeClass('otherMonthDate').removeClass('today');
			});
			for (var w=2; w<8; w++) for (var d=0; d<7; d++) {
				var td = this.calendar.rows[w].cells[d];
				td.firstChild.data = atDate.getUTCDate();
				td.refDate = atDate.getTime();
				if(atDate.getTime() == this.today.getTime()) td.addClass('today');
				if(atDate.getTime() == this.refDate.getTime()) td.addClass('selectedDate');
				if(atDate.getUTCMonth() != forDate.getUTCMonth()) td.addClass('otherMonthDate');
				atDate.setTime(atDate.getTime() + this.options.fullDay);
			}
		}
	});
/*	Note:
		DatePicker implements <Options> and <Events>.
	*/
	DatePicker.implement(new Options);
	DatePicker.implement(new Events);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/date.picker.js,v $
$Log: date.picker.js,v $
Revision 1.20  2007/11/29 19:00:57  newtona
- fixed an issue with StickyWin that could create inheritance issues because of a defect in $merge. this fixed an actual issue with DatePicker, specifically, having more than one on a page
- fixed an issue with the basehref in simple.error.popup

Revision 1.19  2007/10/25 00:17:25  newtona
tweaking date picker asset paths

Revision 1.18  2007/10/25 00:10:58  newtona
left in an extra comma...

Revision 1.17  2007/10/25 00:09:08  newtona
syntax tweak in date picker

Revision 1.16  2007/10/25 00:04:37  newtona
missed a comma

Revision 1.15  2007/10/25 00:02:39  newtona
fixing options references in DatePicker

Revision 1.14  2007/10/23 23:10:26  newtona
added mootools debugger to cvs
new file: setAssetHref.js; enables you to quickly set the location of image assets contained in the framework so you don't use CNET's versions, which can sometimes be slow.

Revision 1.13  2007/10/18 21:43:35  newtona
updating components that reference images and assets at www.cnet.com to allow for easy over-writing for that source url

Revision 1.12  2007/09/24 20:55:49  newtona
new file: StickyWin.Ajax - adds ajax support to all stickywin classes (creates new classes, just append .Ajax to any of the existing ones)
updated redball common full to include StickyWin.Ajax
date.picker, product.picker - updated syntax to use Element.empty
form.validator - now passes along the event object to the onFormValidate event so that the form submit event can be stopped if you like
popupdetails - added html response support; you can now return the html you wish to display rather than a json object; only applies to ajax. Also added a cache so that multiple requests are not made for the same url.
stickyWinHTML - ractored so that options are now, you know, *optional*
MooScroller - added support for width option for horizontal scrolling

Revision 1.11  2007/07/18 16:15:21  newtona
forgot to bind the style objects in the setText.attempt method...

Revision 1.10  2007/07/16 21:00:21  newtona
using Element.setText for all style injection methods (fixes IE6 problems)
moving Element.setText to element.legacy.js; this function is in Mootools 1.11, but if your environment is running 1.0 you'll need this.

Revision 1.9  2007/05/16 20:17:52  newtona
changing window.onDomReady to window.addEvent('domready'

Revision 1.8  2007/03/08 23:29:31  newtona
date picker: strict javascript warnings cleaned up
popup details strict javascript warnings cleaned up
product.picker: strict javascript warnings cleaned up, updating input now fires onchange event
confirmer: new file

Revision 1.7  2007/02/27 21:46:43  newtona
docs update; fixing references

Revision 1.6  2007/02/21 00:27:08  newtona
switched Class.create to Class.empty

Revision 1.5  2007/02/07 20:51:41  newtona
implemented Options class
implemented Events class
StickyWin now uses Element.position

Revision 1.4  2007/02/03 01:36:41  newtona
fixed a fireevent bug

Revision 1.3  2007/01/29 23:50:53  newtona
additional bug fixes and tweaks. stable now.

Revision 1.2  2007/01/27 01:51:36  newtona
numerous ie6 fixes.

Revision 1.1  2007/01/26 21:55:04  newtona
*** empty log message ***


*/
