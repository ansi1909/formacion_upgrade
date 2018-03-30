$(document).ready(function() {

   $('#cambio').click(function(){
		var p_new = $('#p_new').val();
		var p_conf = $('#p_conf').val();
		var usuario_id = $('#usuario_id').val();
		var url_ajaxClave = $('#url_ajaxClave').val();
		if (p_new && p_conf) {
			$.ajax({
				type: "POST",
				url: url_ajaxClave,
				async: true,
				data: { p_new: p_new, p_conf: p_conf, usuario_id: usuario_id },
				dataType: "json",
				success: function(data) {
					$('#mensaje').html(data.html);
					$('#mensaje').show();
					$('#cambio').hide();
					$('#aceptar').show();
				},
				error: function(){
					
				}
			});
		}
	});

   $('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

});