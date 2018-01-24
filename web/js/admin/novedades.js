$(document).ready(function() 
{
	
	$('.delete').click(function()
	{
		var noticia_id = $(this).attr('data');
		sweetAlertDelete(noticia_id, 'AdminNoticia');
	});

    $('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

});
