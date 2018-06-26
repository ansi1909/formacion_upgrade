$(document).ready(function() {

	var usuario_empresa = $("#usuario_empresa").val();
	if (usuario_empresa != '0'){
		var nivel_id = 0;
		getNiveles(usuario_empresa);
		getListadoParticipantes(usuario_empresa,nivel_id);
	}

    $('#empresa_id').change(function(){
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

	$('.paginate_button').click(function(){
        afterPaginate();
    });

	observe();

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
			$('#usuarios').html(data.html);
			applyDataTable();
			observe();
			clearTimeout( timerId );
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function afterPaginate(){
    observe();
}

function observe()
{

	$('.delete').unbind('click');
    $('.delete').click(function(){
        var usuario_id = $(this).attr('data');
        sweetAlertDelete(usuario_id, 'AdminUsuario');
    });

}
