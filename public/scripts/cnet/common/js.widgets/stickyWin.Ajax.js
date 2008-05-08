/*	Script: stickyWin.Ajax.js
		Adds ajax functionality to all the StickyWin classes.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		CNET - <StickyWin.js> and all its dependencies.
		Optional - <StickyWinFx.js>, <StickyWinFx.Drag.js>, <StickyWin.Modal.js>
		
		
		Class: StickyWin.Ajax
		Adds ajax functionality to the StickyWin class. See also <StickyWinFx.Ajax>, <StickyWinModal.Ajax>, and <StickyWinFxModal.Ajax>.
		
		Arguments:
		options - All the options in the relevant <StickyWin> class (<StickyWin>, <StickyWinFx>, etc) plus those listed below.
		
		Options:
		url - (string) the default url for the instance to hit for its content. See <update>
		XHRoptions - (object) options passed on in the ajax request; defaults to {method:'get'}.
		wrapWithStickyWinDefaultHTML - (boolean) if true, wraps the response in <stickyWinHTML>. defaults to false.
		caption - (string) if wrapping with <stickyWinHTML>, this caption will be used; defaults to empty string.
		stickyWinHTMLOptions - (object) if wrapping with <stickyWinHTML>, these options will be passed 
				along as the options to <stickyWinHTML>; defaults to an empty object.
		handleResponse - (function) handles the response from the server. Default will wrap the response
				html with <stickyWinHTML> if that option is enabled (which it isn't by default), then calls <StickyWin.setContent>
				and then <StickyWin.show>. This method is meant to be replaced with custom handlers if you want a different
				behavior (which is why it's an option).
	*/
var SWA = {
	options: {
		url: '',
		showNow: false,
		XHRoptions: {
			method: 'get'
		},
		wrapWithStickyWinDefaultHTML: false, 
		caption: '',
		stickyWinHTMLOptions:{},
		handleResponse: function(response){
			if(this.options.wrapWithStickyWinDefaultHTML) 
				response = stickyWinHTML(this.options.caption, response, this.options.stickyWinHTMLOptions);
			this.setContent(response);
			this.show();
		}
	},
	initialize: function(options){
		this.parent(options);
		this.createXHR();
	},
	createXHR: function(){
		var opt = $merge(this.options.XHRoptions, {
			onSuccess: this.options.handleResponse.bind(this)
		});
		this.XHR = new XHR(opt);
	},
	update: function(url){
		this.XHR.send(url||this.options.url);
		return this;
	}
};
try {	StickyWin.Ajax = StickyWin.extend(SWA); } catch(e){}
try {	StickyWinFx.Ajax = StickyWinFx.extend(SWA); } catch(e){}
try {	StickyWinModal.Ajax = StickyWinModal.extend(SWA); } catch(e){}
try {	StickyWinFxModal.Ajax = StickyWinFxModal.extend(SWA); } catch(e){}
/* do not edit below this line */	 
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/stickyWin.Ajax.js,v $
$Log: stickyWin.Ajax.js,v $
Revision 1.4  2007/09/24 22:10:19  newtona
fixed a bug in popupdetails;
StickyWin*.Ajax.update() now returns the instance (return this);

Revision 1.3  2007/09/24 21:28:05  newtona
fixed a typo in stickyWin.Ajax.js

Revision 1.2  2007/09/24 21:07:45  newtona
hey, semi-colons!

Revision 1.1  2007/09/24 20:55:49  newtona
new file: StickyWin.Ajax - adds ajax support to all stickywin classes (creates new classes, just append .Ajax to any of the existing ones)
updated redball common full to include StickyWin.Ajax
date.picker, product.picker - updated syntax to use Element.empty
form.validator - now passes along the event object to the onFormValidate event so that the form submit event can be stopped if you like
popupdetails - added html response support; you can now return the html you wish to display rather than a json object; only applies to ajax. Also added a cache so that multiple requests are not made for the same url.
stickyWinHTML - ractored so that options are now, you know, *optional*
MooScroller - added support for width option for horizontal scrolling


*/
