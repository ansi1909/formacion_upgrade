$(document).ready(function() {
	
	var root_site = $('#root_site').val();
	var usuario_empresa = '/'+$('#usuario_empresa').val();

    $('.date_picker').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    startDate: '0d',
	    clearBtn: true
	}); 

    $('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

    CKEDITOR.replace( 'resumen', {
		filebrowserBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/noticias'+usuario_empresa,
		filebrowserUploadUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/noticias'+usuario_empresa,
		filebrowserImageBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&rootFolder=recursos/noticias'+usuario_empresa,
		on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.resumen.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			},
			key: function() {
				var editor_data = CKEDITOR.instances.resumen.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			}
		}
	} );

	CKEDITOR.replace( 'contenido', {
		filebrowserBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/noticias'+usuario_empresa,
		filebrowserUploadUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/noticias'+usuario_empresa,
		filebrowserImageBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&rootFolder=recursos/noticias'+usuario_empresa,
		on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.contenido.getData();
				var elem = document.getElementById("deslen2");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			},
			key: function() {
				var editor_data = CKEDITOR.instances.contenido.getData();
				var elem = document.getElementById("deslen2");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			}
		}
	});

	$('.nextBtn, stepwizard-step').click(function(){
		// Cantidad de caracteres en el resumen
		var editor_descripcion = CKEDITOR.instances.resumen.getData();
		var deslen = document.getElementById("deslen");
		deslen.value = parseInt(editor_descripcion.replace(/<[^>]+>/g, '').length);

		// Cantidad de caracteres en el contenido
		var editor_contenido = CKEDITOR.instances.contenido.getData();
		var deslen2 = document.getElementById("deslen2");
		deslen2.value = parseInt(editor_contenido.replace(/<[^>]+>/g, '').length);
	});

	$("#btn_clear").on("click",function(event) {
        $("#imagen").val("");
        $("#figure").html('<img src="'+$('#photo').val()+'">');
    });

    $("#btn_clear2").on("click",function(event) {
        $("#pdf").val("");
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