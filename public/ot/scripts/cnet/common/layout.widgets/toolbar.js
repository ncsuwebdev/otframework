/* Script: toolbar.js
	 This is the Toolbar Class for the CNET History Toolbar. I have not yet converted these docs to NaturalDocs format, so they are likely to be somewhat ugly. - a.n.

initialize  											creates a new toolbar element
																	- calls getCornerPosition to establish the upper left corner of the bar
																	- calls repositionBar to set up bar
																	- sets window.onresize to CTB resize event
																		- unfreezes the bar on resize
-----------------------------------------------------------------------------------------
rebuild														rebuilds the icons in the toolbar
																	- calls fadeIconDock
																	- calls addIcons
																	- calls repositionBar
																	- calls fadeIconDock after a timeout to fade back in
-----------------------------------------------------------------------------------------
onresize													just calls repositionBar (for now; later it may also, for instance,
																	hide any popups for any selected icon)
-----------------------------------------------------------------------------------------
movebar														moves the bar background to an offset (hide/unhide)
																	- implodeIcons
-----------------------------------------------------------------------------------------
repositionBar											calls setDimensionsAndPositionBar after a timeout
-----------------------------------------------------------------------------------------
offsetIconTops										repositions all the icons in the bar based on their size
-----------------------------------------------------------------------------------------
getCornerPosition									gets the appropriate reference corner for the bar
																	- based on $(this.iconDockId)
-----------------------------------------------------------------------------------------
setDimensionsAndPositionBar			repositions the bar to the appropriate location on the page
																	- calls setWindowDimensions (forces)
																	- calls offsetIconTops
																	- calls fitListToWindow
-----------------------------------------------------------------------------------------
fitListToWindow										hides/unhides the appropriate number of icons on the page
																	- calls calculateMaxIcons
																	- calls fadeIcon to fade icons in
-----------------------------------------------------------------------------------------
calculateMaxIcons									figures out the maximum allowable number of icons for the
																	page width
																	- refers to this.frameWidth, set by setWindowDimensions
-----------------------------------------------------------------------------------------
toggle														hides/unhides the bar
																	- calls moveBar
-----------------------------------------------------------------------------------------
addIcons													adds a group of icons to the bar in batch
																	- calls addIcon for each icon passed in
																	- calls offsetIconTops when it's finished
																	- calls fitListToWindow when it's finished
-----------------------------------------------------------------------------------------
addIcon														creates a ICON object
																	- optionally calls repositionBar
																	- optionally calls fitListToWindow
																	- calls reloadIcon on the icon to set the initial size
-----------------------------------------------------------------------------------------
removeIcons												removes Icons from the bar
																	- calls implodeIcons
																	- calls fitListToWindow
																	- calls offsetIconTops
-----------------------------------------------------------------------------------------
fadeIconDock											fades the entire iconDock in and out
-----------------------------------------------------------------------------------------
fadeIcons													fades the icons in the bar in and out
																	- calls fadeIcon
-----------------------------------------------------------------------------------------
fadeIcon													fades an individual icon in and out
-----------------------------------------------------------------------------------------
toggleFreeze											turns the animation of the bar on and off
																	- calls explodeIcon
-----------------------------------------------------------------------------------------
explodeIcon												zooms in on an icon
																	- calls itself
																	- calls resizeSelectedAndNeighbors on each icon
																	- calls offsetIconTops
-----------------------------------------------------------------------------------------
implodeIcons											shrinks an icon
																	- calls itself
																	- calls resizeSelectedAndNeighbors on each icon
																	- calls offsetIconTops
-----------------------------------------------------------------------------------------
imgOver														evaluates any mouseover behavior and resizes the image
																	- calls explodeIcon
																	- calls evalEvent on the icon
-----------------------------------------------------------------------------------------
imgOut														evaluates any mouseout behavior and resizes the image
																	- calls implodeIcons
																	- calls evalEvent on the icon
-----------------------------------------------------------------------------------------
imgClick													evaluates any click behavior
																	- calls evalEvent on the icon
-----------------------------------------------------------------------------------------
setWindowDimensions								figures the window width and height
-----------------------------------------------------------------------------------------


Icon
initialize												creates an ICON, sets some css, sets up event handlers
																	- calls sizeImg
																	- if fading in, creates moo.fx and fades in
																	- sets display = block
-----------------------------------------------------------------------------------------
evalEvent													evaluates code on an event fire
																	replaces %thisIndex% w/ the icon's index against the action
-----------------------------------------------------------------------------------------
sizeImg														sets the width and height of the icon based on the 
																	animation array
-----------------------------------------------------------------------------------------
resizeSelectedAndNeightbors				determines the proper index of the animation index for
																	the image and then calls sizeImg to size it
																	- calls sizeImg
-----------------------------------------------------------------------------------------
reloadIcon												if the icon isn't loaded, makes the image visible and
																	calls sizeImg to size the image
																	- calls sizeImg
-----------------------------------------------------------------------------------------
  */

var CNETToolbar = Class.empty;
CNETToolbar.prototype = {
	TimeOutID: null,
	TimeOutIDScroll: null,
	animationArray: new Array(0.3,0.35,0.4,0.45,0.5,0.55,0.6,0.65,0.7,0.75,0.8,0.85,0.9,0.95,1),
	frameWidth: 0,
	frameHeight: 0,
	timeOverDelay: 20,
	barOffsetTop: 0,
	barOffsetLeft: 0,
	frozen: false,
	frozenIndex: -1,
	hidden: false,
	coords: {x: 0, y:0},
	iconPositions: "fixed",
	hideOffset: 0,
	iconsShown: 0,
	iconFocus: -1,
	itemOffset: 0,
	fixedWidth: 0,
	animate: true,
/*  	
	initialize, creates a new toolbar instance
	pass in args, an array of name/value groups
	required:
		- thisName      - the name of this object instance
		  example: var myBar = new CNETToolbar({thisName: "myBar"});
		- iconDockId    - the DOM element that contains all the icons
		- barBackgroundId
										- the DOM element that contains the barBoxId; this element is moved 
										off screen when the bar is toggled.
	optional:
		- barOffsetTop  - the offset to move the bar up or down
		- barOffsetLeft - the offset to move the bar left or right
		- iconPositions - position the icons relative or fixed
		                  *relative* performs much better
											*fixed* is a little bumpy; may be useful in some layouts so I'm 
											leaving it in here for now
		- animationArray - overwrites the default animation array for zooming in on the icons
											
	additional options (set after instanciation):
	  - timeOverDelay - the delay after the user mouses over an icon before it
		                  zooms; the delay between steps in the zoom. (default: 20)
		- frozen:       - turns the animation off
		                  usually used in conjunction with the .explodeIcon(int) to zoom
											an icon and freeze it. also used to set a preference that the
											toolbar not zoom at all.
		- hidden:       - if the toolbar is offscreen (i.e. hidden), set this boolean to
		                  true. If you use the toggle() function it will set this automatically
		- frozenIndex   - if the toolbar is frozen on a particular icon, this variable is set
											to its index
		- hideOffset    - the offset to move the toolbar to hide it. Typically set in the 
											process of calling toggle(), which takes this value as a parameter
	  */  
	initialize: function(args) {
		this.icons = 	$A({});
		this.name = args.thisName;
		this.barBackgroundId = args.barBackgroundId;
		this.iconDockId = args.iconDockId;
		if (args.fixedWidth) this.fixedWidth = args.fixedWidth;
		if (args.barOffsetTop) this.barOffsetTop = args.barOffsetTop;
		if (args.barOffsetLeft) this.barOffsetLeft = args.barOffsetLeft;
		if (args.iconPositions) this.iconPositions = args.iconPositions;
		if (args.animationArray) this.animationArray = args.animationArray;
		if (args.itemOffset) this.itemOffset = args.itemOffset;
		if (args.leftIcon) this.leftIcon = args.leftIcon;
		if (args.rightIcon) this.rightIcon = args.rightIcon;
		this.getCornerPosition();
		this.repositionBar();
		var tmpCTB = this;
		Event.observe(window, "resize", function(evt) {
			this.frozen = false;
			tmpCTB.onResize(evt, tmpCTB);
		});
		if(args.fadeUp) {
			this.setWindowDimensions();
			this.moveBar({y: args.fadeUp.from}, {y: args.fadeUp.to}, {duration: 400, delay: 600});
		}
	},
	rebuild: function() {
		this.fadeIconDock(1,0);
		var tmpIcons = $A({});
		var tmpCTB = this;
		this.icons.each(function(icon, i) {
			var tmpImg = $(icon.args.id).cloneNode(true);
			$(icon.args.id).remove();
			tmpImg.hide();
			tmpImg.id = icon.args.imgId;
			$(tmpCTB.iconDockId).appendChild(tmpImg);
			//leak
			icon.args.id = tmpImg.id;
			tmpIcons.push(icon.args); 
		});
		this.iconsShown = tmpIcons.length;
		this.icons = $A({});
		//leak
		this.addIcons(tmpIcons, false);
		this.repositionBar();
		if (this.rebuildFaderTO!=null)
			clearTimeout(this.rebuildFaderTO);
		this.rebuildFaderTO = setTimeout(this.name + ".fadeIconDock(0,1);", 250);
	},
	onResize: function(){
		this.repositionBar();
	},
	moveBar: function(from, to, options) {
		//options: delay - how long to wait to move the bar after this function is called
		//options: duration - how long the move action should take
		var moveX = false;
		var moveY = false;
		if (typeof from.x != "undefined" && typeof to.x != "undefined" && from.x != to.x) moveX = true;
		if (typeof from.y != "undefined" && typeof to.y != "undefined" && from.y != to.y) moveY = true;
		if(!movX) {
			from.x = $(this.barBackgroundId).getLeft()
			to.x = from.x
		}
		if(!movY) {
			from.y = $(this.barBackgroundId).getLeft()
			to.y = from.y
		}
		
		if (typeof options.delay == "undefined") options.delay = 0;
		if (moveX || moveY) {
			this.frozen = false;
			var moveEffects = new Fx.Styles(this.barBackgroundId, {duration: 1000});
			(function() {
				moveEffects.custom({
				   'left': [from.x, to.x],
				   'top': [from.x, to.x]
				});
			}).delay(options.delay);
			if(!this.hidden && this.frozen) {
				this.implodeIcons();
			}
		}
		//$(this.barBackgroundId).style.position = "fixed";
	},
	repositionBar: function(){
		if (this.TimeOutIDScroll!=null)
				clearTimeout(this.TimeOutIDScroll);
		this.TimeOutIDScroll=window.setTimeout(this.name + ".setDimensionsAndPositionBar(5);",20);
		return true;
	},
	offsetIconTops: function(barCornerPos){
		var range = {from: this.itemOffset, to: this.itemOffset + this.iconsShown};
		if (typeof barCornerPos != "undefined")
			this.coords = barCornerPos;
			var pix = this.barOffsetLeft;
			if(this.iconsShown == 0 && this.icons.length > 0)
				this.iconsShown = this.fitListToWindow();
			//
			("this.itemOffset: %s", this.itemOffset);
			//position the images
			var tmpCTB = this;
			//for each image
			this.icons.each(function(icon, i){
				if (tmpCTB.iconPositions == "fixed") {
					$(icon.id).style.position = "fixed";
				} else {
					$(icon.id).style.position = "relative";
				}
				if (i >= range.from && i < range.to) {
					if (tmpCTB.iconPositions == "fixed") {
							$(icon.id).style.position = "fixed";
							$(icon.id).style.left = pix + "px";
							pix += Math.round(icon.width*tmpCTB.animationArray[icon.animationIndex]);
					} else {
							$(icon.id).style.position = "relative";
							var iconH = Math.round(icon.height*tmpCTB.animationArray[icon.animationIndex]);
							$(icon.id).style.bottom = icon.originalBottom + icon.currentHeight - icon.minH + tmpCTB.barOffsetTop + "px";
					}
				}
			});
	},
	getCornerPosition: function() {
		this.coords = new Object();
		var pos = Position.cumulativeOffset($(this.iconDockId));
		if(this.hookCorner == "upperRight") {
			pos[0] = pos[0] + $(this.iconDockId).offsetWidth;
		} else if (this.hookCorner == "bottomRight") {
			pos[0] = pos[0] + $(this.iconDockId).offsetWidth;
			pos[1] = pos[1] + $(this.iconDockId).offsetHeight;
		} else if (this.hookCorner == "bottomLeft") {
			pos[1] = pos[1] + $(this.iconDockId).offsetHeight;
		}
		this.coords.x = pos[0];
		this.coords.y = pos[1];
		return this.coords;
	},

	setDimensionsAndPositionBar: function(retry){
		this.setWindowDimensions(true);
		this.offsetIconTops();
		this.fitListToWindow();
		this.implodeIcons();
	},
	fitListToWindow: function() {
		if (typeof this.iconsShown == "undefined")
			this.iconsShown = 0;
		var range = {from: this.itemOffset, to: this.itemOffset + this.iconsShown};
		//dbug.log("range: %s - %s", range.from, range.to);
		var maxIcons = this.calculateMaxIcons();
		//dbug.log("maxIcons: %s", maxIcons);
		var newRange = {from: this.itemOffset, to: this.itemOffset + maxIcons};
		//dbug.log("new range: %s - %s", newRange.from, newRange.to);
		if (newRange.to > this.icons.length) newRange.to = this.icons.length;
		maxIcons = newRange.to - newRange.from;
		//dbug.log("new maxIcons: %s", maxIcons);
		/*  dbug.log("icons shown: %s", this.iconsShown);  */
		var tmpCTB = this;
		$A(this.icons).each(function(icon, idx) {
			/*  dbug.log("idx: %s < maxIcons: %s && idx: %s > iconsShown: %s", idx, maxIcons, idx, tmpCTB.iconsShown);  */
			if (idx >= newRange.from && idx < newRange.to && (idx < range.from || idx >= range.to)) {
				/*  dbug.log('show Icon'); */
				if (tmpCTB.icons[idx].shrunk)
					tmpCTB.zoomIcon({index: idx, w: "grow", h: "none"});
				else
				tmpCTB.fadeIcon(idx, 0, 1);
			} else if(idx < newRange.from || idx >= newRange.to){
				$(icon.id).style.visibility = "hidden";
			}
		});
		this.iconsShown = maxIcons;
		this.highlightScrollIcons();
		//this.implodeIcons();
		return maxIcons;
	},
	highlightScrollIcons: function() {
		var range = {from: this.itemOffset, to: this.itemOffset + this.iconsShown};
		if (range.from > 0) {
			$(this.leftIcon).style.cursor = "pointer";
			$(this.leftIcon).src = $(this.leftIcon).src.replace("out", "over");
		} else {
			$(this.leftIcon).style.cursor = "default";
			$(this.leftIcon).src = $(this.leftIcon).src.replace("over", "out");
		}

		if (this.hasNextPage()) {
			$(this.rightIcon).style.cursor = "pointer";
			$(this.rightIcon).src = $(this.rightIcon).src.replace("out", "over");
		} else {
			$(this.rightIcon).style.cursor = "default";
			$(this.rightIcon).src = $(this.rightIcon).src.replace("over", "out");
		}
	},
	calculateMaxIcons: function() {
		var winW = this.frameWidth;
		if (this.fixedWidth > 0)
			winW = this.fixedWidth;
		var tmpCTB = this;
		var iconsWidth = 0;
		var numOkIcons = 0;
		var avgMax = 0;
		var totalWidth = 0;
		var delta = (this.animationArray[this.animationArray.length-1]-this.animationArray[0])*8;
		this.icons.each(function(icon, index){
			totalWidth += icon.width;
			avgMax = totalWidth/index;
			iconsWidth += (icon.width*tmpCTB.animationArray[0]);
			iconsWidthBuffered = iconsWidth+(avgMax*delta);
			if(iconsWidthBuffered < winW) {
				numOkIcons++;
			}
		});
		return(numOkIcons);
	},
	toggle: function(toggleImgId, showSRC, hideSRC, offset, show) {
		this.implodeIcons(true);
		this.setWindowDimensions();
		this.hideOffset = offset;
		if (typeof show == "undefined") {
			if(this.hidden) {
				this.hidden = false;
				show = true;
			} else {
				this.hidden = true;
				show = false;
			}
		}
		if (show) {	
			this.moveBar({y: 0-offset}, {y: 0}, {duration: 400, delay: 600});
			$(toggleImgId).src = hideSRC;
			this.hidden = false;
		} else {
			this.moveBar({y: 0}, {y: 0-offset}, {duration: 400, delay: 600});
			$(toggleImgId).src = showSRC;
			this.hidden = true;
		}
	},
	addIcons: function(icons, draw, before) {
		var tmpCTB = this;
		if(typeof before == "undefined") before = false;
		$A(icons).each(function(icon) {
			tmpCTB.addIcon(icon, false, before);
		});
	},
	addIcon: function(args, reposition, before){
		//dbug.log("(%s) new icon: %s", this.name, args.id);
		if(typeof reposition == "undefined")
			reposition = true;
		args.attr= "";
		if (args.url!=null) args.attr="window.location.href='"+args.url+"';";
		if (args.js!=null) args.attr=args.js;
		var immObj = new CNETToolbarIcon(args, this);
		if(typeof before == "undefined") before = false;
		if (before) this.icons.unshift(immgObj);
		else this.icons.push(immObj);
		if (reposition) {
			this.repositionBar();
			this.fitListToWindow();
		}
		this.icons[immObj.iconIndex].reloadIcon();
		return immObj;
	},
	zoomIcon: function(args) {
		var icon = this.icons[args.index];
		try {
			if (typeof args.duration == "undefined")
				args.duration = 250;
			w = Fx.Style(icon.id, 'width', {duration: args.duration});
			h = Fx.Style(icon.id, 'height', {duration: args.duration});
			if (args.w == "shrink")
				w.custom(icon.currentWidth, 0);
			else if (args.w == "grow")
				w.custom(0, icon.currentWidth);
	
			if (args.h == "shrink")
				h.custom(icon.currentHeight, 0);
			else if (args.h == "grow")
				h.custom(0, icon.currentHeight);
	
			if (args.h == "shrink" || args.w == "shrink") {
				this.fadeIcon(args.index,1,0, args.duration);
				icon.shrunk = true;
				setTimeout("$('"+icon.id+"').style.display = 'none';", args.duration);
			} else if (args.h == "grow" || args.w == "grow") {
				$(icon.id).style.display = 'block';
				this.fadeIcon(args.index,0,1, args.duration);
				icon.shrunk = false;
			}
		} catch(e) {
			dbug.log(e);
		}	
	},
	removeIcons: function(indexes) {
		var tmpCTB = this;
		var tmpFrozen = this.frozen;
		this.frozen = true;
		$A(indexes).each(function(index) {
			var icon = tmpCTB.icons[index];
			tmpCTB.zoomIcon({index: index, w: "shrink", h: "none"});
			setTimeout("$('"+icon.id+"').parentNode.removeChild($('"+icon.id+"'));", 550);
			tmpCTB.implodeIcons(index);
			tmpCTB.icons.splice(index,1);
			tmpCTB.icons.each(function(CTBicon, index) {
				CTBicon.iconIndex = index;
			});
		});
		this.iconsShown = this.iconsShown-indexes.length;
		this.fitListToWindow();
		this.offsetIconTops();
		this.frozen = tmpFrozen;
	},
	fadeIconDock: function(from, to) {
		if (to == 0) $(this.iconDockId).style.display = "none";
			else $(this.iconDockId).style.display = "block";
		if (typeof this.iconDockOpacity == "undefined" || this.iconDockOpacity == null)
				this.iconDockOpacity = new Fx.Style(this.iconDockId, 'opacity', {duration: 250});
		this.iconDockOpacity.custom(from,to);
	},
	fadeIcons: function(from,to, timeIncrement) {
		var tmpCTB = this;
		var timeout = 0;
		this.icons.each(function(icon, index) {
			if (index<tmpCTB.iconsShown){
				setTimeout(tmpCTB.name + ".fadeIcon(" + index + "," + from + "," + to + ");", timeout);
				timeout += timeIncrement;
			}
		});
	},
	empty: function() {
		this.fadeIconDock(1,0);
		setTimeout(this.name+'.removeAllIcons()', 250);
	},
	removeAllIcons: function() {
		this.iconsShown = 0;
		this.icons = $A([]);
		this.fadeIconDock(0,1);
		$(this.iconDockId).innerHTML = '';
		this.highlightScrollIcons();
	},
	fadeIcon: function(index, from, to, duration) {
		if (typeof duration == "undefined")
			duration = 250;
		var icon = this.icons[index];
				o = new Fx.Style(icon.id, 'opacity', {duration: duration});
		if (from == 0) $(icon.id).style.visibility = "hidden";
		else $(icon.id).style.visibility = "visible";
		$(icon.id).style.display = "block";
		o.custom(from,to);
	},
	toggleFreeze: function(clickIndex) {
		if (this.frozen){
			if(clickIndex == this.frozenIndex)
				this.frozen = false;
			this.explodeIcon(clickIndex, true);
		} else {
			this.explodeIcon(clickIndex, true);
			this.frozen = true;
		}
		this.frozenIndex = clickIndex;
	},
	//this function grows the image
	explodeIcon: function(idImg, ignoreFreeze){
		var range = {from: this.itemOffset, to: this.itemOffset + this.iconsShown};

		if(typeof ignoreFreeze == "undefined") ignoreFreeze = false;
		if(! this.frozen || (this.frozen && ignoreFreeze)) {
			if (this.TimeOutID!=null)
					clearTimeout(this.TimeOutID);
			this.TimeOutID=window.setTimeout(this.name + ".explodeIcon("+idImg+", "+ignoreFreeze+");",this.timeOverDelay);
			b = false;
			//for each image in the bar
			var tmpCTB = this;
			this.icons.each(function(icon, i) {
				if (i >= range.from && i <= range.to) {
					if ($(icon.id).style.display != "none") {
						//if this is the image that's selected
						if (i==idImg){
								//set it to be selected
								icon.selected=true;
								//resize the image to the last value of the animation array
								b=icon.resizeSelectedAndNeighbors(tmpCTB.animationArray.length-1)||b;
								//b is false if the image wasn't loaded
						//else if tmpCTB is on either side by one
						}else if (i==idImg-1 || i==idImg+1){
								//reset it
								icon.selected=false;
								//if the image is offset from the selected one, size it to a point in the index array half way away from the end point.					
								b=icon.resizeSelectedAndNeighbors(Math.floor(tmpCTB.animationArray.length/2))||b;
						}else if (i==idImg-2 || i==idImg+2){
								icon.selected=false;
								//if the image is offset from the selected one, size it to a point in the index array 1/4 way away from the end point.					
								b=icon.resizeSelectedAndNeighbors(Math.floor(tmpCTB.animationArray.length/4))||b;
						}else{
								//size the image to the first index point in the animation
								icon.selected=false;
								if (icon.animationIndex != 0)
									b=icon.resizeSelectedAndNeighbors(0)||b;
								else b=true;
						}
					}
				}
			});
	
			this.offsetIconTops();
			if (!b) clearTimeout(this.TimeOutID);
		}
	},

	//this function shrinks the image
	implodeIcons: function(ignoreFreeze){
		var range = {from: this.itemOffset, to: this.itemOffset + this.iconsShown};
		if(typeof ignoreFreeze == "undefined") ignoreFreeze = false;
		if(! this.frozen || (this.frozen && ignoreFreeze)) {
			if (this.TimeOutID!=null)
					clearTimeout(this.TimeOutID);
			this.TimeOutID=window.setTimeout(this.name + ".implodeIcons(" + ignoreFreeze + ");",this.timeOverDelay);
			var b = false;
			var tmpCTB = this;
			this.icons.each(function(icon, index) {
				if (index >= range.from && index < range.to ) {
					if ($(icon.id).style.display != "none") {
						icon.selected=false;
						if (icon.animationIndex != 0)
							b=icon.resizeSelectedAndNeighbors(0)||b;
					}
				}
			});
			this.offsetIconTops();
			if (!b) clearTimeout(this.TimeOutID);
		}
	},
	toggleAnimation: function(animate) {
		this.frozen = true;
		this.implodeIcons(true);
		if (typeof animate == "undefined") {
			if (this.animate) this.animate = false;
			else this.animage = true;
		} else
			this.animate = animate;
		this.frozen = false;
		if (this.animate)
			this.animationArray = this.animationArrayOld;
		else {
			this.animationArrayOld = this.animationArray;
			this.animationArray = new Array(0.5,1);
		}
	},
	scroll: function(to) {
		var timeout = 75;
		var times = 0;
		//dbug.log("scroll to: %s", to);
		this.implodeIcons();
		var range = {from: this.itemOffset, to: this.itemOffset + this.iconsShown};
		//dbug.log("to: %s, iconsShown: %s, icons.length: %s", to, this.iconsShown, this.icons.length);
		if (to+this.iconsShown > this.icons.length) {
			to = this.icons.length-this.iconsShown-1; 
			//dbug.log("update: scroll to: %s", to);
		}


		//dbug.log("range: from %s to: %s", range.from, to);
		if (range.from < to) {
			//dbug.log("scroll right");
			/*  hide the left icon and show the right until you're caught up to "to" */
			/*  start at the existing first shown icon through the end of the icons that need to show */
			for(i=range.from; i<to; i++) {
				/*  hide the icon */
				setTimeout(this.name+'.zoomIcon({index: '+ i + ', w: "shrink", h: "none", duration: 100});', timeout*times);
				/*  show the icon on the right  */
				if (i+this.iconsShown < this.icons.length)
					setTimeout(this.name+'.zoomIcon({index: '+ (i + this.iconsShown) + ', w: "grow", h: "none", duration: 100});', timeout*times);
				//times=times+((times/-10)+1);
				times++;
			}
			/*  hide everything else */
		} else {
			/*  show the left and hide the right */
			/*  start at the current first icon and move left until you reach the destination icon */
			for(i=range.from; i>=to; i--) {
				/*  hide the icon if it's outside the range */
				if(i > to+this.iconsShown)
					setTimeout(this.name+'.zoomIcon({index: ' + i + ', w: "shrink", h: "none", duration: 100});',timeout*times);
				/*  show the icon  */
				else {
					setTimeout(this.name+'.zoomIcon({index: ' + i + ', w: "grow", h: "none", duration: 100});',timeout*times);
				}
				/*  also hide the icon on the right if it's outside the range  */
				//dbug.log('hide right? %s > %s', i+this.iconsShown, to+this.iconsShown);
				if(i+this.iconsShown > to+this.iconsShown) {
					//dbug.log("hide right: %s (i: %s + shown: %s + 1", i + this.iconsShown, i, this.iconsShown);
					setTimeout(this.name+'.zoomIcon({index: ' + (i + this.iconsShown) + ', w: "shrink", h: "none", duration: 100});',timeout*times);
				}
				//times=times+((times/-10)+1);
				times++;
			}
		}
		this.itemOffset = to;
		this.highlightScrollIcons();
		//this.setDimensionsAndPositionBar();
	},
	hasNextPage: function() {
		var range = {from: this.itemOffset, to: this.itemOffset + this.iconsShown};
		if (range.to+1 < this.icons.length) {
			return true;
		} else {
			return false;
		}
	},
	scrollPage: function(offset) {
		var range = {from: this.itemOffset, to: this.itemOffset + this.iconsShown};
		//if the page offset is < 0 and the from location is > 0, scroll left
		//if the offset is greater than 0 and the range.to is inside the range of available icons
		if ((offset < 0 && range.from != 0) || (offset > 0 && this.hasNextPage())) {
			var scrollTo = range.from + (offset*this.iconsShown);
			if (scrollTo < 0) scrollTo = 0;
			this.scroll(scrollTo);
			return true; //scrolling
		} else return false;
	},
	imgOver: function(idImg, ignoreFreeze){
		//dbug.log("imgOver: %s", idImg);
		this.iconFocus = idImg;
		if(typeof ignoreFreeze == "undefined") ignoreFreeze = false;
		if(! this.frozen || (this.frozen && ignoreFreeze)) {
			if (this.TimeOutID!=null)
					clearTimeout(this.TimeOutID);
			this.TimeOutID=window.setTimeout(this.name + ".explodeIcon("+idImg+");",this.timeOverDelay);
		}
		if (typeof this.icons[idImg].onMouseOver != "undefined") {
			this.icons[idImg].evalEvent(this.icons[idImg].onMouseOver, idImg);
		}
	},

	imgOut: function(idImg, ignoreFreeze){
		this.iconFocus = -1;
		if(typeof ignoreFreeze == "undefined") ignoreFreeze = false;
		if(! this.frozen || (this.frozen && ignoreFreeze)) {
			if (this.TimeOutID!=null)
					clearTimeout(this.TimeOutID);
			this.TimeOutID=window.setTimeout(this.name + ".implodeIcons();",this.timeOverDelay);
		}
		if (typeof this.icons[idImg].onMouseOut != "undefined") {
			this.icons[idImg].evalEvent(this.icons[idImg].onMouseOut, idImg);
		}
	},

	imgClick: function(idImg, ignoreFreeze){
		this.icons[idImg].evalEvent(this.icons[idImg].onMouseClick, idImg);
		//if(typeof ignoreFreeze == "undefined") ignoreFreeze = false;
		//if(! this.frozen || (this.frozen && ignoreFreeze)) {
			//this.icons[idImg].resizeSelectedAndNeighbors(0);
			//this.offsetIconTops();
			//this.imgOver(idImg);
		//}
	},

	setWindowDimensions: function(force){
		if(typeof force == "undefined") force = false;
		if((this.frameWidth <= 0 || this.frameHeight <= 0) || force) {
			if( typeof( window.innerWidth ) == 'number' ) {
					//Non-IE
					this.frameWidth = window.innerWidth-16;
					this.frameHeight = window.innerHeight;
			} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
					//IE 6+ in 'standards compliant mode'
					this.frameWidth = document.documentElement.clientWidth-16;
					this.frameHeight = document.documentElement.clientHeight;
			} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
					//IE 4 compatible
					this.frameWidth = document.body.clientWidth;
					this.frameHeight = document.body.clientHeight;
			}
		}
	}
};




var CNETToolbarIcon = Class.empty;
CNETToolbarIcon.prototype = {
	shrunk: false,
	selected: false,
	loaded: false,
	width: 0,
	height: 0,
	animationIndex: 0,
	minWidth: 0,
	minHeight: 0,
	maxWidth: -1,
	maxHeight: -1,
	initialize: function(args, bar){
		this.args = args;
		if (args.minWidth!=null) this.minWidth = args.minWidth;
		if (args.minHeight!=null) this.minHeight = args.minHeight;
		if (args.maxWidth!=null) this.width = args.maxWidth;
		if (args.maxHeight!=null) this.height = args.maxHeight;
		if (args.onMouseOver!=null) this.onMouseOver = args.onMouseOver;
		if (args.onMouseOut!=null) this.onMouseOut = args.onMouseOut;
		if (args.onClick!=null) this.onMouseClick = args.onClick;
		if (args.onChangeSize!=null) this.onChangeSize = args.onChangeSize;
		this.id=args.id;
		this.src = $(this.id).src;
		this.className = $(this.id).className;
		this.bar = bar;
		this.iconIndex = bar.icons.length;
		if(!args.css) args.css = "";
		//image.setAttribute('style', 'visibility:hidden;cursor:pointer;position: fixed; left:-500px; z-index:15;' + args.css);
		$(this.id).setAttribute('style', 'visibility:hidden;cursor:pointer; z-index:15;' + args.css);

		var icon = this;
		Event.observe(this.id,"mouseover", function() {bar.imgOver(icon.iconIndex);});
		Event.observe(this.id, "mouseout", function() {bar.imgOut(icon.iconIndex);});
		Event.observe(this.id, "click", function() {bar.imgClick(icon.iconIndex); icon.evalEvent(args.attr, icon.iconIndex);});
		this.sizeImg(0);
		if(args.fadeIn) {
			this.w = new Fx.Style(this.id, 'width', {duration: 600});
			this.o = new Fx.Style(this.id, 'opacity', {duration: 600});
			/*  this.img.style.visiblity = "visible"; */
			var width = this.width*this.bar.animationArray[0];
			this.w.custom(0,width);
			this.o.custom(0,1);
		} else {
			$(this.id).style.display = "block";
		}		
	},
	evalEvent: function(evt, index) {
		if(typeof evt != "undefined") {
			try {
				var replaceRegex = new RegExp("%thisIndex%","g");
				evt = evt.replace(replaceRegex, index);
			} catch (e) { }
			window.setTimeout(evt,1);
		}
	},
	sizeImg: function(n){
		//n is the animation Index to size this image to
		this.animationIndex = n;
		if (this.loaded){
				this.currentWidth = Math.floor(this.width*this.bar.animationArray[n]);
				$(this.id).style.width	= this.currentWidth + "px";
				this.currentHeight = Math.floor(this.height*this.bar.animationArray[n]);
				$(this.id).style.height = this.currentHeight + "px";
				return true;
		}
		return false;
 },

	
	resizeSelectedAndNeighbors: function(n){
		//is this a neighbor?
		bool = this.animationIndex!=n;
		if(this.animationIndex<n) n=this.animationIndex+1;
		else if(this.animationIndex>n) n=this.animationIndex-1;
		if(this.onChangeSize)
			if (bool) this.evalEvent(this.onChangeSize, this.iconIndex);
		return this.sizeImg(n);
	},

	reloadIcon: function(force){
		if (!this.loaded || force){
				if (this.width==0)
						this.width	= $(this.id).width;
				if (this.height==0)
						this.height = $(this.id).height;
				$(this.id).style.visibility= "visible";
				this.loaded = true;
				this.minW = Math.floor(this.width*this.bar.animationArray[0]);
				this.minH = Math.floor(this.height*this.bar.animationArray[0]);
				//var pos = Position.cumulativeOffset($(this.id));
				//var docPos = Position.cumulativeOffset($(this.bar.iconDockId));
				this.originalBottom = -this.minH;
				this.sizeImg(this.animationIndex);
		}
	}
}; 

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/layout.widgets/toolbar.js,v $
$Log: toolbar.js,v $
Revision 1.2  2007/02/21 00:29:25  newtona
switched Class.create to Class.empty

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.3  2006/11/21 23:55:42  newtona
documentation update

Revision 1.2  2006/11/13 23:53:04  newtona
added cvs footer


*/
