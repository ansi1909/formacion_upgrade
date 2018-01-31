$(document).ready(function() {


	//var root_site = $('#root_site').val();
    $('#select_empresa_id').change(function(){
    	console.log('Usuario admin');
    	var empresa_id = $(this).val();
		getListadoNotificaciones(empresa_id);
		$('#history_programation').hide();
		
	});
	observe();
});

function observe(){
	$('.delete').click(function(){

		var notificacion_id = $(this).attr('data');

		sweetAlertDelete(notificacion_id, 'AdminNotificacion');

	});
}

function getListadoNotificaciones(empresa_id){
	$.ajax({
		type: "GET",
		url: $('#url_notificaciones').val(),
		async: true,
		data: { empresa_id: empresa_id },
		dataType: "json",
		success: function(data) {
			$('#list_notificaciones').html(data.notificaciones);
			observe();
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}