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
    $('.hola').click(function(){
        var noti_id = $(this).attr('data');
        getLeido();
    });
}

function getLeido()
{
    $("#sonar").hide();   
}