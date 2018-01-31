$(document).ready(function() {

    $('#select_empresa_id').change(function(){
    	console.log('Usuario admin');
    	var empresa_id = $(this).val();
		getListadoNotificaciones(empresa_id);
		$('#loading').hide();
		$('#history_message').show();
		
	});
	$('#tipo_destino_id').change(function(){
		$('#formulario_ajax').html('');
		$('#loading_form').show();
    	var tipo_destino_id = $(this).val();
    	var notificacion_id = $('#notificacion_id').val();
		getformularioProgramaciones(tipo_destino_id, notificacion_id);
		
	});
	

	observe();
});

function observe(){

	$('.tree').jstree();

	$('.see').click(function(){
		var notificacion_id = $(this).attr('data');
		$('#div-active-alert').hide();
		$('#tbody_history_programation').hide();
		$('#loading').show();
		$('#history_message').hide();
		$.ajax({
			type: "GET",
			url: $('#url_programation').val(),
			async: true,
			data: { notificacion_id: notificacion_id },
			dataType: "json",
			success: function(data) {
				$('.tree').jstree();
				$('#tbody_history_programation').html(data.html);
				$('#tbody_history_programation').show();
				$('#notificacionTitle').html(data.notificacion);
				$('#loading').hide();
				$('.tree').jstree();
				editProgramacion()
			},
			error: function(){
				$('.tree').jstree();
				$('#active-error').html($('#error_msg_history').val());
				$('#div-active-alert').show();
				$('#history_message').show();
				$('.tree').jstree();
				editProgramacion()
			}
		});
	});

	$('.add').click(function(){
		$('#tipo_destino_id').val('');
		$('#entidad_id').val('');
		$('.jsonfileds').hide();
		var notificacion_id = $(this).attr('data');
		$('#notificacion_id').val(notificacion_id);

	});

	$('#cancelar').click(function(){
		$('#tipo_destino_id').val('');
		$('#entidad_id').val('');
		$('.jsonfileds').hide();
		$('.alert-danger').hide();

	});

	$('.delete').click(function(){

		var notificacion_id = $(this).attr('data');

		sweetAlertDelete(notificacion_id, 'AdminNotificacionProgramada');

	});

	$('#fecha_difusion').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es'
	});
	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('#guardar').click(function(){
		saveProgramacion();
	});

	$('#form').submit(function(e)
	{
		e.preventDefault();
		saveProgramacion();
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
			$('#history_message').show();
		}
	});
}

function getformularioProgramaciones(tipo_destino_id, notificacion_id){
	$.ajax({
		type: "GET",
		url: $('#url_tipo_destino').val(),
		async: true,
		data: { tipo_destino_id: tipo_destino_id, notificacion_id: notificacion_id },
		dataType: "json",
		success: function(data) {
			$('#active-formulario-error').hide();
			$('#loading_form').hide();
			$('#formulario_ajax').html(data.formulario);
			observe();
		},
		error: function(){
			$('#loading_form').hide();
			$('#active-formulario-error').show();
		}
	});
}

function editProgramacion(){

	$('#edit_programacion').click(function(){

		var programacion_id = $(this).attr('data');
		var notificacion_id = $('#hidden_notificacion_id').val();
		$('#notificacion_id').val(notificacion_id);
		$('#programacion_id').val(programacion_id);

		$.ajax({
		type: "GET",
			url: $('#url_programation_edit').val(),
			async: true,
			data: { programacion_id: programacion_id, notificacion_id: notificacion_id },
			dataType: "json",
			success: function(data) {
				$('#active-formulario-error').hide();
				$('#loading_form').hide();
				$('#tipo_destino_id').val(data.tipo_destino);
				$('#formulario_ajax').html(data.formulario);
				observe();
			},
			error: function(){
				$('#loading_form').hide();
				$('#active-formulario-error').show();
			}
		});

	});
}

function saveProgramacion()
{
	$('#div-alert').hide();
		if ($("#form").valid())
		{
			$('#guardar').prop('disabled', true);
			$.ajax({
				type: "POST",
				url: $('#form').attr('action'),
				async: true,
				data: $("#form").serialize(),
				dataType: "json",
				success: function(data) {
					$('#p-asunto').html(data.asunto);
					$('#p-destino').html(data.destino);
					$('#p-fecha').html(data.fecha);
					$( "#detail-edit" ).attr( "data", data.id );
					if (data.delete_disabled != '') 
					{
						$("#detail-delete").hide();
						$("#detail-delete").removeClass( "delete" );
					}
					else
					{
						$( "#detail-delete" ).attr("data",data.id);
						$( "#detail-delete" ).addClass("delete");
						$( "#detail-delete" ).show();
						$('.delete').click(function()
						{
							var programacion_id= $(this).attr('data');
							sweetAlertDelete(programacion_id, 'AdminNotificacionProgramada');
						});
					}
					$('#form').hide();
					$('#alert-success').show();
					$('#detail').show();
					$('#aceptar').show();
					$('#guardar').hide();
					$('#cancelar').hide();
				},
				error: function(){
					$('#guardar').prop('disabled', false);
					$('#alert-error').html($('#error_msg-save').val());
					$('#div-alert').show();
				}
			});
		}
}