$(document).ready(function() {
    var clock;
    var timing = $('#duracion').val();
    //timing = 10;
    // Instantiate a counter
    clock = new FlipClock($('.clock'), timing, {
        clockFace: 'MinuteCounter',
        autoStart: true,
        countdown: true,
        callbacks: {
            stop: function() {
            	$('.toHide').hide();
            	$('#msgTitulo1').hide();
            	$('#msgTitulo2').show();
            	$('#msgModal1').hide();
            	$('#msgModal2').show();
            	$( "#triggerModal" ).trigger( "click" );
            	setTimeout(function() {
                    window.location.replace($('#url_fin').val());
                    //console.log('Redireccionamiento');
                }, 6000);
            }
        }
    });
});