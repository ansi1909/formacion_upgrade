$(document).ready(function() {

    getAlarma();
    getNotificaciones();

    $('#comentario').focus(function(){
        $('.error').hide();
    });

    $('#responder').click(function(){
        var muro_id = $('#notificaciones_muro_id').val();
        var mensaje = $.trim($('#comentario').val());
        if (mensaje == '')
        {
            $('#muroResponse-error').show();
        }
        else {
            $('#responder').hide();
            $('#notificaciones_wait').show();
            $.ajax({
                type: "POST",
                url: $('#respuesta').attr('action'),
                async: true,
                data: { muro_id: muro_id , mensaje: mensaje },
                dataType: "json",
                success: function(data) {
                    $('.msjMuroResp').append(data.html);
                    $(".msjMuroResp").animate({ scrollTop: $('.msjMuroResp')[0].scrollHeight}, 1000);
                    $('#comentario').val("");
                    $('#notificaciones_wait').hide();
                    $('#responder').show();
                    observeLike();
                },
                error: function(){
                    $('#notificaciones_wait').hide();
                    $('#responder').show();
                    console.log('Error respondiendo un comentario del muro'); // Hay que implementar los mensajes de error para el frontend
                }
            });
        }
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
            $('#noti').html(data.html);
            
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

function notiMuro(muro_id, titulo)
{
    $.ajax({
        type: "GET",
        url: $('#url_NotiMuro').val(),
        async: true,
        data: {muro_id: muro_id}, 
        dataType: "json",
        success: function(data) {
            $('#tituloMuro').html(titulo);
            $('#padre').html(data.html);
            $('#notificaciones_muro_id').val(muro_id);
            observeLike();
            $(".msjMuroResp").animate({ scrollTop: $('.msjMuroResp')[0].scrollHeight}, 1000);
        },
        error: function(){
            console.log('Error obteniendo las respuestas del muro'); // Hay que implementar los mensajes de error para el frontend
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
        var titulo = $(this).attr('titulo');
        var muro_id = $('#muro_id'+noti_id).val();
        notiMuro(muro_id, titulo); 
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

function observeLike()
{
    $('.like').unbind('click');
    $('.like').click(function(){
        var muro_id = $(this).attr('data');
        $('#like'+muro_id).removeClass('ic-lke-act');
        $.ajax({
            type: "POST",
            url: $('#url_like').val(),
            async: true,
            data: { social_id: 1, entidad_id: muro_id, usuario_id: $('#like_usuario_id').val() },
            dataType: "json",
            success: function(data) {
                if (data.ilike)
                {
                    $('#like'+muro_id).addClass('ic-lke-act');
                }
                $('#cantidad_like-'+muro_id).html(data.cantidad);
                //clearTimeout( timerId );
            },
            error: function(){
                console.log('Error en like'); // Hay que implementar los mensajes de error para el frontend
            }
        });
    });
}