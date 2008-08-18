window.addEvent('domready', function() {
	
	var alertBox = $('alertBox').effects({duration: 200});

	$$('.runLink').each(function(el) {
		el.addEvent('click', function(e) {
			var event = new Event(e);
			event.stop();
					
			new Ajax(
	            el.getAttribute('href'),
	            {
	                method: 'get',
	                onComplete: function(txt, xml) {
	                    alertBox.start({'opacity': [0, 1]});
	                    (function() {alertBox.start({'opacity': [1, 0], 'delay': 1000});}).delay(1500);
	                    location.href = location.href;
	                }
	            }
	        ).request();
		});
	});

});