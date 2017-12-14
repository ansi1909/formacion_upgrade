$(document).ready(function() {

	var usuario_empresa = $("#usuario_empresa").val();
	if (usuario_empresa){
		console.log('Usuario de empresa');
		getNiveles(usuario_empresa);
	}

    $('#empresa_id').change(function(){
    	console.log('Usuario admin');
    	var empresa_id = $(this).val();
		getNiveles(empresa_id);
		getListadoParticipantes(empresa_id,0);
	});

	$('#nivel_id').change(function(){
		var nivel_id = $(this).val();
		getListadoParticipantes(empresa_id,nivel_id);
	});

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

function getListadoParticipantes(empresa_id,nivel_id){
	$.ajax({
		type: "GET",
		url: $('#url_Participantes').val(),
		async: true,
		data: { nivel_id: nivel_id },
		dataType: "json",
		success: function(data) {
			$('#nombre').val(data.nombre);
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

