$(document).ready(function() {

   getAlarma();
   getNotificaciones();
   observeMuro();


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
            observeMuro();
           
            if (data.sonar == 1) {
                $('#sonar').show();
            }

        },
        error: function(){
           
        }
    });
}

function notiMuro(muro_id)
{
    $.ajax({
        type: "GET",
        url: $('#url_NotiMuro').val(),
        async: true,
        data: {muro_id: muro_id}, 
        dataType: "json",
        success: function(data) {
            $('#padre').html(data);

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

function observeMuro()
{
    $('.click').click(function(){
        var noti_id = $(this).attr('data');
        var tipo_noti = $('#tipo_noti'+noti_id).val();

        if (tipo_noti == 1) {
            var muro_id = $('#muro_id'+noti_id).val();
            notiMuro(muro_id);

        }
        
        
    });
}

function getLeido(noti_id)
{
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