$(document).ready(function() {

	var subpagina_id = $('#subpagina_id').val();

	CKEDITOR.replace( 'mensaje',
	{
		toolbar : 'MyToolbar'
	});

	$('#fechaPublicacion').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    startDate: '0d',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var startDate = new Date(selected.date.valueOf());
    	$('#fechaVencimiento').datepicker('setStartDate', startDate);
    });

	$('#fechaVencimiento').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    startDate: '0d',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var endDate = new Date(selected.date.valueOf());
    	$('#fechaPublicacion').datepicker('setEndDate', endDate);
	});

	$('#cancelar').click(function(){
		$('#section-form').hide(1000);
        $('#section-list').show(1000);
        $('html, body').animate({
		    scrollTop: 0
		},2000);
	});

	$('.newTopic').each(function(){
		observeTopic($(this));
	});

	$(".table-card").paginate({
        perPage: 10,
        autoScroll: false,
        paginatePosition: ['bottom'],
        useHashLocation: true,
        onPageClick: function() {
        	$('html, body').animate({
			    scrollTop: 0
			},2000);
        }
    });

    $( "#search" ).autocomplete({
    	source: $('#url_search').val(),
      	minLength: 3,
      	select: function( event, ui ) {
        	console.log( "Selected: " + ui.item.value + " AKAA " + ui.item.id );
        	window.location.replace($('#url_detalle').val()+'/'+ui.item.id+'/'+subpagina_id);
      	}
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
	
	console.log($('#fileupload').val());

    $('#fileupload').fileupload({
        url: $('#url_upload').val(),
        dataType: 'json',
        done: function (e, data) {
        	$.each(data.result.response.files, function (index, file) {
        		$('#archivo_input').val(file.name);
				$('#archivo').val($('#base_upload').val()+file.name);
            });
		},
		submit: function (e, data) {
        	$.each(data.files, function (index, file) {
				var hola = $('#archivo_input').val(file.name);
				$('#archivo').val($('#base_upload').val()+file.name);
            });
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            );
        }
    })
    .prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

    $('#saveFile').click(function(){
        var valid = $("#form-upload").valid();
        if (valid) 
        {
        	$('.boton').hide();
            $('#wait_file').show(1000);
		    $.ajax({
		        type: "POST",
		        url: $('#url_archivo').val(),
		        async: true,
		        data: { foro_id: $('#upload_foro_id').val(), descripcion: $('#descripcion').val(), archivo: $('#archivo').val(), edit: 1 },
		        dataType: "json",
		        success: function(data) {
		        	$('#foro_id').val(data.foro_id);
		        	$('#descripcion').val('');
		        	$('#archivo').val('');
		        	$('#archivo_input').val('');
		        	$('.list-downloads').append(data.html);
		        	$('.attachments').show();
		        	observeDeleteFile();
		        	$('.boton').show();
		            $('#wait_file').hide(1000);
		            $( "#cancelarUpload" ).trigger( "click" );
		        },
		        error: function(){
		            console.log('Error guardando el archivo en el espacio colaborativo'); // Hay que implementar los mensajes de error para el frontend
		            $('.boton').show();
		            $('#wait_file').hide(1000);
		        }
		    });
        }
    });

});

function observeTopic(newTopic)
{
	newTopic.click(function(){
		var foro_id = $(this).attr('data');
		var empresa_id = $('#empresa_id').val();
		$('#foro_id').val(foro_id);
		$('#upload_foro_id').val(foro_id);
		$('#mensaje_content').val('');
		$('#section-list').hide(1000);
		$('#wait').show(1000);
		if (foro_id != '0')
		{
			$('#titulo').html($('#titulo_edit').val());
			$('#base_upload').val('recursos/espacio/'+empresa_id+'/'+foro_id+'/');
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
			    	if (data.html != '')
			    	{
			    		$('.list-downloads').html(data.html);
			    		$('.attachments').show();
			    		observeDeleteFile();
			    	}
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
			$('#base_upload').val('');
			$('.list-downloads').html('');
			$('.attachments').hide();
			$('#section-form').show(1000);
			$('#wait').hide(1000);
		}
	});

}

function saveForo()
{
	$('label.mensaje-error').hide();
	$('#subir').hide();
    $('#publicar').hide();
    $('#cancelar').hide();
    $('#wait').show(1000);
    $.ajax({
        type: "POST",
        url: $('#form').attr('action'),
        async: true,
        data: $("#form").serialize(),
        dataType: "json",
        success: function(data) {
            location.reload();
        },
        error: function(){
            console.log('Error guardando el registro de espacio colaborativo'); // Hay que implementar los mensajes de error para el frontend
            $('#publicar').show();
            $('#cancelar').show();
            $('#subir').show();
            $('#wait').hide(1000);
        }
    });
}

function observeDeleteFile()
{
	$('.delete').unbind('click');
	$('.delete').click(function(){
		var archivo_id = $(this).attr('data');
		$.ajax({
	        type: "POST",
	        url: $('#url_delete_file').val(),
	        async: true,
	        data: { archivo_id: archivo_id },
	        dataType: "json",
	        success: function(data) {
	        	$('#archivo-'+archivo_id).remove();
	        	if (data.archivos == 0)
	        	{
	        		$('.list-downloads').html('');
					$('.attachments').hide();
	        	}
	        },
	        error: function(){
	            console.log('Error eliminando el archivo'); // Hay que implementar los mensajes de error para el frontend
	        }
	    });
	});
}
