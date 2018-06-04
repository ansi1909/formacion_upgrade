$(document).ready(function() 
{
	$('#correo_soporte_btn_enviar').click(function()//detectar si se oprime el boton enviar
    { 
        var datosSession=1;
		var errores=
		{
		    'mensaje':0,
		    'asunto':0,
		    'correo':0
	    };

		var campos=
		{
			'correo':{'valor':$('#correo_soporte').val(),'id':'correo_soporte','error_id':'error_correo','error_visible':false},
			'asunto':{'valor':$('#asuntoV').val(),'id':'asuntoV','error_id':'error_asunto','error_visible':false},
			'mensaje':{'valor':$('#msjSv').val(),'id':'msjSv','error_id':'error_mensaje','error_visible':false}
		};
		          
		//Verificar que los campos esten llenos
		errores.mensaje=campoVacio(campos.mensaje);
		errores.asunto=campoVacio(campos.asunto);
		if (campos.correo.valor!=undefined) //si el campo correo se muestra al usuario
		{
		  errores.correo=campoVacio(campos.correo,true);
		  datosSession=0;
		}
		         
		if ((errores.mensaje+errores.asunto+errores.correo)==0) //si no existen errores se procede a enviar el correo
		{
		  $('#btn_enviar_').hide();
   		  $('#wait_soporte').show(1000);
		  enviarCorreo(campos,datosSession);
		}
		
		          

	});


   

		      /////Limpiar mensajes de error, al escribir en uno de los input del formulario de soporte
	$('#correo_soporte').keydown(function()
	{
		$('#error_correo').hide();
	});

	$('#asuntoV').keydown(function()
	{
		$('#error_asunto').hide();
	});

	$('#msjSv').keydown(function()
	{
		$('#error_mensaje').hide();
	});


		///al abrir el modal de correo
	$('.abrir_modal_soporte').click(function()
	{		
		//inicializar como ocultos los mensajes de campos requerido y aseguramos que el formulario se encuentre vacio
    	$('#error_correo').hide();
    	$('#error_asunto').hide();
   		$('#error_mensaje').hide();
   		$('#success_mensaje').hide();
   		document.getElementById('formularioSoporte').reset();    	
	});

});

////funciones creadas por el desarrollador

function campoVacio(campo,correo=false)//verifica si un campo se encuentra vacio , en el caso del correo electronico certifica que el formato de correo ingresado sea correcto
{
	var emailValido=/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/;
	var error=0;

	if (correo) 
	{
		console.log('Entro a la existencia de correo ');
		if (campo.valor.length!=0) //si el correo no esta vacio debemos verificar que sea un email valido
		{
			if(!emailValido.test(campo.valor))//si no es valido se muestra el mensaje de error
			{
				$('#'+campo.error_id).show();
				$('#'+campo.id).focus();
				error=1;
			}
		}
	}
		
	if (campo.valor.length==0) //si el campo esta vacio se muestra el mensaje de error
	{
		$('#'+campo.error_id).show();
		error=1;
	}
	

	return error;
}

function enviarCorreo(campos,sesion)//llama al controlador con los datos del mensaje que se desea enviar
{
	$.ajax({
	        
			type: "POST",
			url: $('#url_sendmail').val(),//"/formacion2.0/web/app_dev.php/soporte/_ajaxEnviarMailSoporte",
			data: { correo: campos.correo.valor, asunto:campos.asunto.valor, mensaje:campos.mensaje.valor,sesion:sesion},
			dataType: "json",
			success: function(data) 
			{
				           
				$('#modalSv').modal('hide');
				document.getElementById('formularioSoporte').reset();
	            $('#btn_enviar_').show();
   				$('#wait_soporte').hide();

				if (data.respuesta==1) //si el mensaje se envio al equipo de soporte 
				{
	                             //se muestra el modal de exito
	                $('#modalSuccess').modal('show');//abre el modal de confirmacion
	                            
				}
				else
				{
				    $('#modalWrong').modal('show');	 
				}
				           
				},
			error: function()
			{
				$('#modalSv').modal('hide');
				document.getElementById('formularioSoporte').reset();
		        $('#btn_enviar_').show();
	   			$('#wait_soporte').hide();
	   			$('#modalWrong').modal('show');
			}
	    });

	return 0;
}
