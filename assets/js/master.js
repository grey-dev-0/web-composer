$(document).ready(function(){
	$(window).on('load', ui).on('resize', ui);
	$('a[href="'+currentUrl+'"]').parent('.nav-item').addClass('active');

	function ui(){
		$('#nav-placeholder').css('height', $('nav').outerHeight()+12+'px');
	}
});