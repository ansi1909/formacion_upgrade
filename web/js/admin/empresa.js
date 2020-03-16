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
    	var pais_id = $(this).val();
    	$('#zona_id').empty();
    	$('#zona_id').prop("disabled",true);
		if(pais_id){
		   $.ajax({
			type: "POST",
			url: $('#url_horarios').val(),
			async: true,
		    data: { pais_id: pais_id },
		    dataType: "json",
			success: function(data) {
				if(data.ok == 1){
					$('#zona_id').append('<option value></option>');
					$('#zona_id').prop("disabled",false);
					//console.log(data.zonaHoraria);
					var horario = JSON.parse(data.zonaHoraria);
					var selected;
					for (var i = 0; i < horario.length; i++) {
						$('#zona_id').append('<option value= '+horario[i].id+' '+horario[i].selected+'>'+horario[i].zona+'</option>');
					}	   
				}else{
					console.log('Debe asociar husos horarios a este pais');
				}
			},
			error: function(){
				$('#active-error').html($('#error_msg-active').val());
				$('#div-active-alert').show();
				}
			});
		}
		else{
			alert('Debe seleccionar un id');
		}
	});



});