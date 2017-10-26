$(document).ready(function() {
	
	var root_site = $('#root_site').val();

	$("#form").validate({
        errorLabelContainer: "#alert-error",
        wrapper: "li",
        debug: true,
        rules: {
            'nombre': {
                required: true,
                minlength: 3
            }
        },
        messages: {
            'nombre': {
                required: "Este campo es requerido",
                minlength: "Debe ser mínimo de 3 caracteres."
            }
        }
    });

    $('#finish').click(function(){
    	console.log('llamando a validaaa');
    	$('.validaaa').trigger('click');
    });

    $('#finish2').click(function(){
    	/*$('#div-error').hide();
    	var str_error = validarForm();
    	if (str_error != '')
    	{
    		$('#alert-error').html(str_error);
    		$('#div-error').show();
    	}
    	else {
    		$('#form').submit();
    	}*/
    	console.log('me llamaron');
    	var valid = $("#form").valid();
    	console.log('Validación: '+valid);
    });

    $('#fecha_nacimiento').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es'
	});

	$('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

    $('#empresa_id').change(function(){
		var empresa_id = $(this).val();
		$.ajax({
			type: "GET",
			url: $('#url_niveles').val(),
			async: true,
			data: { empresa_id: empresa_id },
			dataType: "json",
			success: function(data) {
				$('#nivel_id').html(data.options);
			},
			error: function(){
				$('#active-error').html($('#error_msg-filter').val());
				$('#div-active-alert').show();
			}
		});
	});

});

function responsive_filemanager_callback(field_id){
	
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);
	
	// Se actualiza preview de la imagen
	//var root_site = $('#root_site').val();
	//var post_image = '<img src="'+root_site+'/uploads/'+new_image+'" width="100%" alt="Logo" />';
	$('#figure').html('<img src="'+url+'">');
	
}