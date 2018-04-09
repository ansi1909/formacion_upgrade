$(document).ready(function() {
    var clock;
    var timing = $('#duracion').val();
    //timing = 25;
    //var timing = 60 * 30;
    // Instantiate a counter
    clock = new FlipClock($('.clock'), timing, {
        clockFace: 'MinuteCounter',
        autoStart: true,
        countdown: true,
        callbacks: {
            stop: function() {
            	$('.btn_sp').hide();
                // Redirección a la página de fin de la prueba
                //console.log('Expiró el tiempo');
                window.location.replace($('#url_fin').val());
            }
        }
    });
});