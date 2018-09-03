$(document).ready(function() {

	var wizard = $('#wizard').val();
	var programa_id = $('#programa_id').val();
	var subpagina_id = $('#subpagina_id').val();

	$('.circle-nav').click(function(){

		var circle_nav = $(this);
		$('video').trigger('pause');
		$('audio').trigger('pause');

		// Se suprime el css circle-less-viendo
		if (circle_nav.attr('id') != $('#tab_activo').val())
		{
			$('#'+$('#tab_activo').val()).removeClass('circle-less-viendo');
			$('#'+$('#tab_activo').val()).addClass('circle-less-vista'); // Lección anterior vista
			finishLesson(programa_id, $('#pagina_id_viendo').val());
			$('#prefix').val('recientes');
			$('#mas_valorados').removeClass('active-line');
			$('#mas_recientes').addClass('active-line');
			$('#mas_recientes_comments-'+$('#pagina_id_viendo').val()).hide(1000);
			$('#mas_valorados_comments-'+$('#pagina_id_viendo').val()).hide(1000);
		}

		$('#'+circle_nav.attr('id')).removeClass('circle-less-vista'); // Suprime el css vista al tab que se está presionando
		circle_nav.addClass('circle-less-viendo'); // Agrega el css viendo al tab que se está presionando
		circle_nav.removeClass('less-disabled'); // Remueve el css de disables al tab que se está presionando
		$('#tab_activo').val(circle_nav.attr('id'));
		$('.nav-less-container').scrollTo($('.circle-less-viendo'));
		$('html, body').animate({
		    scrollTop: 0
		},1000);

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

		// Activar el muro
		var muroActivo = $('#muroActivo'+new_pagina_id).val();
		if (muroActivo == 1)
		{
			$('#iconComments').show();
			$('#mas_valorados_comments-'+new_pagina_id).hide(1000);
			$('#mas_recientes_comments-'+new_pagina_id).show(1000);
		}
		else {
			$("#comments").removeClass("open-comments");
			$("#main").removeClass("ml-comments");
			$('#iconComments').hide();
		}

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
		$('.before_lesson').hide();
		$('#wait').show();
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
				$('#wait').hide();
				$('.btn-primary').show();
				$('.before_lesson').show();
				nextPage(pagina_faltante);
			}
			else {
				// Se finaliza esta última lección y se redirige a la pantalla de final
				finishLesson(programa_id, subpagina_id);
				setTimeout(function() {
					// Esperar a que responda el servidor
					window.location.replace($('#url_fin').val()+'/'+$('#puntos_agregados').val());
			    }, 3000);
			}
		}
		else {
			// Continuar a la siguiente lección
			$('.tab-'+step).addClass('circle-less-vista');
			step = parseInt(step)+parseInt(1);
			var id_str = $('.tab-'+step).attr('id');
			if (id_str != 'one-tab')
			{
				$('#wait').hide();
				$('.btn-primary').show();
				$('.before_lesson').show();
				id_arr = id_str.split('-');
				nextPage(id_arr[1]);
			}
		}
	});

	$('.before_lesson').click(function(){
		var button = $(this);
		button.hide();
		$('.next_lesson').hide();
		$('#wait').show();
		var str = button.attr('data');
		var arr = str.split('-');
		var programa_id = arr[0];
		var subpagina_id = arr[1];
		var step = arr[2];
		var last = arr[3];
		
		$('.tab-'+step).addClass('circle-less-vista');
		step = parseInt(step)-parseInt(1);
		var id_str = $('.tab-'+step).attr('id');
		if (id_str != 'one-tab')
		{
			$('#wait').hide();
			$('.btn-primary').show();
			$('.before_lesson').show();
			id_arr = id_str.split('-');
			nextPage(id_arr[1]);
		}
		else {
			$('#wait').hide();
			$('.btn-primary').show();
			$('.before_lesson').show();
			$('#one-tab').trigger( "click" );
		}
	});

	$('#next_subpage').click(function(){
		
		$('#next_subpage').hide();
		$('#wait').show();
		var str = $(this).attr('data');
		var arr = str.split('-');
		var prog_id = arr[0];
		var pagina_id = arr[1];
		var subpag_id = arr[2];

		finishLesson(prog_id, pagina_id);
		setTimeout(function() {
			// Esperar a que responda el servidor
			if (subpag_id > 0)
			{
				window.location.replace($('#url_next').val()+'/'+subpag_id+'/'+$('#puntos_agregados').val());
			}
			else {
				window.location.replace($('#url_fin').val()+'/'+$('#puntos_agregados').val());
			}
	    }, 3000);

	});

	// FUNCIONALIDADES DEL MURO
	$('#button-comment').click(function(){
		var comentario = $.trim($('#comentario').val());
		var prefix = $('#prefix').val();
		$( this ).hide();
		if (comentario != '')
		{
			$.ajax({
				type: "POST",
				url: $('#form-comment').attr('action'),
				async: true,
				data: { pagina_id: $('#pagina_id_viendo').val(), mensaje: comentario, muro_id: 0, prefix: prefix },
				dataType: "json",
				success: function(data) {
					$('#comentario').val('');
					$('#mas_'+prefix+'_comments-'+$('#pagina_id_viendo').val()).prepend(data.html);
					var puntos = $('#puntos_agregados').val();
					puntos = parseInt(puntos) + parseInt(data.puntos_agregados);
					$('#puntos_agregados').val(puntos);
					$('#button-comment').show();
					$('#dirty_'+$('#pagina_id_viendo').val()).val(1);
					observeMuroLecciones();
					observeLikeLecciones();
					//clearTimeout( timerId );
				},
				error: function(){
					console.log('Error comentando el muro'); // Hay que implementar los mensajes de error para el frontend
					$('#button-comment').show();
				}
			});
		}
	});

	$('.tab_rv').click(function(){
		var prefix = $('#prefix').val();
		var pagina_id = $('#pagina_id_viendo').val();
		var link_tab = $(this);
		var dirty = $('#dirty_'+pagina_id).val();
		var link_tab_id = $(this).attr('id');
		var link_tab_arr = link_tab_id.split('_');
		var last_tab = $('#mas_'+prefix+'_comments-'+pagina_id);
		var new_tab = $('#mas_'+link_tab_arr[1]+'_comments-'+pagina_id);
		if (link_tab_arr[1] != $('#prefix').val())
		{
			$('#mas_'+prefix).removeClass('active-line');
			$('#prefix').val(link_tab_arr[1]);
			link_tab.addClass('active-line');
			if (dirty == 1)
			{
				// Refrescar el tab
				$.ajax({
					type: "GET",
					url: $('#url_refresh').val(),
					async: false,
					data: { pagina_id: pagina_id, prefix: $('#prefix').val() },
					dataType: "json",
					success: function(data) {
						new_tab.html(data.html);
						observeMuro();
						observeLikeLecciones();
						observeMore();
						observeMoreResponses();
						last_tab.hide(1000);
						new_tab.show(1000);
						$('#dirty_'+pagina_id).val(0);
						//clearTimeout( timerId );
					},
					error: function(){
						console.log('Error refrescando el muro'); // Hay que implementar los mensajes de error para el frontend
					}
				});
			}
			else {
				// Solo mostrar lo que ya está cargado en el tab
				last_tab.hide(1000);
				new_tab.show(1000);
			}
		}
	});

	observeMuroLecciones();
	observeReply();
	observeLikeLecciones();
	observeMore();
	observeMoreResponses();

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
	$.ajax({
		type: "POST",
		url: $('#url_procesar').val(),
		async: true,
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
			//clearTimeout( timerId );
		},
		error: function(){
			console.log('Error procesando la lección'); // Hay que implementar los mensajes de error para el frontend
		}
	});
}

function observeMuroLecciones()
{

	$('.reply_comment').unbind('click');
	$('.reply_comment').click(function(){
		var muro_id = $(this).attr('data');
		var response_container = $('#response-'+muro_id);
		var prefix = $('#prefix').val();
		if (response_container.length)
		{
			$('#'+prefix+'_respuesta_'+muro_id).focus();
		}
		else {
			$.ajax({
				type: "GET",
				url: $('#url_response').val(),
				async: false,
				data: { muro_id: muro_id, prefix: prefix },
				dataType: "json",
				success: function(data) {
					$('#'+prefix+'_div-response-'+muro_id).html(data.html);
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
	$('.button-reply').unbind('click');
	$('.button-reply').click(function(){
		var muro_id = $(this).attr('data');
		var prefix = $('#prefix').val();
		$( this ).hide();
		var respuesta = $.trim($('#'+prefix+'_respuesta_'+muro_id).val());
		if (respuesta != '')
		{
			$.ajax({
				type: "POST",
				url: $('#form-comment').attr('action'),
				async: true,
				data: { pagina_id: $('#pagina_id_viendo').val(), mensaje: respuesta, muro_id: muro_id, prefix: prefix },
				dataType: "json",
				success: function(data) {
					$('#'+prefix+'_respuesta_'+muro_id).val('');
					$('#'+prefix+'_respuestas-'+muro_id).prepend(data.html);
					$('#'+prefix+'_button-reply-'+muro_id).show();
					var puntos = $('#puntos_agregados').val();
					puntos = parseInt(puntos) + parseInt(data.puntos_agregados);
					$('#puntos_agregados').val(puntos);
					$('#dirty_'+$('#pagina_id_viendo').val()).val(1);
					observeLikeLecciones();
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

function observeLikeLecciones()
{
	$('.like').unbind('click');
	$('.like').click(function(){
		var muro_id = $(this).attr('data');
		var prefix = $('#prefix').val();
		$('#'+prefix+'_i-'+muro_id).removeClass('ic-lke-act');
		$.ajax({
			type: "POST",
			url: $('#url_like').val(),
			async: true,
			data: { social_id: 1, entidad_id: muro_id, usuario_id: $('#usuario_id').val() },
			dataType: "json",
			success: function(data) {
				if (data.ilike)
				{
					$('#'+prefix+'_i-'+muro_id).addClass('ic-lke-act');
				}
				$('#'+prefix+'_like-'+muro_id).html(data.cantidad);
				var puntos = $('#puntos_agregados').val();
				puntos = parseInt(puntos) + parseInt(data.puntos_like);
				$('#puntos_agregados').val(puntos);
				$('#dirty_'+$('#pagina_id_viendo').val()).val(1);
				//clearTimeout( timerId );
			},
			error: function(){
				console.log('Error en like'); // Hay que implementar los mensajes de error para el frontend
			}
		});
	});
}

function observeMore()
{
	$('.more_comments').unbind('click');
	$('.more_comments').click(function(){
		var a = $(this);
		var pagina_id = a.attr('data');
		var prefix = $('#prefix').val();
		var hidden = $('#more_comments_'+prefix+'-'+pagina_id);
		var offset = hidden.val();
		var div = $('#mas_'+prefix+'_comments-'+pagina_id);
		a.hide();
		$.ajax({
			type: "GET",
			url: $('#url_more').val(),
			async: false,
			data: { pagina_id: pagina_id, muro_id: 0, prefix: prefix, offset: offset },
			dataType: "json",
			success: function(data) {
				// Se borran tanto el enlace como el campo hidden, y se vuelve a renovar en caso de que hayan más comentarios.
				a.remove();
				hidden.remove();
				div.append(data.html);
				observeMuroLecciones();
				observeLikeLecciones();
				observeMore();
				//clearTimeout( timerId );
			},
			error: function(){
				console.log('Error obteniendo más comentarios'); // Hay que implementar los mensajes de error para el frontend
			}
		});
	});
}

function observeMoreResponses()
{
	$('.more_answers').unbind('click');
	$('.more_answers').click(function(){
		var a = $(this);
		var muro_id = a.attr('data');
		var prefix = $('#prefix').val();
		var hidden = $('#'+prefix+'_more_answers-'+muro_id);
		var offset = hidden.val();
		var div = $('#'+prefix+'_respuestas-'+muro_id);
		a.hide();
		$.ajax({
			type: "GET",
			url: $('#url_more').val(),
			async: false,
			data: { pagina_id: 0, muro_id: muro_id, prefix: prefix, offset: offset },
			dataType: "json",
			success: function(data) {
				// Se borran tanto el enlace como el campo hidden, y se vuelve a renovar en caso de que hayan más comentarios.
				a.remove();
				hidden.remove();
				div.append(data.html);
				observeLikeLecciones();
				observeMoreResponses();
				//clearTimeout( timerId );
			},
			error: function(){
				console.log('Error obteniendo más respuestas'); // Hay que implementar los mensajes de error para el frontend
			}
		});
	});
}
