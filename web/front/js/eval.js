$(document).ready(function() {
    var clickedOpc = $(".opc");
    var clickedOpcImg = $(".opc_img");

    clickedOpc.on('click',function(){
        clickedOpc.removeClass("opc_activa");
        $(this).toggleClass("opc_activa").fadeIn(600).delay(1000);
    });

    clickedOpcImg.on('click',function(){
        clickedOpcImg.removeClass("opc_activa-img");
        $(this).toggleClass("opc_activa-img");
    });

});