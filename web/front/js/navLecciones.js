// CENTRAR NAVEGACION
    var parent=$('#wraperNavLess');
    var child=$('#navlesson');

    if (parent.length && child.length)
    {
        var scrollChild = child.get(0).scrollWidth;
        var scrollParent = parent.get(0).scrollWidth;

        if ( scrollChild < scrollParent ) {
            child.css("justify-content", "center");
        } else {
            $('#btnLessLeft').css('opacity', '1');
            $('#btnLessRight').css('opacity', '1');
        }
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
