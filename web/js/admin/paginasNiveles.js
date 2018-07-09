$(document).ready(function() {

    $('#empresa_id').change(function(){
    	console.log('Usuario admin');
    	var empresa_id = $(this).val();
		getListadoNiveles(empresa_id);
	});

	afterPaginate();

    observe();

	clearTimeout( timerId );

});


function getListadoNiveles(empresa_id){
	$.ajax({
		type: "GET",
		url: $('#url_niveles').val(),
		async: true,
		data: { empresa_id: empresa_id},
		dataType: "json",
		success: function(data) {
			$('#lpe').html(data.niveles);
			$('#id_empresa').val(empresa_id);
			$('#div-paginas').hide();
			$('#div-grupos').show();
			$('#nombre-p').html(data.empresa);
			afterPaginate();
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}


function observe()
{
	$('.cb_activo').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f');
		var pagina_id = id_arr[1];
		var nivel_id = $('#id_nivel').val();
		$('#div-alert').hide();
		$.ajax({
			type: "POST",
			url: $('#url_asignarP').val(),
			async: true,
			data: { id_pagina: pagina_id, id_nivel: nivel_id, entity: 'CertiNivelPagina', checked: checked },
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
}

function afterPaginate(){
	$('.see').click(function(){
		var nivel_id = $(this).attr('data');
		$('#div-active-alert').hide();
		$.ajax({
			type: "GET",
			url: $('#url_ver').val(),
			async: true,
			data: { nivel_id: nivel_id },
			dataType: "json",
			success: function(data) {
				$('#paginas').html(data.paginas);
				$('#nivelTitle').html(data.nombre);
				$('#div-paginas').show();
				observe();
			},
			error: function(){
				$('#active-error').html($('#error_msg-subapps').val());
				$('#div-active-alert').show();
				$('#div-paginas').hide();
			}
		});
	});


}
