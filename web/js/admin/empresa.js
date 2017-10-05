$(document).ready(function() {
	
	$('.cb_activo').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f');
		var app_id = id_arr[1];
		$('#div-alert').hide();
		$.ajax({
			type: "POST",
			url: $('#url_active').val(),
			async: true,
			data: { app_id: app_id, checked: checked },
			dataType: "json",
			success: function(data) {
				console.log('Activación/Desactivación realizada. Id '+data.id);
			},
			error: function(){
				$('#active-error').html($('#error_msg-active').val());
				$('#div-active-alert').show();
			}
		});
	});

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

});