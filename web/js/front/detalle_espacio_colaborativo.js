$(document).ready(function() {

	var foro_main_id = $('#foro_main_id').val();

	CKEDITOR.replace( 'mensaje_response',
	{
		toolbar : 'MyToolbar',
		on: {
	        focus: function() {
	        	$('.mensaje-error').hide();
	        }
	    }
	});

	CKEDITOR.replace( 'mensaje_reResponse',
	{
		toolbar : 'MyToolbar',
		on: {
	        focus: function() {
	        	$('.mensaje-error').hide();
	        }
	    }
	});

	$('#publicar').click(function(){
        
        var editor_data = CKEDITOR.instances.mensaje_response.getData();
        var contenido = editor_data.replace(/<[^>]+>/g, '');
        
        if (contenido == ""){
            $('#mensaje-error-response').show();
        }
        else {
            $('#mensaje_content').val(editor_data);
            saveForo(foro_main_id, foro_main_id);
            CKEDITOR.instances.mensaje_response.setData('');
        }

    });

    $('#responder').click(function(){
        
        var editor_data = CKEDITOR.instances.mensaje_reResponse.getData();
        var contenido = editor_data.replace(/<[^>]+>/g, '');
        
        if (contenido == ""){
            $('#mensaje-error-reResponse').show();
        }
        else {
            $('#mensaje_content').val(editor_data);
            saveForo($('#foro_id').val(), foro_main_id);
            CKEDITOR.instances.mensaje_reResponse.setData('');
            $( "#cancelar" ).trigger( "click" );
        }

    });

    $('.cancel').click(function(){
		$('#foro_id').val(0);
		CKEDITOR.instances.mensaje_reResponse.setData('');
		$('#mensaje_content').val('');
	});


	$('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

	$('.ic-del').click(function(){
    	var foro_id = $(this).attr('data');
    	$('#foro_delete_id').val(foro_id);
    });

    $('.cancelarCs').click(function(){
    	$('#foro_delete_id').val(0);
    });

    $('#eliminar').click(function(){
    	$('.btn-modalDelete').hide();
    	var foro_id = $('#foro_delete_id').val();
    	$.ajax({
	        type: "POST",
	        url: $('#url_delete').val(),
	        async: true,
	        data: { foro_id: foro_id },
	        dataType: "json",
	        success: function(data) {
	            $('#toDel-'+foro_id).remove();
	            $('.btn-modalDelete').show();
	            $( ".cancelarCs" ).trigger( "click" );
	        },
	        error: function(){
	            console.log('Error eliminando el registro de respuesta de espacio colaborativo'); // Hay que implementar los mensajes de error para el frontend
	            $('.btn-modalDelete').show();
	        }
	    });
    });

    $('.more_sons').click(function(){
		var li = $('#more_sons');
		var offset = $('#offset').val();
		li.hide();
		$.ajax({
			type: "GET",
			url: $('#url_more').val(),
			async: false,
			data: { foro_id: foro_main_id, offset: offset },
			dataType: "json",
			success: function(data) {
				$('#div_addResponse').append(data.html);
				if (data.more)
				{
					li.show();
					$('#offset').val(data.offset);
				}
				observeResponse();
    			observeLike();
				//clearTimeout( timerId );
			},
			error: function(){
				console.log('Error obteniendo más comentarios'); // Hay que implementar los mensajes de error para el frontend
			}
		});
	});

    observeResponse();
    observeLike();

});

function observeResponse()
{
	$('.reResponse').unbind('click');
	$('.reResponse').click(function(){
    	var foro_id = $(this).attr('data');
    	$('#foro_id').val(foro_id);
    });
}

function observeLike()
{
	$('.like').unbind('click');
	$('.like').click(function(){
		var foro_id = $(this).attr('data');
		$('#like'+foro_id).removeClass('ic-lke-act');
		$.ajax({
			type: "POST",
			url: $('#url_like').val(),
			async: true,
			data: { social_id: 2, entidad_id: foro_id, usuario_id: $('#usuario_id').val() },
			dataType: "json",
			success: function(data) {
				if (data.ilike)
				{
					$('#like'+foro_id).addClass('ic-lke-act');
				}
				$('#cantidad_like-'+foro_id).html(data.cantidad);
				//clearTimeout( timerId );
			},
			error: function(){
				console.log('Error en like'); // Hay que implementar los mensajes de error para el frontend
			}
		});
	});
}

function saveForo(foro_id, foro_main_id)
{
	$('.mensaje-error, .boton').hide();
    $('#wait').show(1000);
    $('html, body').animate({
	    scrollTop: ($('#wait').offset().top-100)
	},1000);
    $.ajax({
        type: "POST",
        url: $('#url_save').val(),
        async: true,
        data: { foro_id: foro_id, mensaje: $('#mensaje_content').val(), foro_main_id: foro_main_id },
        dataType: "json",
        success: function(data) {
        	if (foro_id == foro_main_id)
        	{
        		$('#div_addResponse').append(data.html);
        		$('html, body').animate({
				    scrollTop: ($('#li_responder').offset().top-500)
				},1000);
        	}
        	else {
        		$('#div_addReResponse'+foro_id).append(data.html);
        		$('html, body').animate({
				    scrollTop: ($('#div_addReResponse'+foro_id).offset().top-150)
				},1000);
        	}
        	observeLike();
        	observeResponse();
        	$('#mensaje_content').val('');
        	$('.boton').show();
            $('#wait').hide(1000);
        },
        error: function(){
            console.log('Error guardando la respuesta al espacio colaborativo'); // Hay que implementar los mensajes de error para el frontend
            $('.boton').show();
            $('#wait').hide(1000);
        }
    });
}
