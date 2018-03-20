$(document).ready(function() {
	var reporte = $("#reporte").val();
	var usuario_empresa = $("#usuario_empresa").val();
	var nivel_id = 0;
	var pagina_id = 0;
	if ( reporte == '1') 
	{
		if (usuario_empresa != '0'){
			getNiveles(usuario_empresa);
			getListadoParticipantes(usuario_empresa, nivel_id, pagina_id, reporte);
		}

	    $('#empresa_id').change(function(){
	    	$('#div-active-alert').hide();
	    	var empresa_id = $(this).val();
	    	var nivel_id = 0;
			getNiveles(empresa_id);
			getListadoParticipantesE(empresa_id,nivel_id);
		});

		$('#nivel_id').change(function(){
			$('#div-active-alert').hide();
			var nivel_id = $(this).val();
			var empresa_id = $('#empresa_id').val();
			getListadoParticipantesE(empresa_id,nivel_id);
		});

		$('.paginate_button').click(function(){
	        afterPaginate();
	    });
	}
	else
		if ( reporte == '2')
		{
			if (usuario_empresa != '0'){
				getProgramas(usuario_empresa);
			}

			$('#empresa_id').change(function(){
				$('#div-active-alert').hide();
		    	var empresa_id = $(this).val();
				getProgramas(empresa_id);
			});

			$('#programa_id').change(function(){
				$('#div-active-alert').hide();
				var pagina_id = $(this).val();
				var empresa_id = $('#empresa_id').val();
				getListadoParticipantesR(empresa_id,pagina_id);
			});
		}
		else
			if ( reporte == '3')
			{
				if (usuario_empresa != '0'){
					getProgramas(usuario_empresa);
				}

				$('#empresa_id').change(function(){
					$('#div-active-alert').hide();
		    		var empresa_id = $(this).val();
					getProgramas(empresa_id);
				});
			}
			else
				if (reporte == '4') 
				{
					$('#div-active-alert').hide();
					if (usuario_empresa != '0'){
						getProgramas(usuario_empresa);
					}

					$('#empresa_id').change(function(){
		    			var empresa_id = $(this).val();
						getProgramas(empresa_id);
					});
				}
				else if (reporte == '5') 
				{
					if (usuario_empresa != '0'){
						getProgramas(usuario_empresa);
					}

					$('#empresa_id').change(function(){
						$('#div-active-alert').hide();
	    				var empresa_id = $(this).val();
						getProgramas(empresa_id);
					});
				}
});

function getNiveles(empresa_id){
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
}

function getProgramas(empresa_id){
	$.ajax({
		type: "GET",
		url: $('#url_programas').val(),
		async: true,
		data: { empresa_id: empresa_id },
		dataType: "json",
		success: function(data) {
			$('#programa_id').html(data.options);
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function getListadoParticipantes(empresa_id, nivel_id, pagina_id, reporte){
	$.ajax({
		type: "GET",
		url: $('#url_participantes').val(),
		async: true,
		data: { empresa_id: empresa_id, nivel_id: nivel_id, pagina_id: pagina_id, reporte: reporte },
		dataType: "json",
		success: function(data) {
			$('#usuarios').html(data.html);
			applyDataTable();
			clearTimeout( timerId );
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function getListadoParticipantesR(empresa_id,pagina_id){
	$.ajax({
		type: "GET",
		url: $('#url_participantesR').val(),
		async: true,
		data: { pagina_id: pagina_id, empresa_id: empresa_id },
		dataType: "json",
		success: function(data) {
			$('#usuarios').html(data.html);
			applyDataTable();
			clearTimeout( timerId );
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}
