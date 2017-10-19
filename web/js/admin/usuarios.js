$(document).ready(function() {

	$('.tree').jstree();

	$('.cb_activo').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f');
		var usuario_id = id_arr[1];
		$('#div-alert').hide();
		$.ajax({
			type: "POST",
			url: $('#url_active').val(),
			async: true,
			data: { id: usuario_id, entity: 'AdminUsuario', checked: checked },
			dataType: "json",
			success: function(data) {
				console.log('Activación/Desactivación realizada. Id '+data.id);
			},
			error: function(){
				$('#active-error').html($('#error_msg-active').val());
				$('#div-active-alert').show();
			}
		});
	});

});
