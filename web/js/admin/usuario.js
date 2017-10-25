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