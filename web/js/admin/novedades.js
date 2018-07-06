$(document).ready(function() {
    
    $('.delete').click(function(){
        var noticia_id = $(this).attr('data');
        sweetAlertDelete(noticia_id, 'AdminNoticia');
    });

    paginateScroll();
    
});

function paginateScroll() 
{
    $('html, body').animate({
       scrollTop: $(".card-block").offset().top
    }, 100);
    console.log('pagination button clicked');
    $(".paginate_button").unbind('click', paginateScroll);
    $(".paginate_button").bind('click', paginateScroll);
}
