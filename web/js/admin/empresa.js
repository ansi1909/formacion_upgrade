$(document).ready(function() {
	
	var root_site = $('#root_site').val();
	var empresa_id = $('#empresa_id').val();
	if (empresa_id)
	{
		empresa_id = empresa_id+'/';
	}

    CKEDITOR.replace( 'bienvenida', {
		//filebrowserBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/empresas',
		//filebrowserUploadUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/empresas',
		//filebrowserImageBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/empresas',
		//filebrowserBrowseUrl: root_site+'/assets/vendor/ckfinder/ckfinder.html?type=Files&currentFolder=/empresas/',
	    //filebrowserImageBrowseUrl: root_site+'/assets/vendor/ckfinder/ckfinder.html?type=Images&currentFolder=/empresas/',
	    filebrowserUploadUrl: root_site+'/assets/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&currentFolder=/empresas/'+empresa_id,
	    filebrowserImageUploadUrl: root_site+'/assets/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&currentFolder=/empresas/'+empresa_id,
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

	$('.nextBtn, stepwizard-step').click(function(){

		// Cantidad de caracteres en la bienvenida
		var editor_data = CKEDITOR.instances.bienvenida.getData();
		var deslen = document.getElementById("deslen");
		deslen.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);

	});

    $("#pais_id").change(function(){
		alert("The text has been changed.");
	});

	$.ajax({
		type: "POST",
		url: $('#url_horarios').val(),
		async: true,
	    data: { pais_id: $("#pais_id:selected" ).val() },
	    dataType: "json",
		success: function(data) {
			console.log(data);
		},
		error: function(){
			$('#active-error').html($('#error_msg-active').val());
			$('#div-active-alert').show();
			}
		});

});