$(document).ready(function() {

	$('#div-active-alert').hide();

	$('.new').click(function(){
		initModalEdit();
		enableSubmit();
		$('#liga_id').val("");
		$('#nombre').val("");
		$('#descripcion').val("");
		$('#minimo').val("");
		$('#maximo').val("");
		$('#imagen').val("");
		
	});


	$('#guardar').click(function(){
		console.log('Aqui');
        if ($("#form").valid())
		{console.log('aqui');
		    $('#form').submit();
        }
		return false;
	});

	$('#form').submit(function(e) {
		e.preventDefault();
	});
 
 	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.edit').click(function(){
		var liga_id = $(this).attr('data');
        console.log(liga_id);
		var url_edit = $('#url_edit').val();
		initModalEdit();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { liga_id: liga_id },
			dataType: "json",
			success: function(data) {
				console.log(data);
				enableSubmit();
				$('#liga_id').val(liga_id);
				$('#nombre').val(data.nombre);
				$('#descripcion').val(data.descripcion);
				$('#minimo').val(data.minimo);
				$('#maximo').val(data.maximo);
				$('#imagen').val(data.imagen);
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

	$('.delete').click(function(){
		var liga_id = $(this).attr('data');
        sweetAlertDelete(liga_id,'AdminLigas');
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
						$('.form-control').val('');
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
							$('.delete').unbind('click');
							$('.delete').click(function()
							{
								var liga_id= $(this).attr('data');
		                        sweetAlertDelete(liga_id,'AdminLigas');
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
						$('#guardar').prop('disabled', false);
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

	$('.uploadFileHref').click(function(){
		$('#fileUpload').val($(this).attr('data-etiqueta'));
		$('#div-error').hide();
	});

	$('.uploadFile').fileupload({
		url: $('#url_uploadFiles_liga').val(),
        dataType: 'json',
        done: function (e, data) {
        	var id = $('#fileUpload').val();
        	if (data.result.response.files[0].error) {
			     getErrorUploadHandler(data.result.response.files[0].error,id);
			 }
        	
        	$.each(data.result.response.files, function (index, file) 
        	{
        		$('#'+id).val(file.name);
            });
            showButtons();
           // $('#div-error').hide();
        },
        add: function (e,data ){
        	 hideButtons();
	  		 data.submit();
        },
        fail: function(e, data){
        	failedRequest();
        }
    });



function hideButtons()
{
	$('.uploadFileHref').hide();
    $('#guardar').hide();
	$('#cancelar').hide();
	$('#wait').show(1000);
	return 0;
}

	disableSubmit();

});
