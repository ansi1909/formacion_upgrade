$(document).ready(function() {

	var root_site = $('#root_site').val();

    $('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

    CKEDITOR.replace( 'form_descripcion', {
		filebrowserBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos/paginas',
		filebrowserUploadUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos/paginas',
		filebrowserImageBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&fldr=recursos/paginas',
		on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.form_descripcion.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			},
			key: function() {
				var editor_data = CKEDITOR.instances.form_descripcion.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			}
		}
	} );

	CKEDITOR.replace( 'form_contenido', {
		filebrowserBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos/paginas',
		filebrowserUploadUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos/paginas',
		filebrowserImageBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&fldr=recursos/paginas',
		on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.form_contenido.getData();
				var elem = document.getElementById("deslen2");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			},
			key: function() {
				var editor_data = CKEDITOR.instances.form_contenido.getData();
				var elem = document.getElementById("deslen2");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			}
		}
	} );

	$('.nextBtn, .stepwizard-step').click(function(){

		// Cantidad de caracteres en la descripción
		var editor_descripcion = CKEDITOR.instances.form_descripcion.getData();
		var deslen = document.getElementById("deslen");
		deslen.value = parseInt(editor_descripcion.replace(/<[^>]+>/g, '').length);

		// Cantidad de caracteres en el contenido
		var editor_contenido = CKEDITOR.instances.form_contenido.getData();
		var deslen2 = document.getElementById("deslen2");
		deslen2.value = parseInt(editor_contenido.replace(/<[^>]+>/g, '').length);

	});

});

function responsive_filemanager_callback(field_id){
	
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);
	
	if (field_id == 'form_foto')
	{
		$('#figure').html('<img src="'+url+'" width="100%">');
	}
}