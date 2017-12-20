$(document).ready(function() {

	$('#div-active-alert').hide();

	$('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

	$('#guardar').click(function(){
		$('#div-alert').hide();
		$('#guardar').hide();
        var valid = $("#form").valid();
        if (!valid) 
        {
            notify($('#div-error').html());
            $('#guardar').show();
        }
        else {
            var pregunta_opcion_id = $('#pregunta_opcion_id').val()
			$.ajax({
				type: "POST",
				url: $('#form').attr('action'),
				async: true,
				data: $("#form").serialize()+'&es_asociacion='+$('#es_asociacion').val(),
				dataType: "json",
				success: function(data) {
					if (pregunta_opcion_id)
					{
						$( "#tr-"+pregunta_opcion_id ).html( data.html );
					}
					else {
						$( "#tbody-options" ).append( data.html );
					}
					// Si se marca SI, las demás deben marcarse como NO
					if (data.checked != '' && $('#es_asociacion').val() == 0)
					{
						$('.cb_activo').each(function(){
							if ($(this).attr('id') != 'f'+data.id)
							{
								$(this).prop('checked', false);
							}
						});
					}
					observe();
					$( "#cancelar" ).trigger( "click" );
					clearTimeout( timerId );
				},
				error: function(){
					$('#alert-error').html($('#error_msg-save').val());
					$('#div-alert').show();
					$('#guardar').show();
				}
			});
        }
    });

	$('.new').click(function(){
		$('#div-alert').hide();
		$('#pregunta_opcion_id').val("");
		$('#descripcion').val("");
		$('#enunciado').val("");
		$('#imagen').val("");
		$('#figure_imagen').html('<img src="'+$('#img_default').val()+'" style="background: transparent; width: 150px; height: auto;">');
		$('#imagen_enunciado').val("");
		$('#figure_imagen_enunciado').html('<img src="'+$('#img_default').val()+'" style="background: transparent; width: 150px; height: auto;">');
		$('#guardar').show();
	});

	observe();

});

function observe()
{

	var es_asociacion = $('#es_asociacion').val();
	var elemento_imagen = $('#elemento_imagen').val();

	$('.edit').click(function(){
		var pregunta_opcion_id = $(this).attr('data');
		$('#div-alert').hide();
		$('#guardar').show();
		$.ajax({
			type: "GET",
			url: $('#url_edit').val(),
			async: true,
			data: { pregunta_opcion_id: pregunta_opcion_id },
			dataType: "json",
			success: function(data) {
				$('#pregunta_opcion_id').val(pregunta_opcion_id);
				$('#descripcion').val(data.descripcion);
				if (elemento_imagen == 1)
				{
					$('#imagen').val(data.imagen);
					var figure_imagen = data.url_imagen ? data.url_imagen : $('#img_default').val();
					$('#figure_imagen').html('<img src="'+figure_imagen+'" style="background: transparent; width: 150px; height: auto;">');
					if (es_asociacion == 1)
					{
						$('#imagen_enunciado').val(data.imagen_enunciado);
						var figure_imagen_enunciado = data.url_imagen_enunciado ? data.url_imagen_enunciado : $('#img_default').val();
						$('#figure_imagen_enunciado').html('<img src="'+figure_imagen_enunciado+'" style="background: transparent; width: 150px; height: auto;">');
					}
				}
				if (es_asociacion == 0)
				{
					$('#correcta').prop('checked', data.correcta);
				}
				else {
					$('#enunciado').val(data.enunciado);
				}
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

	if (es_asociacion == 0)
	{
		$('.cb_activo').click(function(){
			var checked = $(this).is(':checked') ? 1 : 0;
			var id = $(this).attr('id');
			var id_arr = id.split('f');
			var pregunta_opcion_id = id_arr[1];
			$('#div-alert').hide();
			// Si se marca SI, las demás deben marcarse como NO
			if (checked == 1)
			{
				$('.cb_activo').each(function(){
					if ($(this).attr('id') != id)
					{
						$(this).prop('checked', false);
					}
				});
			}
			$.ajax({
				type: "POST",
				url: $('#url_correcta').val(),
				async: true,
				data: { pregunta_opcion_id: pregunta_opcion_id, checked: checked },
				dataType: "json",
				success: function(data) {
					console.log('Activación/Desactivación realizada. Id '+data.id);
					clearTimeout( $('#timerId').val() );
					activarTimeout();
				},
				error: function(){
					$('#active-error').html($('#error_msg-active').val());
					$('#div-active-alert').show();
				}
			});
		});
	}

	$('.delete').click(function(){
		var pregunta_opcion_id = $(this).attr('data');
		sweetAlertDelete(pregunta_opcion_id, 'CertiPreguntaOpcion', $('#url_delete_opcion').val());
	});

}

function responsive_filemanager_callback(field_id){
	
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);
	
	$('#figure_'+field_id).html('<img src="'+url+'" style="background: transparent; width: 150px; height: auto;">');
	
}