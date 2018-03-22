// CENTRAR NAVEGACION
    var parent=$('#wraperNavLess');
    var child=$('#navlesson');

    var scrollChild = child.get(0).scrollWidth;
    var scrollParent = parent.get(0).scrollWidth;
    if ( scrollChild < scrollParent ) {
        child.css("justify-content", "center");
    }
//FIN CENTRAR NAVEGACION


// BOTONES PARA LA NAVEGACION DE LAS LECCIONES
    $('#btnLessRight').click(function() {
        event.preventDefault();
        $('.nav-less-container').animate({
            scrollLeft: "+=300px"
        }, "slow");
    });

    $('#btnLessLeft').click(function() {
        event.preventDefault();
        $('.nav-less-container').animate({
            scrollLeft: "-=300px"
        }, "slow");
    });
// FIN
