$(document).ready(function() {
    $('.resp-down').on('click', function(){
        var scrollPositionResponse = $(".f-card-det-resp").offset().top;
        var scrollPosition = scrollPositionResponse - 60;

        $('html,body').animate({scrollTop: scrollPosition}, 'slow');
        CKEDITOR.instances.mensaje_response.focus(); 
        
    });

});