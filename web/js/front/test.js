$(document).ready(function() {

	var total = $('#total').val();
	var prueba_log_id = $('#prueba_log_id').val();

	$('.btn_sp').click(function(){

		var next = $(this);
		var next_id = next.attr('id');
		$('.btn_sp').hide();
		var nro = $('#nro').val();
		var pregunta_id = $('#pregunta_id').val();

		// Escondemos la corriente pregunta
		$('#pregunta-'+nro).hide(1000);

		if (next_id == 'before')
		{
			var nro_p = parseInt(nro)-parseInt(2);
		}
		else {
			var nro_p = nro;
		}

		// Porcentaje de avance
		var porcentaje = parseInt(((nro_p/total)*100), 10);

		// Almacenamos las respuestas
		$.ajax({
			type: "POST",
			url: $('#url_respuesta').val(),
			async: true,
			data: $('#form-pregunta'+pregunta_id).serialize()+'&prueba_log_id='+prueba_log_id+'&pregunta_id='+pregunta_id+'&nro='+nro+'&porcentaje='+porcentaje,
			dataType: "json",
			success: function(data) {
				if (data.ok == 1)
				{
					if (next_id == 'before')
					{
						$("#progreso").attr("style", 'width: '+porcentaje+'%');
						nro = parseInt(nro)-parseInt(1);
						$('#nro').val(nro);
						var nro_pregunta = nro < 10 ? '0'+nro : nro;
						$('#nro_pregunta').html(nro_pregunta);
						$('#pregunta-'+nro).show(1000); // Se muestra la siguiente pregunta
						$('#pregunta_id').val($('#pregunta-'+nro).attr('data')); // Anterior pregunta_id
						$('#next').show();
						if (nro > 1)
						{
							$('#before').show();
						}
					}
					else {
						$("#progreso").attr("style", 'width: '+porcentaje+'%');
						if (nro < total)
						{
							nro = parseInt(nro)+parseInt(1);
							$('#nro').val(nro);
							var nro_pregunta = nro < 10 ? '0'+nro : nro;
							$('#nro_pregunta').html(nro_pregunta);
							$('#pregunta-'+nro).show(1000); // Se muestra la siguiente pregunta
							$('#pregunta_id').val($('#pregunta-'+nro).attr('data')); // Nueva pregunta_id
							$('#next').show();
							if (nro > 1)
							{
								$('#before').show();
							}
						}
						else {
							// Redirección a la página de fin de la prueba
							window.location.replace($('#url_fin').val());
						}
					}
				}
				else {
					// Redirección a la página de resultados
					window.location.replace($('#url_resultados').val());
				}
				//clearTimeout( timerId );
			},
			error: function(){
				console.log('Error procesando las respuestas a la pregunta '+pregunta_id); // Hay que implementar los mensajes de error para el frontend
			}
		});

	});

	$('.opc, .opc_img, .elec-resp').unbind('click');

	$('.opc, .opc_img').click(function(){

		var div_opc = $(this);
		var div_str = div_opc.attr('class');
		var data = div_opc.attr('data');
		var data_arr = data.split('_');
		var tipo_elemento = data_arr[0];
		var tipo_pregunta = data_arr[1];
		var po_id = data_arr[2];
		var css = tipo_elemento==1 ? 'opc_activa' : 'opc_activa-img';
		var div_class = tipo_elemento==1 ? 'opc' : 'opc_img';
		var pregunta_id = $('#pregunta_id').val();

		if (tipo_pregunta == 1)
		{
			$('.opc__'+pregunta_id).removeClass(css);
			div_opc.addClass(css);
		}
		else {
			// Si ya estaba activado, se desactiva; y viceversa
			if (div_str.indexOf(css) == -1)
			{
				div_opc.addClass(css);
				if (tipo_elemento == 2)
				{
					$('#resp-opc__'+po_id).addClass(css);
				}
			}
			else {
				div_opc.removeClass(css);
				if (tipo_elemento == 2)
				{
					$('#resp-opc__'+po_id).removeClass(css);
				}
			}
		}

		// Valores seleccionados actualiza pregunta_id
		var opcion_ids = [];
		$('.opc__'+pregunta_id).each(function(){
			div_o = $(this);
			var class_o = div_o.attr('class');
			var data_o = div_o.attr('data');
			var data_o_arr = data_o.split('_');
			var opcion_id = data_o_arr[2];
			if (class_o.indexOf(css) != -1)
			{
				opcion_ids.push(opcion_id);
			}
		});
		var opcion_str = opcion_ids.join(",");
		$('#pregunta_id'+pregunta_id).val(opcion_str);

	});

	$('.opc_lado-a, .elec-resp-asig-a').draggable({ revert: true, helper: "clone" });

	$( ".opc_lado-b, .elec-resp-asig-b" ).droppable({
     	drop: function( event, ui ) {
     		
     		var target = $(this);
     		var target_data = target.attr('data');
     		var target_style = target.attr('style');
     		var ui_data = ui.draggable.attr('data');
     		var style = ui.draggable.attr('style');
     		var tipo_elemento = ui.draggable.attr('telement');

     		// Valores asociados
      		var ui_data_arr = ui_data.split("__");
      		var p_pa = ui_data_arr[1].split("_");
      		var target_data_arr = target_data.split("__");
      		$('#'+ui_data_arr[1]).val(p_pa[1]+'_'+target_data_arr[1]);

     		if (target_style != style)
     		{
     			// Se debe desasociar su anterior selección
     			$('.opc_lado-a__'+p_pa[0]).each(function(){
     				div_a = $(this);
	      			style_a = div_a.attr('style');
	      			if (target_style == style_a)
	      			{
	      				var data_a = div_a.attr('data');
	      				var data_a_arr = data_a.split("__");
	      				var p_pa_a = data_a_arr[1].split("_");
	      				$('#'+data_a_arr[1]).val(p_pa_a[1]+'_0');
	      			}
	      		});
     		}
      		
      		// Chequeamos que otra opción ya no esté seleccionada con la misma pregunta
      		$('.opc_lado-b__'+p_pa[0]).each(function(){
      			div_b = $(this);
      			style_b = div_b.attr('style');
      			if (style == style_b)
      			{
      				// Remover el border-color
      				div_b.attr("style", "");
      				if (tipo_elemento == 2)
		      		{
		      			div_b.children().attr( "style", "" );
		      		}
      			}
      		});
      		target.attr("style", style);
      		if (tipo_elemento == 2)
      		{
      			target.children().attr( "style", style );
      		}
        	
      	}
    });

});
