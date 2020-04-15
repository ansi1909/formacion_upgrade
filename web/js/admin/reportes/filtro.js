$(document).ready(function() {

	var filtro_programas = $('#filtro_programas').val();
	var filtro_tema = $('#filtro_tema').val();
	var empresa_id = $('#empresa_id').val();
	$("#check_filtro").prop('disabled', true);

	$('#pagina_id').change(function(event) {
		if($('#pagina_id').val()!=''){
			$("#check_filtro").prop('disabled', false);

		}else{
			$("#check_filtro").prop('disabled', true);
		}

	});

	$('#check_filtro').change(function(event) {
		if($(this).is(":checked")){
			$("#desde").attr('readonly', true);
			$("#hasta").attr('readonly', true);
			$.ajax({
				type: "POST",
				url: $("#url_pagina_empresa").val(),
				async: true,
				data: { empresa_id: $("#empresa_id").val(),pagina_id: $("#pagina_id").val() },
				dataType: "json",
				success: function(data) {
					$('#desde').datepicker("setDate",data.fecha_inicio);
					$('#hasta').datepicker("setDate",data.fecha_fin);
				},
				error: function(){
					$('#div-error-server').html($('#error-msg').val());
					notify($('#div-error-server').html());
				}
			});
		}else{
			$("#desde").attr('readonly', false);
			$("#hasta").attr('readonly', false);

		}
    });
	
	if (filtro_programas == '1')
	{

		$('#empresa_id').change(function(){
			$('#reporte').hide();
			selectProgramas();
		});

		if (empresa_id != '')
		{
			selectProgramas();
		}

	}

	if (filtro_tema == '1')
	{

		$('#empresa_id').change(function(){
			$('#reporte').hide();
			selectProgramas();
			observePagina();
		});

		if (empresa_id != '')
		{
			selectProgramas();
			observePagina();
		}
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

function selectProgramas()
{
	$('#pagina_id').hide();
	$('#pagina-loader').show();
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
}

function observePagina()
{
	$('#pagina_id').change(function(){
		$('#tema_id').hide();
		$('#tema-loader').show();
		$('#reporte').hide();
		$.ajax({
			type: "GET",
			url: $("#url_temas").val(),
			async: true,
			data: { empresa_id: $("#empresa_id").val() , pagina_id: $("#pagina_id").val() },
			dataType: "json",
			success: function(data) {
				$('#tema_id').html(data.options);
				$('#tema_id').show();
				$('#tema-loader').hide();
			},
			error: function(){
				$('#div-error-server').html($('#error-msg-paginas').val());
				notify($('#div-error-server').html());
			}
		});
	});
}
