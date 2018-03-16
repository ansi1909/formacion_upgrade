$(document).ready(function() {

	$('.nav-less-container').scrollTo($('.circle-less-viendo'));

	var wizard = $('#wizard').val();
	var subpagina_id = $('#subpagina_id').val();
	if (wizard == 1)
	{
		nextPage(subpagina_id, 0);
	}

	$('.next_lesson').click(function(){
		var str = $(this).attr('data');
		var arr = str.split('-');
		var pagina_id = arr[0];
		var step = arr[1];
		var last = arr[2];
		if (last == 1)
		{
			// Es la última lección. Se determina qué lección falta por ver antes de finalizar.
		}
		else {
			// Continuar a la siguiente lección
			$('.tab-'+step).addClass('circle-less-vista');
			step = parseInt(step)+parseInt(1);
			var id_str = $('.tab-'+step).attr('id');
			if (id_str != 'one-tab')
			{
				id_arr = id_str.split('-');
				nextPage(id_arr[1], 1);
			}
		}
	});

});

function nextPage(pagina_id, procesar)
{

	// Se suprime el css circle-less-viendo
	$('.circle-nav').each(function(){
		$(this).removeClass('circle-less-viendo')
	});

	// Simulación de click
	var nav_item = $( "#tab-"+pagina_id );
	if (nav_item.length)
	{
		nav_item.trigger( "click" );
		nav_item.addClass('circle-less-viendo');
	}
	else {
		$('#one-tab').trigger( "click" );
		$('#one-tab').addClass('circle-less-viendo');
	}
	$('.nav-less-container').scrollTo($('.circle-less-viendo'));

}