$(document).ready(function() {
    $('.resp-down').on('click', function(){
    	$('html, body').animate({
		    scrollTop: ($('#li_responder').offset().top-100)
		},2000);
        CKEDITOR.instances.mensaje_response.focus(); 
    });

});