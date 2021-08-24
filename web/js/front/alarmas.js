$(document).ready(function() {
    mostrarBotonRanking();
    getAlarma();
    getNotificaciones();
    removeNotificacionesPush();



    $('#comentario').focus(function(){
        $('.error').hide();
    });

    $('#responder').click(function(){
        var muro_id = $('#notificaciones_muro_id').val();
        var mensaje = $.trim($('#respuestaMuro').val());
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
                    $('#respuestaMuro').val("");
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
            //const alertNotificacion = document.getElementById("alert_notificacion");
            $('#noti').html(data.html);
            if (data.sonar == 1) {
                $('#sonar').show();

                //notificaciones push
                if (data.push.length){
                    console.log(data.push.length);
                   crearDivAlert(data.push);
                }

         
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
    console.log('Marcando como leido');
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
    }, 45000);
   
}

function removeNotificacionesPush(){
    timer = setInterval(function(){
        removeNotificationBox();
    }, 20000);
}

function removeNotificationBox(){
    $("div").remove(".notification-box");
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

function crearDivAlert(notificaciones){
    const container   = document.getElementById("alert-notif-container");
    for(const index in notificaciones){
            console.log(notificaciones[index]);
            var divAlert    = document.createElement("div");
            var spanIcon    = document.createElement("span");
            var icon        = document.createElement("i");
            var spanDescrip = document.createElement("span");
            var buttonClose = document.createElement("button");
            var spanButton  = document.createElement("span");
            var link        = document.createElement("a"); 
            var input      = document.createElement("input");

            input.setAttribute("type","hidden");
            

            divAlert.classList.add("alert", "alert-success","alert-dismissible", "fade","notification-box", "notification-ranking","pl-3", "py-1","position-relative" ,"show");
            divAlert.setAttribute("role","alert");
            

            icon.classList.add("material-icons","icNotify");
            icon.innerHTML = notificaciones[index].icono;
            
            spanIcon.classList.add("stickerNotify",notificaciones[index].css);
            link.classList.add("link-alert-notif");
            if (!notificaciones[index].muro){
                link.setAttribute("href",notificaciones[index].href);
               
            }else{
                link.setAttribute("href","#");
                link.setAttribute("data-toggle","modal");
                link.setAttribute("data-target","#modalMn");
                link.classList.add("click");
            }
            
            link.classList.add("leido");
            link.setAttribute("data",notificaciones[index].id);
            link.setAttribute("titulo",notificaciones[index].titulo);
            
            spanDescrip.classList.add("notification-ranking__text");
            spanDescrip.innerHTML = notificaciones[index].descripcion;

            buttonClose.classList.add("close");
            buttonClose.setAttribute("type","button");
            buttonClose.setAttribute("data-dismiss","alert");
            buttonClose.setAttribute("aria-label","Close");
            buttonClose.classList.add("pl-1");

            spanButton.setAttribute("aria-hidden",true);
            spanButton.innerHTML = "&times;";
            
            link.appendChild(spanDescrip);
            buttonClose.appendChild(spanButton);
            spanIcon.appendChild(icon);
            divAlert.appendChild(spanIcon);
            divAlert.appendChild(link);
            divAlert.appendChild(buttonClose);
            container.appendChild(divAlert);
    }

    $('.link-alert-notif').click(function(){
        removeNotificationBox();
    });

}

function mostrarBotonRanking(){
    $.ajax({
        type: "POST",
        url: $('#url_boton_ranking').val(),
        async: true,
        dataType: "json",
        success: function(data) {
            if(data.ligas){
                $('#boton_ranking').show();
            }else{
                $('#boton_ranking').hide();
            }
        },
        error: function(){
            console.log('Error en consulta de liga'); // Hay que implementar los mensajes de error para el frontend
        }
    });
}