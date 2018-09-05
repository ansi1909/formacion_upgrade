$(document).ready(function() {

	$('#div-active-alert').hide();

	$('.new').click(function(){
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$('#faq_id').val("");
		$('#pregunta').val("");
		$('#respuesta').val("");
		$('#tipo_pregunta_id').val("");
		$('#new_pregunta').val("");
		$('#div-alert').hide();
	});

	afterPaginate();

	$('#guardar').click(function(){
		saveFaq();
	});


	$('.nuevap').click(function(){
		nuevaPregunta();
	});
	 
 	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.delete').click(function(){
		var faq_id = $(this).attr('data');
		sweetAlertDelete(faq_id, 'AdminFaqs');
	});

	observe();

});

function observe()
{

	$('.edit').click(function(){
		var faq_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		$('#guardar').prop('disabled', false);
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$('#div-alert').hide();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { faq_id: faq_id },
			dataType: "json",
			success: function(data) {
				$('#faq_id').val(faq_id);
				$('#pregunta').val(data.pregunta);
				$('#respuesta').val(data.respuesta);
				$('#id_tipo_pregunta').val(data.id_tipo_pregunta)
				$('#tipo_pregunta_id').html(data.tipo_pregunta);
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

function saveFaq()
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
					$('#p-pregunta').html(data.pregunta);
					$('#p-respuesta').html(data.respuesta);
					$('#p-t').html(data.tipo_pregunta);
					console.log('Formulario enviado. Id '+data.id);
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
						$('.delete').unbind('click');
						$('.delete').click(function()
						{
							var faq_id= $(this).attr('data');
							sweetAlertDelete(faq_id, 'AdminFaqs');
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
					$('#alert-error').html($('#error_msg-save').val());
					$('#div-alert').show();
				}
			});
		}
}

function afterPaginate(){
	$('.see').click(function(){
		var faq_id = $(this).attr('data');
		$('#div-respuesta').show();
		$('#loader').show();
		$('#respuesta_v').hide();
		$('#div-active-alert').hide();
		$.ajax({
			type: "GET",
			url: $('#url_respuesta').val(),
			async: true,
			data: { faq_id: faq_id },
			dataType: "json",
			success: function(data) {
				$('#respuesta_v').html(data.respuesta);
				$('#loader').hide();
				$('#respuesta_v').show();
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

function nuevaPregunta()
{		
	var url_nuevaPregunta = $('#url_nuevaPregunta').val();
	$('#nuevap').prop('disabled', true);
	$.ajax({
		type: "POST",
		url: url_nuevaPregunta,
		async: true,
		data: $("#form").serialize(),
		dataType: "json",
		success: function(data) {
			$('#tipo_pregunta_id').html(data.tipo_pregunta);
			$('#new_pregunta').val("");
		},
		error: function(){
			$('#alert-error').html($('#error_msg-save').val());
			$('#div-alert').show();
		}
	});
}