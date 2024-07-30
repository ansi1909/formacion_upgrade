$(document).ready(function() {
    var clickedOpc = $(".opc");
    var clickedOpcImg = $(".elec-resp");

    clickedOpc.on('click',function(){
        clickedOpc.removeClass("opc_activa");
        $(this).toggleClass("opc_activa").fadeIn(600).delay(1000);
    });

    clickedOpcImg.on('click',function(){
        clickedOpcImg.find('.opc_img').removeClass("opc_activa-img");
        clickedOpcImg.find('.resp-opc').removeClass("opc_activa-img");
        $(this).find('.opc_img').toggleClass("opc_activa-img");
        $(this).find('.resp-opc').toggleClass("opc_activa-img");
    });

});