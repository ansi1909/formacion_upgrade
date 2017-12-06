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

});

function responsive_filemanager_callback(field_id){
	
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);
	
	$('#figure').html('<img src="'+url+'" width="100%">');
	
}