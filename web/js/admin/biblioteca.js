$(document).ready(function() {
	
	var root_site = $('#root_site').val();
	var usuario_empresa = $('#usuario_empresa').val();

    $('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

    $('#tipo_biblioteca_id').change(function(){
        var tipo = $(this).val();
        $('#recurso1').hide();
        $('#recurso2').hide();
        $('#recurso3').hide();
        if (tipo == 1) {
        	$('#recurso1').show();
        }else if (tipo == 2 ) {
        	$('#recurso2').show();
        }else if ( tipo == 3 || tipo == 4) {
        	$('#recurso3').show();
        }
    });

	CKEDITOR.replace( 'contenido', {
		filebrowserBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/noticias'+usuario_empresa,
		filebrowserUploadUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/noticias'+usuario_empresa,
		filebrowserImageBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&rootFolder=recursos/noticias'+usuario_empresa,
		on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.contenido.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			},
			key: function() {
				var editor_data = CKEDITOR.instances.contenido.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			}
		}
	});

	$('.nextBtn').click(function(){
		// Cantidad de caracteres en el contenido
		var editor_contenido = CKEDITOR.instances.contenido.getData();
		var deslen = document.getElementById("deslen");
		deslen.value = parseInt(editor_contenido.replace(/<[^>]+>/g, '').length);
	});

});

function responsive_filemanager_callback(field_id)
{
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);

	if(field_id=="imagen")
		$('#figure').html('<img src="'+url+'" width="100%">');
	
}

