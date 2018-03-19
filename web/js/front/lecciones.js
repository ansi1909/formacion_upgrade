$(document).ready(function() {

	var wizard = $('#wizard').val();
	var programa_id = $('#programa_id').val();
	var subpagina_id = $('#subpagina_id').val();

	$('.circle-nav').click(function(){

		var circle_nav = $(this);
		// Se suprime el css circle-less-viendo
		if (circle_nav.attr('id') != $('#tab_activo').val())
		{
			$('#'+$('#tab_activo').val()).removeClass('circle-less-viendo');
			$('#'+$('#tab_activo').val()).addClass('circle-less-vista'); // Lección anterior vista
			finishLesson(programa_id, $('#pagina_id_viendo').val());
		}

		$('#'+circle_nav.attr('id')).removeClass('circle-less-vista'); // Suprime el css vista al tab que se está presionando
		circle_nav.addClass('circle-less-viendo'); // Agrega el css viendo al tab que se está presionando
		$('#tab_activo').val(circle_nav.attr('id'));
		$('.nav-less-container').scrollTo($('.circle-less-viendo'));

		var new_pagina_id = circle_nav.attr('data');

		// Chequear enlaces del nav-bar para activar el que corresponda
		$('#menu_indice li a').each(function(){
			var nav_li = $(this);
			var id_str = nav_li.attr('id');
			var id_arr = id_str.split('-');
			if (id_arr[1] == new_pagina_id)
			{
				// Se agrega el css active al enlace
				$('#menu_indice li a.active').removeClass('active');
				$('#m-'+new_pagina_id).addClass('active');
			}
		});

		$('#pagina_id_viendo').val(new_pagina_id);
		startLesson(programa_id, $('#pagina_id_viendo').val());

	});

	if (wizard == 1)
	{
		nextPage(subpagina_id);
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
				nextPage(id_arr[1]);
			}
		}
	});

	$('#next_subpage').click(function(){
		
		var str = $(this).attr('data');
		var arr = str.split('-');
		var prog_id = arr[0];
		var pagina_id = arr[1];
		var subpag_id = arr[2];

		// Procesar página
		$.ajax({
			type: "POST",
			url: $('#url_procesar').val(),
			async: true,
			data: { programa_id: prog_id, pagina_id: pagina_id },
			dataType: "json",
			success: function(data) {
				console.log('Log Id: '+data.id);
				// Redireccionar al siguiente URL
				if (subpag_id > 0)
				{
					window.location.replace($('#url_next').val()+'/'+subpag_id);
				}
				else {
					// Página de fin
				}
				//clearTimeout( timerId );
			},
			error: function(){
				console.log('Error procesando la página'); // Hay que implementar los mensajes de error para el frontend
			}
		});

	});

});

function nextPage(pagina_id)
{

	// Simulación de click
	var nav_item = $( "#tab-"+pagina_id );
	if (nav_item.length)
	{
		nav_item.trigger( "click" );
		nav_item.removeClass('less-disabled');
	}
	else {
		$('#one-tab').trigger( "click" );
	}

}

function startLesson(programa_id, pagina_id)
{
	$.ajax({
		type: "POST",
		url: $('#url_iniciar').val(),
		async: true,
		data: { programa_id: programa_id, pagina_id: pagina_id },
		dataType: "json",
		success: function(data) {
			console.log('Logs iniciados:');
			console.log(data.logs);
			//clearTimeout( timerId );
		},
		error: function(){
			console.log('Error iniciando la lección'); // Hay que implementar los mensajes de error para el frontend
		}
	});
}

function finishLesson(programa_id, pagina_id)
{
	var ok = 0;
	$.ajax({
		type: "POST",
		url: $('#url_procesar').val(),
		async: true,
		data: { programa_id: programa_id, pagina_id: pagina_id },
		dataType: "json",
		success: function(data) {
			console.log('Log procesado: '+data.id);
			ok = 1;
			//clearTimeout( timerId );
		},
		error: function(){
			console.log('Error procesando la lección'); // Hay que implementar los mensajes de error para el frontend
		}
	});
	return ok;
}