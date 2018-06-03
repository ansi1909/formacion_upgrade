$(document).ready(function() {

	$( document ).tooltip({
    	track: true
    });

    $('#modificar').click(function(){
    	var valid = $("#form").valid();
    	if (valid)
    	{
    		$('.boton').hide();
            $('#wait_profile').show(1000);
		    $.ajax({
		        type: "POST",
		        url: $('#form').attr('action'),
		        async: true,
		        data: $("#form").serialize(),
		        dataType: "json",
		        success: function(data) {
		        	$('#label-correo').html(data.correo);
		        	$('#label-correo_corporativo').html(data.correo_corporativo);
		        	$('#label-fn').html(data.fechaNacimiento);
		        	$('.boton').show();
		            $('#wait_profile').hide(1000);
		            $( ".close" ).trigger( "click" );
		        },
		        error: function(){
		            console.log('Error guardando los datos del perfil del usuario'); // Hay que implementar los mensajes de error para el frontend
		            $('.boton').show();
		            $('#wait_profile').hide(1000);
		        }
		    });
    	}
    });

    $('#cambio').click(function(){
    	var valid = $("#form-clave").valid();
    	if (valid)
    	{
    		$('.boton').hide();
            $('#wait_password').show(1000);
		    $.ajax({
		        type: "POST",
		        url: $('#form-clave').attr('action'),
		        async: true,
		        data: $("#form-clave").serialize(),
		        dataType: "json",
		        success: function(data) {
		        	$('#clave').val('');
		        	$('#confirmar').val('');
		        	$('.boton').show();
		            $('#wait_password').hide(1000);
		            $( ".close" ).trigger( "click" );
		            console.log('cambio de contraseña realizado'); // Hay que implementar los mensajes de error para el frontend
		        },
		        error: function(){
		            console.log('Error cambiando la contraseña'); // Hay que implementar los mensajes de error para el frontend
		            $('.boton').show();
		            $('#wait_password').hide(1000);
		        }
		    });
    	}
    });

   	$('.fileinput-button').click(function(){
    	$('.error').html('');
    });

   	$('#fileupload').fileupload({
        url: $('#url_upload').val(),
        dataType: 'json',
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        add: function (e, data) {
	        var goUpload = true;
	        var uploadFile = data.files[0];
	        if (!(/\.(gif|jpg|jpeg|tiff|png)$/i).test(uploadFile.name)) {
	            $('#error').html('Sólo archivo de imagen');
	            goUpload = false;
	        }
	        if (goUpload == true) {
	            data.submit();
	        }
	    },
        done: function (e, data) {
        	$.each(data.result.response.files, function (index, file) {

        		// Actualizar img
        		var uploads = $('#uploads').val();
        		var base_upload = $('#base_upload').val();
        		var img = $('#perfil');
        		img.attr("src", uploads+base_upload+file.name);

        		// Actualización de la foto en BD
        		var foto = base_upload+file.name;
        		$.ajax({
					type: "POST",
					url: $('#url_img').val(),
					async: true,
					data: { foto: foto },
					dataType: "json",
					success: function(dataAjax) {
						console.log('Archivo: '+file.name+' actualizado en el usuario '+dataAjax.id);
					},
					error: function(){
						console.log('Error actualizando la foto'); // Hay que implementar los mensajes de error para el frontend
					}
				});

            });
        }
    });

});
