$(document).ready(function() {

   getAlarma();
   getNotificaciones();

   

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
            console.log('por aca');
            if (data.sonar == 1) {
                $('#sonar').show();
         
            }
            else{
                $('#sonar').hide();
                
            }
            observeLeido();
            observeMuro();
           

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
            $('#padre').html(data.html);
            $('#escondido').html(data.muro);
            $(".msjMuroResp").animate({ scrollTop: $('.msjMuroResp')[0].scrollHeight}, 1000);

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

    $('#responder').click(function(){
        var muro_id = $('#id_muro').attr('data');
        var mensaje = $('#comentario').val();
        var pagina = $('#id_pagina').attr('data');
        $.ajax({
            type: "POST",
            url: $('#respuesta').attr('action'),
            async: true,
            data: {muro_id: muro_id , mensaje: mensaje , pagina: pagina},
            dataType: "json",
            success: function(data) {
                notiMuro(muro_id);
               $('#comentario').val("");
            },
            error: function(){
                
            }
        });
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