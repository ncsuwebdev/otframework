$('document').ready(function() {
	
	$('#alertBox').hide();
	
	$('.runLink').each(function() {
		$(this).click(function(e) {
			e.preventDefault();
			
			$.ajax({
			    url: $(this).attr('href'),
			    type: 'get',
			    cache: false,
			    success: function(html, status) {
			        $("#alertBox").fadeIn();
			        setTimeout(function(){ $('#alertBox').fadeOut('normal', function() {location.href=location.href;});}, 1500);
			    }
			});
		});
	});

});