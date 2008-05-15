/*	Script: stickyWin.Modal.js
		This script extends <StickyWin> and <StickyWinFx> classes to add <Modalizer> functionality.

		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)

		Dependencies:
		cnet - <stickyWin.js>, and all their dependencies plus <modalizer.js>.
		optional - <stickyWinFx.js> 
	*/
var modalWinBase = {
	initialize: function(options){
		options = options||{};
		this.setModalOptions($merge(options.modalOptions||{}, {
			onModalHide: function(){
					this.hide(false);
				}.bind(this)
			}));
		this.parent(options);
	},
	show: function(showModal){
		if($pick(showModal, true))this.modalShow();
		this.parent();
	},
	hide: function(hideModal){
		if($pick(hideModal, true))this.modalHide();
		this.parent();
	}
};

/*	Class: StickyWinModal
		Creates a <StickyWin> that uses the functionality in <Modalizer> to overlay the document.
		
		Argument:
		options - an object with key/value options defined in <StickyWin> and <Modalizer>
	
		Options:
		inherits all the options of <StickyWin>
		modalOptions - options object for <Modalizer>
	*/
var StickyWinModal = StickyWin.extend(modalWinBase);
StickyWinModal.implement(new Modalizer);

/*	Class: StickyWinFxModal
		Creates a <StickyWinFx> that uses the functionality in <Modalizer> to overlay the document.
		
		Argument:
		options - an object with key/value options defined in <StickyWin>, <StickyWinFx> and <Modalizer>
		
		Argument:
		options - an object with key/value options defined in <StickyWin> and <Modalizer>
	
		Options:
		inherits all the options of <StickyWinFx>
		modalOptions - options object for <Modalizer>

	*/
var StickyWinFxModal = (typeof StickyWinFx != "undefined")?StickyWinFx.extend(modalWinBase):Class.empty;
try { StickyWinFxModal.implement(new Modalizer()); }catch(e){}
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/stickyWin.Modal.js,v $
$Log: stickyWin.Modal.js,v $
Revision 1.7  2007/02/21 00:25:29  newtona
updated options handling

Revision 1.6  2007/02/06 18:11:53  newtona
fixed a bug where the iframeshim was being left behind.

Revision 1.5  2007/01/27 08:40:21  newtona
that last fix didn't work. now it handles no options passed in.

Revision 1.4  2007/01/27 08:37:03  newtona
handle the posibility that no options are applied

Revision 1.3  2007/01/26 05:49:26  newtona
added docs

Revision 1.2  2007/01/22 22:00:15  newtona
numerous bug fixes to modalizer, stickywin, and popupdetails
updated for mootools 1.0
fixed date validation in form.validator

Revision 1.1  2007/01/11 20:55:23  newtona
changed the way options are set, split up stickywin into 4 files, refactored popupdetails to use stickywin and modalizer


*/
