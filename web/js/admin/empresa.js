$(document).ready(function() {
	
	var root_site = $('#root_site').val();

    CKEDITOR.replace( 'bienvenida', {
		filebrowserBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos',
		filebrowserUploadUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos',
		filebrowserImageBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos',
		on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.bienvenida.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			},
			key: function() {
				var editor_data = CKEDITOR.instances.bienvenida.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			}
		}
	} );

	$('.nextBtn').click(function(){

		// Cantidad de caracteres en la bienvenida
		var editor_data = CKEDITOR.instances.bienvenida.getData();
		var deslen = document.getElementById("deslen");
		deslen.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);

	});

});