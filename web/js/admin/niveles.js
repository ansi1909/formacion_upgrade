$(document).ready(function() {

	$('.tree').jstree();

	$('#div-active-alert').hide();

	$('.new').click(function(){
		$('#empresa_id').val($(this).attr('data'));
		$('#nombre').val("");
	});


	$('#guardar').click(function(){
		saveNivel();
	});


	$('#form').submit(function(e) {
		e.preventDefault();
	  	saveNivel();
	});

	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

});

function saveNivel()
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
				$('#td-'+data.empresa_id).html(data.html);
				$('#guardar').prop('disabled', false);
				$( "#cancelar" ).trigger( "click" );
				$('.tree').jstree();
			},
			error: function(){
				$('#alert-error').html($('#error_msg-save').val());
				$('#div-alert').show();
			}
		});
	}

}