$(document).ready(function() {
	var reporte = $("#reporte").val();
	var empresa_id = $("#usuario_empresa").val();
	var pagina_previa= $("#pagina_selected").val();
	var empresa_previa = $('#empresa_selected').val();
	var nivel_id = 0;
	var pagina_id = 0;
	if ( reporte == '1') 
	{
		if (empresa_id != '0'){
			getNiveles(empresa_id);
			getListadoParticipantes(empresa_id, nivel_id, pagina_id, reporte);
		}

		if (empresa_previa != '0') {
			getNiveles(empresa_previa);
			getListadoParticipantes(empresa_previa, nivel_id, pagina_id, reporte);
		}

	    $('#empresa_id').change(function(){
	    	$('#div-active-alert').hide();
	    	var empresa_id = $(this).val();
	    	var nivel_id = 0;
			getNiveles(empresa_id);
			getListadoParticipantes(empresa_id, nivel_id, pagina_id, reporte);
		});

		$('#nivel_id').change(function(){
			$('#div-active-alert').hide();
			var nivel_id = $(this).val();
			var empresa_id = $('#empresa_id').val();
			getListadoParticipantes(empresa_id, nivel_id, pagina_id, reporte);
		});

		$('.paginate_button').click(function(){
	        afterPaginate();
	    });
	}
	else
		if ( reporte == '2')
		{
			if (empresa_id != '0'){
				getProgramas(empresa_id,pagina_previa);
				if (pagina_previa!=0) 
				{
					getListadoParticipantes(empresa_id, nivel_id, pagina_previa, reporte);
				}
				
			}

			if (empresa_previa != '0') {
				getProgramas(empresa_previa,pagina_previa);
				getListadoParticipantes(empresa_previa, nivel_id, pagina_previa, reporte);
			}

			$('#empresa_id').change(function(){
				$('#div-active-alert').hide();
		    	var empresa_id = $(this).val();
				getProgramas(empresa_id,pagina_id);
			});

			$('#programa_id').change(function(){
				$('#div-active-alert').hide();
				var pagina_id = $(this).val();
				var empresa_id = $('#empresa_id').val();
				getListadoParticipantes(empresa_id, nivel_id, pagina_id, readyeporte);
			});
		}
		else
			if ( reporte == '3')
			{
				if (empresa_id != '0'){
					getProgramas(empresa_id,pagina_previa);
					if (pagina_previa!=0) 
					{
						getListadoParticipantes(empresa_id, nivel_id, pagina_previa, reporte);
					}
				}

				if (empresa_previa != '0') {
					getProgramas(empresa_previa,pagina_previa);
					getListadoParticipantes(empresa_previa, nivel_id, pagina_previa, reporte);
				}

				$('#empresa_id').change(function(){
					$('#div-active-alert').hide();
		    		var empresa_id = $(this).val();
					getProgramas(empresa_id,pagina_id);
				});

				$('#programa_id').change(function(){
					$('#div-active-alert').hide();
					var pagina_id = $(this).val();
					var empresa_id = $('#empresa_id').val();
					getListadoParticipantes(empresa_id, nivel_id, pagina_id, reporte);
				});
			}
			else
				if (reporte == '4') 
				{
					$('#div-active-alert').hide();
					if (empresa_id != '0'){
						getProgramas(empresa_id,pagina_previa);
						if (pagina_previa!=0) 
						{
							getListadoParticipantes(empresa_id, nivel_id, pagina_previa, reporte);
						}
					}

					if (empresa_previa != '0') {
						getProgramas(empresa_previa,pagina_previa);
						getListadoParticipantes(empresa_previa, nivel_id, pagina_previa, reporte);
					}

					$('#empresa_id').change(function(){
		    			var empresa_id = $(this).val();
						getProgramas(empresa_id,pagina_id);
					});

					$('#programa_id').change(function(){
						$('#div-active-alert').hide();
						var pagina_id = $(this).val();
						var empresa_id = $('#empresa_id').val();
						getListadoParticipantes(empresa_id, nivel_id, pagina_id, reporte);
					});
				}
				else if (reporte == '5') 
				{
					if (empresa_id != '0'){
						getProgramas(empresa_id,pagina_previa);
						if (pagina_previa!=0) 
						{
							getListadoParticipantes(empresa_id, nivel_id, pagina_previa, reporte);
						}
					}

					if (empresa_previa != '0') {
						getProgramas(empresa_previa,pagina_previa);
						getListadoParticipantes(empresa_previa, nivel_id, pagina_previa, reporte);
					}

					$('#empresa_id').change(function(){
						$('#div-active-alert').hide();
	    				var empresa_id = $(this).val();
						getProgramas(empresa_id,pagina_id);
					});

					$('#programa_id').change(function(){
						$('#div-active-alert').hide();
						var pagina_id = $(this).val();
						var empresa_id = $('#empresa_id').val();
						getListadoParticipantes(empresa_id, nivel_id, pagina_id, reporte);
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
			$('#excel').show();
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function getProgramas(empresa_id,pagina_previa){
	$.ajax({
		type: "GET",
		url: $('#url_programas').val(),
		async: true,
		data: { empresa_id: empresa_id,pagina_previa: pagina_previa },
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
			$('#excel').show();
			applyDataTable();
			clearTimeout( timerId );
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});

	
}
