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
		$('#tutorial_id').val("");
		$('#nombre').val("");
		$('#pdf').val("");
		$('#video').val("");
		$('#div-alert').hide();
	});


	$('#guardar').click(function(){
		saveTutorial();
	});

	$('#form').submit(function(e)
	{
		e.preventDefault();
		saveTutorial();
	});

	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.edit').click(function(){
		var tutorial_id = $(this).attr('data');
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
			data: { tutorial_id: tutorial_id },
			dataType: "json",
			success: function(data) {
				$('#tutorial_id').val(tutorial_id);
				$('#nombre').val(data.nombre);
				$('#pdf').val(data.pdf);
				$('#video').val(data.video);
			},
			error: function(){
				$('alert-error').html($('#error_msg_edit').val());
				$('#div-alert').show();
			}
		});
	});

	$('.delete').click(function(){
		var tutorial_id = $(this).attr('data');
		sweetAlertDelete(tutorial_id, 'AdminTutorial');
	});


	$('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

});

function responsive_filemanager_callback(field_id){
	
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);

	$('#'+field_id).html(new_image);
	
}

function saveTutorial()
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
				$('#p-pdf').html(data.pdf);
				$('#p-video').html(data.video);
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
						var tutorial_id= $(this).attr('data');
                        sweetAlertDelete(tutorial_id,'AdminTutorial');
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