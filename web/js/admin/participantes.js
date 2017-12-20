$(document).ready(function() {

	var usuario_empresa = $("#usuario_empresa").val();
	if (usuario_empresa != '0'){
		console.log('Usuario empresa');
		getNiveles(usuario_empresa);
	}

    $('#empresa_id').change(function(){
    	console.log('Usuario admin');
    	var empresa_id = $(this).val();
    	var nivel_id = 0;
		getNiveles(empresa_id);
		getListadoParticipantes(empresa_id,nivel_id);
	});

	$('#nivel_id').change(function(){
		var nivel_id = $(this).val();
		var empresa_id = $('#empresa_id').val();
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
		url: $('#url_participantes').val(),
		async: true,
		data: { nivel_id: nivel_id, empresa_id: empresa_id },
		dataType: "json",
		success: function(data) {
			$('#usuarios').html(data.usuarios);
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

