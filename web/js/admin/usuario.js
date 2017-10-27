$(document).ready(function() {
	
	$('#finish').click(function(){
    	var valid = $("#form").valid();
    	if (!valid) 
    	{
    		notify($('#div-error').html());
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
	
	$('#figure').html('<img src="'+url+'">');
	
}