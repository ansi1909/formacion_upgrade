$(document).ready(function() {

	$('.new').click(function(){
		initModalEdit();
		enableSubmit();
		$('#tutorial_id').val("");
		$('#nombre').val("");
		$('#pdf').val("");
		$('#video').val("");
		$('#imagen').val("");
		$('#descripcion').val("");
		$('#wait').hide();
	});



	$('#guardar').click(function(){
		$('#form').submit();
		return false;
	});

	$('#form').submit(function(e) {
		e.preventDefault();
	});

	$('#form').safeform({
		submit: function(e) {

			$('#div-alert').hide();
			$('#div-error').hide();
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
						if($('#tutorial_id').val() != '')//si se edita un tutorial
						{
							table.ajax.reload(null,false);//recarga los datos de la tabla manteniendose en la pagina actual
						}
						else {
							table.ajax.reload(null,true)//recarga los datos de la tabla y la muestra desde la pagina inicial
						}
						$('#wait_tutorial').hide();
						$('#p-nombre').html(data.nombre);
						$('#p-pdf').html(data.pdf);
						$('#p-imagen').html(data.pdf);
						$('#p-video').html(data.video);
						$( "#detail-edit" ).attr( "data", data.id );
						$( "#detail-delete" ).attr("data",data.id);
						$( "#detail-edit" ).attr( "disabled", false);
						$( "#detail-delete" ).attr("disabled", false);
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
				$('#div-error').show();
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
		url: $('#url_uploadFiles_tutorial').val(),
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

	$('.form-control').focus(function(){
		$('#div-alert').hide();
		$('#div-error').hide();
		$('.form-control').removeClass('error');
	});

	$( "#BodyTable, #buttons" ).on( "click",".edit" , function (){
		document.getElementById("form").reset();
		var tutorial_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		initModalEdit();
		$('#div-error').hide();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { tutorial_id: tutorial_id },
			dataType: "json",
			success: function(data) {
				enableSubmit();
				$('#tutorial_id').val(tutorial_id);
				$('#nombre').val(data.nombre);
				$('#pdf').val(data.pdf);
				$('#video').val(data.video);
				$('#imagen').val(data.imagen);
				$('#descripcion').val(data.descripcion);
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

	$( "#BodyTable, #buttons" ).on( "click",".delete" , function (){
		var tutorial_id = $(this).attr('data');
		var ubicacion = $(this).attr('data-ubicacion');
		sweetAlertDeleteTutorial(tutorial_id,ubicacion);	
     });

	$("#BodyTable,#div-button").on("click",".button-tutorial",function(event) {
		$('#div-error').hide();
		$('#nombre,#descripcion,#pdf,#imagen,#video').removeClass('error');
	});

});

function showButtons()
{
	$('#guardar').show();
    $('#cancelar').show();
    $('#wait').hide();
	$('.uploadFileHref').show();
	return 0;
}

function hideButtons()
{
	$('.uploadFileHref').hide();
    $('#guardar').hide();
	$('#cancelar').hide();
	$('#wait').show(1000);
	return 0;
}
