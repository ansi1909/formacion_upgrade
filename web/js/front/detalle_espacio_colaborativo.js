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

    $('.reResponse').click(function(){
    	var foro_id = $(this).attr('data');
    	$('#foro_id').val(foro_id);
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

	$('.deleteTopic').click(function(){
    	var foro_id = $(this).attr('data');
    	var tema = $(this).attr('tema');
    	$('#foro_delete_id').val(foro_id);
    	$('#titleDelete').html(tema);
    });

    $('.cancelarCs').click(function(){
    	$('#foro_delete_id').val(0);
    	$('#titleDelete').html('');
    });

    $('#eliminar').click(function(){
    	$('.btn-modalDelete').hide();
    	$.ajax({
	        type: "POST",
	        url: $('#url_delete').val(),
	        async: true,
	        data: { foro_id: $('#foro_delete_id').val() },
	        dataType: "json",
	        success: function(data) {
	            location.reload();
	        },
	        error: function(){
	            console.log('Error eliminando el registro de espacio colaborativo'); // Hay que implementar los mensajes de error para el frontend
	            $('.btn-modalDelete').show();
	        }
	    });
    });

});

function observeTopic(newTopic)
{
	newTopic.click(function(){
		var foro_id = $(this).attr('data');
		$('#foro_id').val(foro_id);
		$('#mensaje_content').val('');
		$('#section-list').hide(1000);
		$('#wait').show(1000);
		if (foro_id != '0')
		{
			$('#titulo').html($('#titulo_edit').val());
			$.ajax({
		        type: "GET",
		        url: $('#url_edit').val(),
		        async: true,
		        data: { foro_id: foro_id },
		        dataType: "json",
		        success: function(data) {
		            $('#tema').val(data.tema);
		            $('#fechaPublicacion').val(data.fechaPublicacion);
		            $('#fechaVencimiento').val(data.fechaVencimiento);
		            CKEDITOR.instances.mensaje.setData(data.mensaje);
		            var startDate = new Date(data.fechaPublicacionF);
		            startDate.setDate(startDate.getDate() + 1);
		            var endDate = new Date(data.fechaVencimientoF);
		            endDate.setDate(endDate.getDate() + 1);
		            $('#fechaVencimiento').datepicker('setStartDate', startDate);
			    	$('#fechaPublicacion').datepicker('setEndDate', endDate);
			    	$('#section-form').show(1000);
					$('#wait').hide(1000);
					$('html, body').animate({
					    scrollTop: ($('#section-form').offset().top-100)
					},2000);
		            //clearTimeout( timerId );
		        },
		        error: function(){
		            console.log('Error editando el espacio colaborativo'); // Hay que implementar los mensajes de error para el frontend
		            $('#button-comment').show();
		        }
		    });
		}
		else {
			$('#titulo').html($('#titulo_new').val());
			$('.form-control').val('');
			CKEDITOR.instances.mensaje.setData('');
			$('#section-form').show(1000);
			$('#wait').hide(1000);
		}
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
        	}
        	else {
        		$('#div_addReResponse'+foro_id).append(data.html);
        		$('html, body').animate({
				    scrollTop: ($('#div_addReResponse'+foro_id).offset().top-100)
				},1000);
        	}
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
