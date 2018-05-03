$(document).ready(function() {
   getAlarma();

   $('#').click(function(){
        getLeido();
    });

});

function getAlarma()
{
    $.ajax({
        type: "GET",
        url: $('#url_alarma').val(),
        async: true,
        dataType: "json",
        success: function(data) {
            $('#noti').html(data.noti);
            if (data.sonar == 1) {
                $('#sonar').show();
            }
        },
        error: function(){
           
        }
    });
}


function getLeido()
{
    $('#sonar').hiden();   
}