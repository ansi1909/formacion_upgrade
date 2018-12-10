$(document).ready(function() {
	
	var reporte = $("#reporte").val();
	var empresa_id = $("#usuario_empresa").val();
	var pagina_selected = $("#pagina_selected").val();
	var empresa_selected = $('#empresa_selected').val();
	var nivel_id = 0;
	var pagina_id = 0;
	
	if (reporte == '1') 
	{
		if (empresa_id != '0'){
			getNiveles(empresa_id);
			getListadoParticipantes(empresa_id, nivel_id, pagina_id, reporte);
		}

		if (empresa_selected != '0') {
			getNiveles(empresa_selected);
			getListadoParticipantes(empresa_selected, nivel_id, pagina_id, reporte);
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
	else if (reporte == '2') {
		
		if (empresa_id != '0') 
		{
			getProgramas(empresa_id,pagina_selected);
			if (pagina_selected!=0) 
			{
				getListadoParticipantes(empresa_id, nivel_id, pagina_selected, reporte);
			}
		}

		if (empresa_selected != '0') {
			getProgramas(empresa_selected,pagina_selected);
			getListadoParticipantes(empresa_selected, nivel_id, pagina_selected, reporte);
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
		
	} else if (reporte == '3') {
		
		if (empresa_id != '0')
		{
			getProgramas(empresa_id,pagina_selected);
			if (pagina_selected!=0) 
			{
				getListadoParticipantes(empresa_id, nivel_id, pagina_selected, reporte);
			}
		}

		if (empresa_selected != '0') {
			getProgramas(empresa_selected,pagina_selected);
			getListadoParticipantes(empresa_selected, nivel_id, pagina_selected, reporte);
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

	} else if (reporte == '4') {
					
		$('#div-active-alert').hide();
		
		if (empresa_id != '0') 
		{
			getProgramas(empresa_id, pagina_selected);
			if (pagina_selected!=0) 
			{
				getListadoParticipantes(empresa_id, nivel_id, pagina_selected, reporte);
			}
		}

		if (empresa_selected != '0') {
			getProgramas(empresa_selected,pagina_selected);
			getListadoParticipantes(empresa_selected, nivel_id, pagina_selected, reporte);
		}

		$('#empresa_id').change(function(){
			var empresa_id = $(this).val();
			getProgramasA(empresa_id, pagina_id);
		});

		$('#programa_id').change(function(){
			$('#div-active-alert').hide();
			var pagina_id = $(this).val();
			var empresa_id = $('#empresa_id').val();
			getListadoParticipantes(empresa_id, nivel_id, pagina_id, reporte);
		});

		$('#finish').click(function(){
			getParticipantesA();
		});

	} else if (reporte == '5') {
					
		if (empresa_id != '0')
		{
			getProgramas(empresa_id,pagina_selected);
			if (pagina_selected!=0) 
			{
				getListadoParticipantes(empresa_id, nivel_id, pagina_selected, reporte);
			}
		}

		if (empresa_selected != '0') {
			getProgramas(empresa_selected,pagina_selected);
			getListadoParticipantes(empresa_selected, nivel_id, pagina_selected, reporte);
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
				
	} else if (reporte == '6')  {

		if (empresa_id != '0')
		{
			var empresa_id = $('#empresa_id').val();
			getLecciones(empresa_id);
		}

		$('#empresa_id').change(function(){
			$('#div-active-alert').hide();
			var empresa_id = $(this).val();
			getLecciones(empresa_id);
		});

	}

});

function getNiveles(empresa_id){
	$('#nivel_id').hide();
	$('#pagina-loader').show();
	$.ajax({
		type: "GET",
		url: $('#url_niveles').val(),
		async: true,
		data: { empresa_id: empresa_id },
		dataType: "json",
		success: function(data) {
			$('#nivel_id').html(data.options);
			$('#nivel_id').show();
			$('#pagina-loader').hide();	
			$('#excel').show();
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function getParticipantesA(){
	var empresa_id = $('#empresa_id').val();
	var entidades = $('#entidades').val();
	$.ajax({
		type: "GET",
		url: $('#url_participantesA').val(),
		async: true,
		data: { empresa_id: empresa_id, entidades: entidades },
		dataType: "json",
		success: function(data) {
			$('#usuarios').show();
			$('#usuarios').html(data.entidades);
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function getLecciones(empresa_id){
	$.ajax({
		type: "GET",
		url: $('#url_lecciones').val(),
		async: true,
		data: { empresa_id: empresa_id },
		dataType: "json",
		success: function(data) {
			$('#leccion_id').html(data.str);
			$('#excel').show();
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function getProgramas(empresa_id,pagina_selected){
	$('#programa_id').hide();
	$('#pagina-loader').show();
	$.ajax({
		type: "GET",
		url: $('#url_programas').val(),
		async: true,
		data: { empresa_id: empresa_id,pagina_selected: pagina_selected },
		dataType: "json",
		success: function(data) {
			$('#programa_id').html(data.options);
			$('#programa_id').show();
			$('#pagina-loader').hide();
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function getProgramasA(empresa_id,pagina_selected){
	$('#programa_id').hide();
	$('#change').show();
	$('.load1').show();
	$('#div-entidades-alert').hide();
	$('#div-grupo').hide();
	
	$.ajax({
		type: "GET",
		url: $('#url_grupoA').val(),
		async: true,
		data: { empresa_id: empresa_id,pagina_selected: pagina_selected },
		dataType: "json",
		success: function(data) {
			$('#change').hide();
			$('#div-grupo').show();
			$('.load1').hide();
			$('#div-entidades').html(data.html);
			observeMultiSelect();
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function getListadoParticipantes(empresa_id, nivel_id, pagina_id, reporte){
	$('#loader').show();
	$('#usuarios').hide();
	$.ajax({
		type: "GET",
		url: $('#url_participantes').val(),
		async: true,
		data: { empresa_id: empresa_id, nivel_id: nivel_id, pagina_id: pagina_id, reporte: reporte },
		dataType: "json",
		success: function(data) {
			$('#loader').hide();
			$('#usuarios').show();
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
