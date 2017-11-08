$(document).ready(function() {
	
	var root_site = $('#root_site').val();

    $('#finish').click(function(){
    	$('#div-error').hide();
    	var str_error = validarForm();
    	if (str_error != '')
    	{
    		$('#alert-error').html(str_error);
    		$('#div-error').show();
    	}
    	else {
    		$('#form').submit();
    	}
    });

    CKEDITOR.replace( 'bienvenida', {
		filebrowserBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&fldr=recursos',
		filebrowserUploadUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&fldr=recursos',
		filebrowserImageBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&fldr=recursos',
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

});