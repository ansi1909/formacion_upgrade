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
            observeNotify();
        },
        error: function(){
           
        }
    });
}

function observeNotify()
{    var x = 0, y = 0, z = 0;
    $("#notify").on('click',function(){
        if(y == 0){
            if(x == 1){
                $(".dropDownMenu").slideUp(50);
                x = 0;
            }else if(z == 1){
                $(".dropDownApps").slideUp(50);
                $(".markApps").fadeOut(40);
                z = 0;
            }
            $(".dropDownNotify").slideDown(500, function(){
                $(".markNotify").fadeIn(100).delay(25);
                $(".opcListNotify").show();
            });
            y = 1;
        }else {
            $(".dropDownNotify").slideUp(500, function(){
                $(".markNotify").fadeOut(600);
            });
            y = 0;
        }
    });
}