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
	var usuario_id = $('#usuario_id').val();
	var url_ajaxImg = $('#url_ajaxImg').val();
	$('#'+field_id).val(new_image);
	$.ajax({
		type: "POST",
		url: url_ajaxImg,
		async: true,
		data: {  usuario_id: usuario_id, new_image: new_image },
		dataType: "json",
		success: function(data) {
			$('#figure').html('<img src="'+url+'" class="img-user-pic">');
		},
		error: function(){
			
		}
	});
}