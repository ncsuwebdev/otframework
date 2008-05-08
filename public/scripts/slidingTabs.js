// the Sliding Tabs mootools plugin is a creation of Jenna “Blueberry” Fox!
// Jenna released it under a donationware license on the 7th of December ‘07
// Use of sliding Tabs for more than evaluation requires donation unless you
// really cannot afford a couple of bucks. Regardless, I'd like to know where
// the script gets used! My email address is at http://creativepony.com/#contact
// Documentation: http://creativepony.com/journal/scripts/sliding-tabs/
// version: 1.6.1

var SlidingTabs = new Class({
	options: {
		startingSlide: false, // sets the slide to start on, either an element or an id 
		activeButtonClass: 'active', // class to add to selected button
		activationEvent: 'click', // you can set this to ‘mouseover’ or whatever you like
		wrap: true, // calls to previous() and next() should wrap around?
		slideEffect: { // options for effect used to animate the sliding, see Fx.Base in mootools docs
			duration: 400 // half a second
		},
		animateHeight: true, // animate height of container
		rightOversized: 0 // how much of the next pane to show to the right of the current pane
	},
	current: null, // zero based current pane number, read only
	buttons: false,
	outerSlidesBox: null,
	innerSlidesBox: null,
	panes: null,
	fx: null, // this one animates the scrolling inside
	heightFx: null, // this one animates the height
	
	
	initialize: function(buttonContainer, slideContainer, options) {
		if (buttonContainer) { this.buttons = $(buttonContainer).getChildren(); }
		this.outerSlidesBox = $(slideContainer);
		this.innerSlidesBox = this.outerSlidesBox.getFirst();
		this.panes = this.innerSlidesBox.getChildren();
		
		this.setOptions(options);
		
		this.fx = new Fx.Scroll(this.outerSlidesBox, this.options.slideEffect);
		this.heightFx = this.outerSlidesBox.effect('height', this.options.slideEffect);
		
		// set up button highlight
		this.current = this.options.startingSlide ? this.panes.indexOf($(this.options.startingSlide)) : 0;
		if (this.buttons) { this.buttons[this.current].addClass(this.options.activeButtonClass); }
		
		// add needed stylings
		this.outerSlidesBox.setStyle('overflow', 'hidden');
		this.panes.each(function(pane, index) {
			pane.setStyles({
			 'float': 'left',
			 'width': this.outerSlidesBox.offsetWidth.toInt() - this.options.rightOversized + 'px',
			 'overflow': 'hidden'
		  });
		}.bind(this));
		
		// stupidness to make IE work - it boggles the mind why this has any effect
		// maybe it's something to do with giving it layout?
		this.innerSlidesBox.setStyle('float', 'left');
		
		this.innerSlidesBox.setStyle(
			'width', (this.outerSlidesBox.offsetWidth.toInt() * this.panes.length) + 'px'
		);
		
		if (this.options.startingSlide) this.fx.toElement(this.options.startingSlide);
		
		// add events to the buttons
		if (this.buttons) this.buttons.each( function(button) {
		  button.addEvent(this.options.activationEvent, this.buttonEventHandler.bindWithEvent(this, button));
		}.bind(this));
		
		if (this.options.animateHeight)
		  this.heightFx.set(this.panes[this.current].offsetHeight);
	},
	
	
	changeTo: function(element) {
		var event = { cancel: false, target: $(element) };
		this.fireEvent('change', event);
		if (event.cancel == true) { return; };
		
		if (this.buttons) { this.buttons[this.current].removeClass(this.options.activeButtonClass); };
		this.current = this.panes.indexOf($(event.target));
		if (this.buttons) { this.buttons[this.current].addClass(this.options.activeButtonClass); };
		this.fx.stop();
		this.fx.toElement(event.target);
		if (this.options.animateHeight)
		  this.heightFx.start(this.panes[this.current].offsetHeight);
	},
	
	// Handles a click
	buttonEventHandler: function(event, button) {
		if (event.target == this.buttons[this.current]) return;
		this.changeTo(this.panes[this.buttons.indexOf($(button))]);
	},
	
	next: function() {
		var next = this.current + 1;
		if (next == this.panes.length) {
			if (this.options.wrap == true) { next = 0 } else { return }
		}
		
		this.changeTo(this.panes[next]);
	},
	
	previous: function() {
		var prev = this.current - 1
		if (prev < 0) {
			if (this.options.wrap == true) { prev = this.panes.length - 1 } else { return }
		}
		
		this.changeTo(this.panes[prev]);
	}
});

SlidingTabs.implement(new Options, new Events);
