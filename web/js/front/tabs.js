$(document).ready(function() {
    
    var clickedTab = $(".tabs > .activo");
    var tabWrapper = $(".tab__content");
    var activeTab = tabWrapper.find(".activo");
    var activeTabHeight = activeTab.outerHeight();
    
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

});