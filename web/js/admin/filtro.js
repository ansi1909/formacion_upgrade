$(document).ready(function() {

    $('.datePicker').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es'
	});

	$('#form').submit(function(e) {
		e.preventDefault();
	});

	$('#search').click(function(){
		var valid = $("#form").valid();
        if (!valid) 
        {
            notify($('#div-error').html());
        }
        else {
        	$('#form').submit();
			return false;
        }
	});

	$('#form').safeform({
		submit: function(e) {
			$('#reporte').show();
        	$('.load1').show();
        	$('#search').hide();
			$.ajax({
				type: "POST",
				url: $('#form').attr('action'),
				async: true,
				data: $("#form").serialize(),
				dataType: "json",
				success: function(data) {
					$('#resultado').html(data.resultado);
					$('#form').safeform('complete');
					return false;
				},
				error: function(){
					$('#div-error-server').html($('#error-msg').val());
					notify($('#div-error-server').html());
					$('#reporte').hide();
		        	$('.load1').hide();
		        	$('#search').show();
					$('#form').safeform('complete');
                    return false;
				}
			});
		}
	});

});
