$(document).ready(function() {

	$('#div-active-alert').hide();

	$('#guardar').click(function(){
		$('#div-alert').hide();
		if ($("#form").valid())
		{
			$('#guardar').prop('disabled', true);
			$.ajax({
				type: "POST",
				url: $('#form').attr('action'),
				async: true,
				data: $("#form").serialize(),
				dataType: "json",
				success: function(data) {
					$('#p-titulo').html(data.titulo);
					$('#p-contenido').html(data.contenido);
					$('#p-pdf').html(data.pdf);
					$('#p-imagen').html(data.imagen);

					$( "#detail-edit" ).attr( "data", data.id );
					if (data.delete_disabled != '')
					{
						$( "#detail-delete" ).hide();
						$( "#detail-delete" ).removeClass( "delete" );
					}
					else {
						$( "#detail-delete" ).attr( "data", data.id );
						$( "#detail-delete" ).addClass( "delete" );
						$( "#detail-delete" ).show();
						$('.delete').click(function(){
							var app_id = $(this).attr('data');
							sweetAlertDelete(app_id, 'AdminNoticia');
						});
					}
					$('#form').hide();
					$('#alert-success').show();
					$('#detail').show();
					$('#aceptar').show();
					$('#guardar').hide();
					$('#cancelar').hide();
				},
				error: function(){
					$('#alert-error').html($('#error_msg-save').val());
					$('#div-alert').show();
					$('#guardar').prop('disabled', false);
				}
			});
		}
	});

	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.new').click(function(){
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$('#div-alert').hide();
		$('#app_id').val("");
		$('#nombre').val("");
		$('#url').val("");
		$('#icono').val("");
	});

	observe();

});

function observe()
{

	$('.edit').click(function(){
		var app_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		$('#guardar').prop('disabled', false);
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$('#div-alert').hide();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { app_id: app_id },
			dataType: "json",
			success: function(data) {
				$('#app_id').val(app_id);
				$('#nombre').val(data.nombre);
				$('#url').val(data.url);
				$('#icono').val(data.icono);
				$('#activo').prop('checked', data.activo);
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

	$('.delete').click(function(){
		var app_id = $(this).attr('data');
		sweetAlertDelete(app_id, 'AdminNoticia');
	});

	$('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });
    
}

function responsive_filemanager_callback(field_id){
	
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);
	
	$('#figure').html('<img src="'+url+'">');
	
}