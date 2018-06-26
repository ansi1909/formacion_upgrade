$(document).ready(function() {

	$('.tree').jstree();

	$('.cb_activo').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f');
		var usuario_id = id_arr[1];
		$('#div-active-alert').hide();
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

	$('#empresa_id').change(function(){
		var empresa_id = $(this).val();
		$('#div-active-alert').hide();
		$.ajax({
			type: "GET",
			url: $('#url_niveles').val(),
			async: true,
			data: { empresa_id: empresa_id },
			dataType: "json",
			success: function(data) {
				$('#nivel_id').html(data.options);
			},
			error: function(){
				$('#active-error').html($('#error_msg-filter').val());
				$('#div-active-alert').show();
			}
		});
	});

	$('.delete').click(function(){
		var usuario_id = $(this).attr('data');
		sweetAlertDelete(usuario_id, 'AdminUsuario');
	});

	$('#buscar').click(function(){
		$(this).hide();
	});

});
