$(document).ready(function() {

	var root_site = $('#root_site').val();
	var usuario_empresa = $('#usuario_empresa').val();

	if (usuario_empresa == 0)
    {
        var brws_upld_url = root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos/notificaciones';
        var img_brws_url = root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&fldr=recursos/notificaciones';
    }
    else {
        var brws_upld_url = root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos/notificaciones'+usuario_empresa;
        var img_brws_url = root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&fldr=recursos/notificaciones'+usuario_empresa;
    }
	CKEDITOR.replace( 'form_mensaje', {

		filebrowserBrowseUrl : brws_upld_url,
		filebrowserUploadUrl : brws_upld_url,
		filebrowserImageBrowseUrl : img_brws_url,
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

	$('#finish').click(function(){
		$('#finish').hide();
	});

	$('#form').submit(function(e)
	{
		$('#finish').hide();
	});

});