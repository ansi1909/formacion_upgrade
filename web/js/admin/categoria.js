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
			$('#categoria_id').val("");
			$('#categoria').val("");
			$('#div-alert').hide();
			});


	$('#guardar').click(function(){
		saveCategoria();
	});


	$('#form').submit(function(e) {
		e.preventDefault();
	  	saveCategoria();
	});
 
 	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.edit').click(function(){
		var categoria_id = $(this).attr('data');
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
			data: { categoria_id: categoria_id },
			dataType: "json",
			success: function(data) {
				$('#categoria_id').val(categoria_id);
				$('#categoria').val(data.nombre);
			},
			error: function(){
				$('alert-error').html($('#error_msg_edit').val());
				$('#div-alert').show();
			}
		});
	});

	$('.delete').click(function(){
		var categoria_id = $(this).attr('data');
		sweetAlertDelete(categoria_id);
	});

});

function saveCategoria()
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
				$('#p-nombre').html(data.nombre);
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
					$('.delete').click(function()
					{
						var categoria_id= $(this).attr('data');
						sweetAlertDelete(categoria_id);
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