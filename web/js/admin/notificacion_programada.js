$(document).ready(function() {

	var tipo_destino_id = $('#tipo_destino_id').val();
	if (tipo_destino_id == 4)
	{
		observeMultiSelect();
	}

    $('#fecha_difusion').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es'
	});

	$('#tipo_destino_id').change(function(){
		var tipo_destino_id = $('#tipo_destino_id').val();
		var notificacion_id = $('#notificacion_id').val();
		var notificacion_programada_id = $('#notificacion_programada_id').val();
		if (tipo_destino_id != '')
		{
			$('#change').show();
			$('.load1').show();
			$('#div-entidades-alert').hide();
			$('#div-grupo').hide();
			$.ajax({
				type: "GET",
				url: $('#url_grupo').val(),
				async: true,
				data: { tipo_destino_id: tipo_destino_id, notificacion_id: notificacion_id, notificacion_programada_id: notificacion_programada_id },
				dataType: "json",
				success: function(data) {
					$('#div-entidades').html(data.html);
					if (tipo_destino_id == 4)
					{
						observeMultiSelect();
					}
				},
				error: function(){
					$('.load1').hide();
					$('#div-entidades-alert').show();
				}
			});
		}
	});

});
