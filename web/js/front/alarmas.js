$(document).ready(function() {

   getAlarma();
   getNotificaciones();

   $('.click').click(function(){
        var noti_id = $(this).attr('data');
        getLeido(noti_id);
    });

});

var timer;

function getAlarma()
{
    $.ajax({
        type: "GET",
        url: $('#url_alarma').val(),
        async: true,
        dataType: "json",
        success: function(data) {
            $('#noti').html(data.noti);
            observeLeido();
           
            if (data.sonar == 1) {
                $('#sonar').show();
            }

        },
        error: function(){
           
        }
    });
}

function observeLeido()
{
    $('.leido').click(function(){
        var noti_id = $(this).attr('data');
        getLeido(noti_id);
    });
}

function getLeido(noti_id)
{
    /*$("#sonar").hide();*/
    $.ajax({
        type: "POST",
        url: $('#url_leido').val(),
        async: true,
        data: {noti_id: noti_id},
        dataType: "json",
        success: function(data) {
            getAlarma();
           
        },
        error: function(){
           
        }
    });
}

function getNotificaciones()
{
    timer = setInterval(function(){
        getAlarma();
    }, 60000);
   
}