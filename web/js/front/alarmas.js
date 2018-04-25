$(document).ready(function() {
   getAlarma();

});

function getAlarma()
{
    $.ajax({
        type: "GET",
        url: $('#url_alarma').val(),
        async: true,
        dataType: "json",
        success: function(data) {
            $('#noti').val(data.html);
        },
        error: function(){
            console.log('Error editando el espacio colaborativo'); // Hay que implementar los mensajes de error para el frontend
            $('#button-comment').show();
        }
    });
}