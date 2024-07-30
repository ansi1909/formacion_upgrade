$(document).ready(function() {

	$('#div-active-alert').hide();

	$('.new').click(function(){
		initModalEdit();
		enableSubmit();
		$('#faq_id').val("");
		$('#tipo_pregunta_id').val("");
		$('#pregunta').val("");
		$('#respuesta').val("");
		$('#new_tipo_pregunta_id').val("");
		$('#new_tipo_pregunta_id').prop('disabled', true);
	});

	afterPaginate();

	$('.paginate_button').click(function(){
		afterPaginate();
	});

	$('#guardar').click(function(){
		$('#form').submit();
		return false;
	});

	$('#form').submit(function(e) {
		e.preventDefault();
	});
 
 	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.delete').click(function(){
		var faq_id = $(this).attr('data');
		sweetAlertDelete(faq_id, 'AdminFaqs');
	});

	$('#form').safeform({
		submit: function(e) {
			
			$('#div-alert').hide();
			if ($("#form").valid())
			{
				var tipo_pregunta_id = $('#tipo_pregunta_id').val();
				if (tipo_pregunta_id == 'add')
				{
					$('#alert-error').html($('#error_msg-tipo').val());
					$('#div-alert').show();
					$('#guardar').prop('disabled', false);
					$('#form').safeform('complete');
                    return false; // revent real submit
				}
				else {
					$('#guardar').prop('disabled', true);
					$.ajax({
						type: "POST",
						url: $('#form').attr('action'),
						async: true,
						data: $("#form").serialize(),
						dataType: "json",
						success: function(data) {
							$('#p-t').html(data.tipo_pregunta);
							$('#p-pregunta').html(data.pregunta);
							$('#p-respuesta').html(data.respuesta);
							console.log('Formulario enviado. Id '+data.id);
							$( "#detail-edit" ).attr( "data", data.id );
							$( "#detail-delete" ).attr("data",data.id);
							$('.delete').unbind('click');
							$('.delete').click(function()
							{
								var faq_id= $(this).attr('data');
								sweetAlertDelete(faq_id, 'AdminFaqs');
							});

							initModalShow();

							// manual complete, reenable form ASAP
							$('#form').safeform('complete');
							return false; // revent real submit

						},
						error: function(){
							$('#alert-error').html($('#error_msg-save').val());
							$('#div-alert').show();
							$('#guardar').prop('disabled', false);
							$('#form').safeform('complete');
	                        return false; // revent real submit
						}
					});
				}
			}
			else {
				$('#form').safeform('complete');
                return false; // revent real submit
			}
			
		}
	});

	$('#tipo_pregunta_id').change(function(){
		var tipo_pregunta_id = $('#tipo_pregunta_id').val();
		if (tipo_pregunta_id == 'add')
		{
			$('#new_tipo_pregunta_id').prop('disabled', false);
		}
	});

	$('#add').click(function(){
		var new_tipo_pregunta = $.trim($('#new_tipo_pregunta_id').val());
		if (new_tipo_pregunta != '')
		{
			$.ajax({
				type: "POST",
				url: $('#url_nuevaPregunta').val(),
				async: true,
				data: { new_tipo_pregunta: new_tipo_pregunta },
				dataType: "json",
				success: function(data) {
					if (data.ok == 1)
					{
						$('#tipo_pregunta_id').append($('<option>', {
						    value: data.id,
						    text: new_tipo_pregunta
						}));
					}
					$('#tipo_pregunta_id').val(data.id);
					$('#new_tipo_pregunta_id').val("");
					$('#new_tipo_pregunta_id').prop('disabled', true);
				},
				error: function(){
					$('#alert-error').html($('#error_msg-type').val());
					$('#div-alert').show();
				}
			});
		}
	});

	observe();

});

function observe()
{

	$('.edit').unbind('click');
	$('.edit').click(function(){
		var faq_id = $(this).attr('data');
		initModalEdit();
		$.ajax({
			type: "GET",
			url: $('#url_edit').val(),
			async: true,
			data: { faq_id: faq_id },
			dataType: "json",
			success: function(data) {
				enableSubmit();
				$('#faq_id').val(faq_id);
				$('#tipo_pregunta_id').val(data.tipo_pregunta_id);
				$('#pregunta').val(data.pregunta);
				$('#respuesta').val(data.respuesta);
				$('#new_tipo_pregunta_id').val('');
				$('#new_tipo_pregunta_id').prop('disabled', true);
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

	$('.delete').unbind('click');
	$('.delete').click(function(){
		var faq_id = $(this).attr('data');
		sweetAlertDelete(faq_id, 'AdminFaqs');
	});

}

function afterPaginate(){

	$('.see').unbind('click');
	$('.see').click(function(){
		var faq_id = $(this).attr('data');
		$('#div-respuesta').show();
		$('#loader').show();
		$('#answer').hide();
		$('#div-active-alert').hide();
		$.ajax({
			type: "GET",
			url: $('#url_respuesta').val(),
			async: true,
			data: { faq_id: faq_id },
			dataType: "json",
			success: function(data) {
				$('#loader').hide();
				$('#answer').html(data.respuesta);
				$('#answer').show();
				observe();
			},
			error: function(){
				$('#active-error').html($('#error_msg-subapps').val());
				$('#loader').hide();
				$('#div-active-alert').show();
				$('#div-respuesta').hide();
			}
		});
	});
}
