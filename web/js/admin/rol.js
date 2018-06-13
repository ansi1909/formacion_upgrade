$(document).ready(function() {

	$('#div-active-alert').hide();

	$('.new').click(function(){
		initModalEdit();
		$('#rol_id').val("");
		$('#rol').val("");
		$('#descripcion').val("");
	});


	$('#guardar').click(function(){
		$('#form').submit();
		return false;
	});

	$('#form').submit(function(e) {
		e.preventDefault();
	});

	$('.edit').click(function(){
		var rol_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		initModalEdit();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { rol_id: rol_id },
			dataType: "json",
			success: function(data) {
				$('#rol_id').val(rol_id);
				$('#rol').val(data.nombre);
				$('#descripcion').val(data.descripcion);
			},
			error: function(){
				$('alert-error').html($('#error_msg_edit').val());
				$('#div-alert').show();
			}
		});
	});
	 
 	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.delete').click(function(){
		var rol_id = $(this).attr('data');
		sweetAlertDelete(rol_id, 'AdminRol');
	});

	$('#form').safeform({
		submit: function(e) {
			
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
						$('#p-des').html(data.descripcion);
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
								var rol_id= $(this).attr('data');
								sweetAlertDelete(rol_id, 'AdminRol');
							});
						}

						initModalShow();

						// manual complete, reenable form ASAP
						$('#form').safeform('complete');
						return false; // revent real submit

					},
					error: function(){
						$('#alert-error').html($('#error_msg-save').val());
						$('#div-alert').show();
						$('#form').safeform('complete');
                        return false; // revent real submit
					}
				});
			}
			else {
				$('#form').safeform('complete');
                return false; // revent real submit
			}
			
		}
	});

});
