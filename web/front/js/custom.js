function closeNav() {
    document.getElementById("mainSidenav").classList.toggle("main-sidenav-close");
    document.getElementById("menuArrow").classList.toggle("right");
    document.getElementById("main").classList.toggle("ml-10");
    if (document.getElementById("pills-tabContent")){
        document.getElementById("pills-tabContent").classList.toggle("hidden-nav-content");
    }
    if (!$(".main-sidenav-close")[0] && $(".open-comments")[0]){
        $("#comments").removeClass("open-comments");
        $("#main").removeClass("ml-comments");
    }
}

window.onload=function(){
    //var body = document.getElementsByTagName('body')[0],
    //sidebar = document.getElementById('mainSidenav');

    // sidebar.onmouseover = function() {
    //     body.style.overflow = 'hidden';
    //
    // }
    // sidebar.onmouseout = function() {
    //     body.style.overflow = 'auto';
    // }

    // if (document.getElementById('comments')) {
    //
    //     var comments = document.getElementById('comments');
    //     comments.onmouseover = function() {
    //         body.style.overflow = 'hidden';
    //     }
    //     comments.onmouseout = function() {
    //         body.style.overflow = 'auto';
    //     }
    // }

};


$(document).ready(function() {
  // do stuff when DOM is ready
// $("#message").fadeIn("slow");
    var clickedOpc = $(".opc");
    var clickedOpcImg = $(".opc_img");

$("#equis").click(function(){
        $("#message").fadeOut("slow");
    });

$("#arrowDown").click(function(){
        $(".well-message-prog p").toggleClass("up");
        $("#arrowDown").toggleClass("arrow-move");

    });

$("#iconComments").click(function(){
            $("#comments").toggleClass("open-comments");
            $("#main").toggleClass("ml-comments");
            if (!$(".main-sidenav-close")[0]){
                 closeNav();
            }
        });


    clickedOpc.on('click',function(){
        clickedOpc.removeClass("opc_activa");
        $(this).toggleClass("opc_activa").fadein(300).delay(1000);
    });

    clickedOpcImg.on('click',function(){
        clickedOpcImg.removeClass("opc_activa-img");
        $(this).toggleClass("opc_activa-img");
    });

});
