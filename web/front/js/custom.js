function closeNav() {
    document.getElementById("mainSidenav").classList.toggle("main-sidenav-close");
    document.getElementById("menuArrow").classList.toggle("right");
    document.getElementById("main").classList.toggle("ml-10");
    if (document.getElementById("pills-tabContent")){
        document.getElementById("pills-tabContent").classList.toggle("hidden-nav-content");
    }
}

window.onload=function(){
    var body = document.getElementsByTagName('body')[0],
    sidebar = document.getElementById('mainSidenav');

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
    var clickedTab = $(".tabs > .activo");
    var tabWrapper = $(".tab__content");
    var activeTab = tabWrapper.find(".activo");
    var activeTabHeight = activeTab.outerHeight();
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

        });
        
    // Muestra el tab al cargar la pagina load
    activeTab.show();
    // Establecer la altura del contenedor en la carga de la página
    tabWrapper.height(activeTabHeight);
    $(".tabs > li").on("click", function() {
        // Remueve la clase de activo del tab
        $(".tabs > li").removeClass("activo");
        // agrega la clase activo haciendo click al tab
        $(this).addClass("activo");
        // Actualiza la variable clickedTab
        clickedTab = $(".tabs .activo");
        // animacion fade out cuando se activa el tab
        activeTab.fadeOut(250, function() {
            // Remueve la clase activo de todos los tabs
            $(".tab__content > li").removeClass("activo");
            // obtiene el indice del tab clickeado
            var clickedTabIndex = clickedTab.index();
            // agrega la class activo correspondiente al tab
            $(".tab__content > li").eq(clickedTabIndex).addClass("activo");
            // Actualiza nuevo activo en el tab
            activeTab = $(".tab__content > .activo");
            // Actualiza la variable
            activeTabHeight = activeTab.outerHeight();
            // Animar la altura del contenedor a la nueva altura del tab
            tabWrapper.stop().delay(50).animate({
                height: activeTabHeight
            }, 500, function() {
                // animacion Fade in por la clase activo en el tab
                activeTab.delay(50).fadeIn(250);
            });
        });
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
