var remaining_time = $('#remaining_time').val();
activarTimeout();

function activarTimeout(){
    var timerId = setTimeout(function()
    { 
        $( "#automaticLogout" ).trigger( "click" );
        $('#keepSession').show();
        var timer = setInterval(function(){ 
            var remaining = $('#remaining').val();
            if (remaining > 0)
            {
                remaining = parseInt(remaining) - parseInt(1);
                $('#remaining').val(remaining);
                $('#seconds').html(remaining);
            }
            else {
                $('#keepSession').hide();
                clearInterval(timer);
                window.location.replace($('#url_logout').val());
            }
        }, 1000);

        $('#timer').val(timer);

    }, $('#sesion_time').val());

}

$('#keepSession').click(function(){
    $('#remaining').val(remaining_time);
    $('#seconds').html(remaining_time);
    clearInterval($('#timer').val());
    activarTimeout();
});
