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
		circle_nav.removeClass('less-disabled'); // Remueve el css de disables al tab que se está presionando
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
		var button = $(this);
		button.hide();
		var str = button.attr('data');
		var arr = str.split('-');
		var programa_id = arr[0];
		var subpagina_id = arr[1];
		var step = arr[2];
		var last = arr[3];
		if (last == 1)
		{
			// Es la última lección. Se determina qué lección falta por ver antes de finalizar.
			var faltante = 0;
			var pagina_faltante = 0;
			$('.circle-nav').each(function(index){
				var nav_a = $(this);
				var class_str = nav_a.attr('class');
				if ((class_str.indexOf('circle-less-vista') == -1) && (class_str.indexOf('circle-less-viendo') == -1))
				{
					if (faltante == 0)
					{
						pagina_faltante = nav_a.attr('data');
					}
					faltante = 1;
				}
			});
			if (faltante == 1)
			{
				// Nos vamos al primer tab faltante
				$('.btn-primary').show();
				nextPage(pagina_faltante);
			}
			else {
				// Se finaliza esta última lección y se redirige a la pantalla de final
				var ok = finishLesson(programa_id, subpagina_id);
				if (ok > 0)
				{
					window.location.replace($('#url_fin').val()+'/'+$('#puntos_agregados').val());
				}
			}
		}
		else {
			// Continuar a la siguiente lección
			$('.tab-'+step).addClass('circle-less-vista');
			step = parseInt(step)+parseInt(1);
			var id_str = $('.tab-'+step).attr('id');
			if (id_str != 'one-tab')
			{
				$('.btn-primary').show();
				id_arr = id_str.split('-');
				nextPage(id_arr[1]);
			}
		}
	});

	$('#next_subpage').click(function(){
		
		$('#next_subpage').hide();
		var str = $(this).attr('data');
		var arr = str.split('-');
		var prog_id = arr[0];
		var pagina_id = arr[1];
		var subpag_id = arr[2];

		var ok = finishLesson(prog_id, pagina_id);

		if (ok > 0)
		{
			if (subpag_id > 0)
			{
				window.location.replace($('#url_next').val()+'/'+subpag_id+'/'+$('#puntos_agregados').val());
			}
			else {
				window.location.replace($('#url_fin').val()+'/'+$('#puntos_agregados').val());
			}
		}

	});

	// FUNCIONALIDADES DEL MURO
	$('#button-comment').click(function(){
		var comentario = $.trim($('#comentario').val());
		$( this ).hide();
		if (comentario != '')
		{
			$.ajax({
				type: "POST",
				url: $('#form-comment').attr('action'),
				async: true,
				data: { pagina_id: $('#pagina_id_viendo').val(), mensaje: comentario, muro_id: 0 },
				dataType: "json",
				success: function(data) {
					$('#comentario').val('');
					$('#mas_recientes_comments-'+$('#pagina_id_viendo').val()).prepend(data.html);
					var puntos = $('#puntos_agregados').val();
					puntos = parseInt(puntos) + parseInt(data.puntos_agregados);
					$('#puntos_agregados').val(puntos);
					$('#button-comment').show();
					observeMuro();
					observeLike();
					//clearTimeout( timerId );
				},
				error: function(){
					console.log('Error comentando el muro'); // Hay que implementar los mensajes de error para el frontend
					$('#button-comment').show();
				}
			});
		}
	});

	observeMuro();
	observeReply();
	observeLike();

});

function nextPage(pagina_id)
{

	// Simulación de click
	var nav_item = $( "#tab-"+pagina_id );
	if (nav_item.length)
	{
		nav_item.trigger( "click" );
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
		async: false,
		data: { programa_id: programa_id, pagina_id: pagina_id },
		dataType: "json",
		success: function(data) {
			console.log('Log_puntos procesado: '+data.id);
			// Se van sumando los puntos obtenidos en la lección
			var str = data.id;
			var log_puntos = str.split('_');
			var puntos = $('#puntos_agregados').val();
			puntos = parseInt(puntos) + parseInt(log_puntos[1]);
			$('#puntos_agregados').val(puntos);
			ok = 1;
			//clearTimeout( timerId );
		},
		error: function(){
			console.log('Error procesando la lección'); // Hay que implementar los mensajes de error para el frontend
		}
	});
	return ok;
}

function observeMuro()
{

	$('.reply_comment').click(function(){
		var muro_id = $(this).attr('data');
		var response_container = $('#response-'+muro_id);
		if (response_container.length)
		{
			$('#respuesta_'+muro_id).focus();
		}
		else {
			$.ajax({
				type: "GET",
				url: $('#url_response').val(),
				async: false,
				data: { muro_id: muro_id },
				dataType: "json",
				success: function(data) {
					$('#div-response-'+muro_id).html(data.html);
					observeReply();
					//clearTimeout( timerId );
				},
				error: function(){
					console.log('Error renderizando el campo de respuesta'); // Hay que implementar los mensajes de error para el frontend
				}
			});
		}
	});

}

function observeReply()
{
	$('.button-reply').click(function(){
		var muro_id = $(this).attr('data');
		$( this ).hide();
		var respuesta = $.trim($('#respuesta_'+muro_id).val());
		if (respuesta != '')
		{
			$.ajax({
				type: "POST",
				url: $('#form-comment').attr('action'),
				async: true,
				data: { pagina_id: $('#pagina_id_viendo').val(), mensaje: respuesta, muro_id: muro_id },
				dataType: "json",
				success: function(data) {
					$('#respuesta_'+muro_id).val('');
					$('#respuestas-'+muro_id).prepend(data.html);
					$('#button-reply-'+muro_id).show();
					var puntos = $('#puntos_agregados').val();
					puntos = parseInt(puntos) + parseInt(data.puntos_agregados);
					$('#puntos_agregados').val(puntos);
					observeLike();
					//clearTimeout( timerId );
				},
				error: function(){
					console.log('Error respondiendo al comentario'); // Hay que implementar los mensajes de error para el frontend
					$('#button-reply-'+muro_id).show();
				}
			});
		}
	});
}

function observeLike()
{
	$('.like').click(function(){
		var muro_id = $(this).attr('data');
		$('#i-'+muro_id).removeClass('ic-lke-act');
		$.ajax({
			type: "POST",
			url: $('#url_like').val(),
			async: true,
			data: { social_id: 1, entidad_id: muro_id, usuario_id: $('#usuario_id').val() },
			dataType: "json",
			success: function(data) {
				if (data.ilike)
				{
					$('#i-'+muro_id).addClass('ic-lke-act');
				}
				$('#like-'+muro_id).html(data.cantidad);
				var puntos = $('#puntos_agregados').val();
				puntos = parseInt(puntos) + parseInt(data.puntos_like);
				$('#puntos_agregados').val(puntos);
				//clearTimeout( timerId );
			},
			error: function(){
				console.log('Error en like'); // Hay que implementar los mensajes de error para el frontend
			}
		});
	});
}