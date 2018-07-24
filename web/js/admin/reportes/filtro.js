$(document).ready(function() {

	var filtro_programas = $('#filtro_programas').val();

	if (filtro_programas == '1')
	{
		$('#empresa_id').change(function(){
			$('#pagina_id').hide();
			$('#pagina-loader').show();
			$('#reporte').hide();
			$.ajax({
				type: "GET",
				url: $("#url_programas").val(),
				async: true,
				data: { empresa_id: $("#empresa_id").val() },
				dataType: "json",
				success: function(data) {
					$('#pagina_id').html(data.options);
					$('#pagina_id').show();
					$('#pagina-loader').hide();
				},
				error: function(){
					$('#div-error-server').html($('#error-msg-paginas').val());
					notify($('#div-error-server').html());
				}
			});
		});
	}

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
				data: $("#form").serialize()+'&excel=0',
				dataType: "json",
				success: function(data) {
					$('.load1').hide();
		        	$('#search').show();
					mostrarReporte(data);
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
