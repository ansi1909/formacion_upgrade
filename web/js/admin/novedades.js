$(document).ready(function() {
	
	var root_site = $('#root_site').val();

	$('.delete').click(function(){
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

	CKEDITOR.replace( 'contenido', {
		filebrowserBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos/noticias',
		filebrowserUploadUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos/noticias',
		filebrowserImageBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&fldr=recursos/noticias',
		on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.contenido.getData();
				var elem = document.getElementById("deslen3");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			},
			key: function() {
				var editor_data = CKEDITOR.instances.contenido.getData();
				var elem = document.getElementById("deslen3");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			}
		}
	} );

	// Cantidad de caracteres en el contenido
	var editor_contenido = CKEDITOR.instances.contenido.getData();
	var deslen3 = document.getElementById("deslen3");
	deslen3.value = parseInt(editor_contenido.replace(/<[^>]+>/g, '').length);

$('#guardar').click(function()
	{
		$('#div-alert').hide();
		
		// Cantidad de caracteres en la descripción
		/*var editor_descripcion = CKEDITOR.instances.contenido.getData();
		var deslen = document.getElementById("deslen");
		deslen.value = parseInt(editor_descripcion.replace(/<[^>]+>/g, '').length);*/

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
					$('#p-empresa').html(data.empresa);
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
							var noticia_id = $(this).attr('data');
							if(noticia_id!="")
								sweetAlertDelete(noticia_id, 'AdminNoticia');
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
		$('#noticia_id').val("");
		$('#empresa').val("");
		$('#titulo').val("");
		$('#contenido').val("");
		$('#pdf').val("");
		$('#imagen').val("");
	});

	observe();

});

function observe()
{

	$('.edit').click(function(){
		var noticia_id = $(this).attr('data');
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
			data: { noticia_id: noticia_id },
			dataType: "json",
			success: function(data) {
				$('#noticia_id').val(noticia_id);
				$('#empresa').val(data.empresa);
				$('#titulo').val(data.titulo);
				$('#contenido').val(data.contenido);
				$('#pdf').val(data.pdf);
				$('#imagen').val(data.imagen);
				$('#form').show();
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

	$('.delete').click(function(){
		var noticia_id = $(this).attr('data');
		sweetAlertDelete(noticia_id, 'AdminNoticia');
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