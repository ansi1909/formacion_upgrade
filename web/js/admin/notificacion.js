$(document).ready(function() {

	var root_site = $('#root_site').val();
	var empresa_id = $('#empresa_id').val();
	if (empresa_id)
	{
		empresa_id = empresa_id+'/';
	}

    CKEDITOR.replace( 'form_mensaje', {
		//filebrowserBrowseUrl: root_site+'/assets/vendor/ckfinder/ckfinder.html?type=Files&start=Files:/notificaciones/',
	    //filebrowserImageBrowseUrl: root_site+'/assets/vendor/ckfinder/ckfinder.html?type=Images&start=Images:/recursos/notificaciones/',
	    filebrowserUploadUrl: root_site+'/assets/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&currentFolder=/notificaciones/'+empresa_id,
	    filebrowserImageUploadUrl: root_site+'/assets/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&currentFolder=/notificaciones/'+empresa_id,
	    on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.form_mensaje.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			},
			key: function() {
				var editor_data = CKEDITOR.instances.form_mensaje.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			}
		}
	} );

});
