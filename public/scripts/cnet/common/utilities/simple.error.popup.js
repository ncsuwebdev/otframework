/*	Script: simple.error.popup.js
		The function in this script just makes a little alert box with a close button.
		
		Dependencies:
		CNET - <stickyWin.js>, <stickyWin.Modal.js>, <stickyWin.default.layout.js>
		Assets - images located at www.cnet.com/html/rb/js/assets/global/simple.error.popup (see css inline)
		
		Function: simpleErrorPopup
		This function just makes a little alert box with a close button.
		
		Arguments:
		msghdr - the caption for the window
		msg - the error message
		baseHref - the location of the icon_problems_sm.gif file; defaults to cnet's domain.
		
		Example:
		>simpleErrorPopup('Woops!', 'Oh nos! I've got five Internets open!');
		
		Returns: 
		An instance of <StickyWinModal>
	*/
var simpleErrorPopup = function(msghdr, msg, baseHref) {
	baseHref = baseHref||"http://www.cnet.com/html/rb/assets/global/simple.error.popup";
	msg = '<p class="errorMsg SWclearfix">' +
						'<img src="'+baseHref+'/icon_problems_sm.gif"'+
						' class="bang clearfix" style="float: left; width: 30px; height: 30px; margin: 3px 5px 5px 0px;">'
						 + msg + '</p>';
	var body = stickyWinHTML(msghdr, msg, {width: '250px'});
	return new StickyWinModal({
		modalOptions: {
			modalStyle: {
				zIndex: 11000
			}
		},
		zIndex: 110001,
		content: body,
		position: 'center' //center, corner
	});
};
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/utilities/simple.error.popup.js,v $
$Log: simple.error.popup.js,v $
Revision 1.12  2007/10/23 23:10:26  newtona
added mootools debugger to cvs
new file: setAssetHref.js; enables you to quickly set the location of image assets contained in the framework so you don't use CNET's versions, which can sometimes be slow.

Revision 1.11  2007/10/18 21:43:35  newtona
updating components that reference images and assets at www.cnet.com to allow for easy over-writing for that source url

Revision 1.10  2007/05/11 00:05:29  newtona
adding clearfix css to all sticky win instances

Revision 1.9  2007/05/07 21:38:33  newtona
zindex tweak so that error popup is higher than the default stickywin (in case there's an error on a page with a stickywin)

Revision 1.8  2007/03/08 23:31:22  newtona
strict javascript warnings cleaned up
removed deprecated dbug loadtimers
dbug enables on debug.cookie()

Revision 1.7  2007/02/21 00:42:30  newtona
docs update

Revision 1.6  2007/02/21 00:31:32  newtona
using default sticky win html now.

Revision 1.5  2007/02/06 18:12:26  newtona
changed the way that the css was added to the dom. only does this once now, even if you execute the function numerous times.

Revision 1.4  2007/01/26 05:56:03  newtona
syntax update for mootools 1.0
docs update

Revision 1.3  2007/01/22 21:55:18  newtona
changed image paths to www.cnet.com
updated stickyWin namespace to StickyWin

Revision 1.2  2007/01/11 20:56:05  newtona
docs change

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.2  2007/01/05 18:54:51  newtona
some documentation changes

Revision 1.1  2007/01/05 18:32:04  newtona
first check in


*/
