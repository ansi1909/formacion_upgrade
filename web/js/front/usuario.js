$(document).ready(function() {
    $('#modificar').click(function(){
    	var valid = $("#form").valid();
    	if (valid)
    	{
    		$('.boton').hide();
            $('#wait_profile').show(1000);
            $('#correo_usado').hide();
		    $.ajax({
		        type: "POST",
		        url: $('#form').attr('action'),
		        async: true,
		        data: $("#form").serialize(),
		        dataType: "json",
		        success: function(data) {
		        	console.log(data);
		        	if (data.html =='') 
		        	{
			        	$('#label-correo').html(data.correo);
			        	$('#label-correo_corporativo').html(data.correo_corporativo);
			        	$('#label-fn').html(data.fechaNacimiento);
			        	$('.boton').show();
			            $('#wait_profile').hide(1000);
			            $('#correo_exito').show();
			            setTimeout(function(){ $('#correo_exito').hide(); }, 3000);
			            //$( ".close" ).trigger( "click" );

			        }else
			        {
			        	$('#correo_usado').html(data.html);
			        	$('#correo_usado').show();
			        	$('#correo_secundario').focus();
			        	$('.boton').show();
			            $('#wait_profile').hide(1000);
			        }
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
					if(data.ok == 1)
					{
						$('#password').val('');
						$('#confirmar').val('');
						$('.boton').show();
						$('#wait_password').hide(1000);
						//$( ".close" ).trigger( "click" );
						console.log('Cambio de contraseña realizado'); // Hay que implementar los mensajes de error para el frontend
						$('#error_clave').removeClass('error_clave');
						$('#error_clave').addClass('exito_clave');
						$('#error_clave').html(data.mensaje);
						$('#error_clave').show();
						$("#cambio").addClass( "blocked" );
					}else{
						$('#password').val('');
						$('#confirmar').val('');
						$('.boton').show();
						$('#wait_password').hide(1000);
						//$( ".close" ).trigger( "click" );
						console.log('la contrasena debe ser distinta a la actual'); // Hay que implementar los mensajes de error para el frontend
						$('#error_clave').removeClass('exito_clave');
						$('#error_clave').addClass('error_clave');
						$('#error_clave').html(data.mensaje);
						$('#error_clave').show();
						$("#cambio").addClass( "blocked" );
					}
		        },
		        error: function(){
		            console.log('Error cambiando la contraseña'); // Hay que implementar los mensajes de error para el frontend
		            $('#error_clave').removeClass('exito_clave');
				    $('#error_clave').addClass('error_clave');
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
        	$('#radar-img').show();
	        var goUpload = true;
	        var uploadFile = data.files[0];
	        if (!(/\.(gif|jpg|jpeg|tiff|png)$/i).test(uploadFile.name)) {
	        	$('#radar-img').hide();
	            $('#error').html('Sólo archivo de imagen');
	            goUpload = false;
	        }
	        if (goUpload == true) {
	        	setTimeout(function(){ 
	        		$('#radar-img').hide();
	        	}, 1000);
	            data.submit();
	        }
	    },
        done: function (e, data) {
        	$.each(data.result.response.files, function (index, file) {
            setTimeout(function(){ 
            	$('#radar-img').hide();
             }, 1000);
                
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
