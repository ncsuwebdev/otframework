/*	Script: setAssetHref.js
		Overrides the location of assets referenced in CNET js framework files.
		
		You can download the assets at google
		http://code.google.com/p/cnetjavascript/downloads/list
		
		Function: setCNETAssetBaseHref
		Overrides the location of assets referenced in CNET js framework files.
		
		Arguments:
		baseHref - (string) the path to the assets directory for CNET JS files.
		
		Example:
		If the file "/tips/bubble.png" were at the url "http://mysite.com/cnetAssets/tips/bubble.png"
		you would execute:
		> setCNETAssetBaseHref('http://mysite.com/cnetAssets');
		
		You only need to do this once on the page and then all the asset requests will go to 
		your server instead of CNETs.
	*/
function setCNETAssetBaseHref(baseHref) {
	if (typeof stickyWinHTML != "undefined") {
		var CGFstickyWinHTML = stickyWinHTML.bind(window);
		stickyWinHTML = function(caption, body, options){
		    return CGFstickyWinHTML(caption, body, $merge({
		        baseHref: baseHref + '/stickyWinHTML/'
		    }, options));
		};
	}
	if (typeof TagMaker != "undefined") {
		TagMaker = TagMaker.extend({
		    options: {
		        baseHref: baseHref + '/tips/'
		    }
		});
	}

	if (typeof simpleErrorPopup != "undefined") {
		var CGFsimpleErrorPopup = simpleErrorPopup.bind(window);
		simpleErrorPopup = function(msghdr, msg, baseHref) {
		    return CGFsimpleErrorPopup(msghdr, msg, baseHref|| baseHref + "/simple.error.popup");
		};
	}
	
	if (typeof DatePicker != "undefined") {
		DatePicker = DatePicker.extend({
		    options: {
		        baseHref: baseHref
		    }
		});
	}
	
	if (typeof ProductPicker != "undefined") {
		ProductPicker = ProductPicker.extend({
		    options:{
		        baseHref: baseHref + '/Picker'
		    }
		});
	}
	
	if (typeof Autocompleter != "undefined") {
		Autocompleter.Base = Autocompleter.Base.extend({
		    options:{
		        baseHref: baseHref + '/autocompleter/'
		    }
		});
	}
	
	if (typeof Lightbox != "undefined") {
		Lightbox = Lightbox.extend({
		    options: {
		        assetBaseUrl: baseHref + '/slimbox/'
		    }
		});
	}
};
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/setAssetHref.js,v $
$Log: setAssetHref.js,v $
Revision 1.7  2007/11/02 18:15:38  newtona
fixing an issue with the image path in setAssetHref for the date picker
adding mms to url validator in form validator

Revision 1.6  2007/10/30 18:59:53  newtona
fixpng.js now supports background png images
doc typo in setAssetHref.js

Revision 1.5  2007/10/24 23:27:22  newtona
adding error catchers for setAssetHref.js

Revision 1.4  2007/10/24 17:26:20  newtona
typo in setAssetHref.js

Revision 1.3  2007/10/23 23:25:33  newtona
fixing a typo in setAssetHref.js

Revision 1.2  2007/10/23 23:11:55  newtona
tweaking setAssetHref.js

Revision 1.1  2007/10/23 23:10:24  newtona
added mootools debugger to cvs
new file: setAssetHref.js; enables you to quickly set the location of image assets contained in the framework so you don't use CNET's versions, which can sometimes be slow.


*/
