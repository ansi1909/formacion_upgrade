$(document).ready(function() {
	
	var root_site = $('#root_site').val();
	var usuario_empresa = '/'+$('#usuario_empresa').val();
	
	observe();


    $('#tipo_biblioteca_id').change(function(){
        observe();
    });

    $('.date_picker').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    startDate: '0d',
	    clearBtn: true
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

	$('.nextBtn, stepwizard-step').click(function(){
		// Cantidad de caracteres en el contenido
		var editor_contenido = CKEDITOR.instances.contenido.getData();
		var deslen = document.getElementById("deslen");
		deslen.value = parseInt(editor_contenido.replace(/<[^>]+>/g, '').length);
	});

	$("form :input").attr("autocomplete", "off");
        
    $('.uploadFileHref').click(function(){
		$('#file_input').val($(this).attr('data-etiqueta'));
		$('#div-error').hide();
	});

	$('.fileupload').click(function(){
		$('#foto_img').hide();
		$('.load1').show();
	});

	$('.fileupload').fileupload({
		
        url: $('#url_upload').val(),
        dataType: 'json',
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png|pdf)$/i,
        add: function (e, data) {
            var goUpload = true;
            var uploadFile = data.files[0];
            var file_input = $('#file_input').val();
            if (!(/\.(jpg|jpeg|tiff|png)$/i).test(uploadFile.name) && file_input == 'imagen') {
                $('#div-error ul').html("<li>- Debes seleccionar sólo archivo de imagen</li>");
                goUpload = false;
            }
            if (!(/\.(pdf)$/i).test(uploadFile.name) && file_input == 'pdf') {
                $('#div-error ul').html("<li>- Debes seleccionar sólo archivo PDF</li>");
                goUpload = false;
			}
			if(!(/\.(mp3)$/i).test(uploadFile.name) && file_input == 'audio')
			{
				$('#div-error ul').html("<li>- Debes seleccionar sólo archivo MP3</li>");
                goUpload = false;
			}
			if(!(/\.(mp4|webm|ogv)$/i).test(uploadFile.name) && file_input == 'video')
			{
				$('#div-error ul').html("<li>- Debes seleccionar sólo archivo mp4|webm|ogv</li>");
                goUpload = false;
			}
            if (goUpload == true) {
                data.submit();
            }
            else {
                $('#div-error ul').show();
                notify($('#div-error').html());
            }
        },
        done: function (e, data) {
            $.each(data.result.response.files, function (index, file) {
                var file_input = $('#file_input').val();
                var uploads = $('#uploads').val();
				var base_upload = $('#base_upload').val();
				console.log(base_upload+'aqui');
                if (file_input == 'imagen')
                {
                    var img = $('#foto_img');
					img.attr("src", uploads+base_upload+file.name);
					$('#foto_img').show();
					$('.load1').hide();
                }
                $('#'+file_input).val(base_upload+file.name);
            });
        }
    });

    $("#btn_clear").on("click",function(event) {
        $("#imagen").val("");
        $("#figure").html('<img id="foto_img" src="'+$('#photo').val()+'" style="width: 512px; height: auto; margin: 0 1rem;">');
    });

    $("#btn_clear_video").on("click",function(event) {
        $("#video").val("");
    });

    $("#btn_clear_pdf").on("click",function(event) {
        $("#pdf").val("");
    });

    $("#btn_clear_audio").on("click",function(event) {
        $("#audio").val("");
    });

});

function responsive_filemanager_callback(field_id)
{

	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	console.log('field_id: '+field_id+'. new_image: '+new_image);
	$('#'+field_id).val(new_image);

	if (field_id == "imagen")
		$('#figure').html('<img src="'+url+'" width="100%">');
	
}

function observe()
{

	var tipo_biblioteca_id = $('#tipo_biblioteca_id').val();

	if (tipo_biblioteca_id != '')
	{
		if (tipo_biblioteca_id == 1)
		{
			$('#autor').prop('disabled', true);
			$('#recurso1').show();
			$('#recurso2').hide();
			$('#recurso3').hide();
		}
		else if (tipo_biblioteca_id == 2)
		{
			$('#autor').prop('disabled', true);
			$('#recurso1').hide();
			$('#recurso2').show();
			$('#recurso3').hide();
		}
		else if (tipo_biblioteca_id == 3 || tipo_biblioteca_id == 4)
		{
			$('#autor').prop('disabled', false);
			$('#recurso1').hide();
			$('#recurso2').hide();
			$('#recurso3').show();
		}
	}
	else {
		$('#autor').prop('disabled', true);
		$('#recurso1').hide();
		$('#recurso2').hide();
		$('#recurso3').hide();
	}

}