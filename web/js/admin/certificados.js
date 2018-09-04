$(document).ready(function() 
{
	
	$('.delete').click(function()
	{
		var certificado_id = $(this).attr('data');
		sweetAlertDelete(certificado_id, 'AdminNoticia');
	});

    $('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
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
