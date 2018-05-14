$(document).ready(function() 
{
    ///inicializar como ocultos los mensajes de campos requerido
    $('#error_correo').hide();
    $('#error_asunto').hide();
    $('#error_mensaje').hide();

    ////funciones creadas por el desarrollador

	   function campoVacio(campo,correo=false)//verifica si un campo se encuentra vacio , en el caso del correo electronico certifica que el formato de correo ingresado sea correcto
	   {
		      var emailValido=/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/;
		      var error=0;

		      if (correo) 
		      {
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
		      	  $('#'+campo.id).focus();
		      	  error=1;
		      }

		      return error;
	   }


    ///Metodos propios de JQuery
		    $('#correo_soporte_btn_enviar').click(function()//detectar si se oprime el boton enviar
		      { 
		          
		          
		          var error=null;
		          var campos={
		          	           'correo':{'valor':$('#correo_soporte').val(),'id':'correo_soporte','error_id':'error_correo','error_visible':false},
		          	           'asunto':{'valor':$('#asuntoV').val(),'id':'asuntoV','error_id':'error_asunto','error_visible':false},
		          	           'mensaje':{'valor':$('#msjSv').val(),'id':'msjSv','error_id':'error_mensaje','error_visible':false}
		          	          };
		          

		          ///Verificar que los campos esten llenos, verifica desde el 

		          
		          error=campoVacio(campos.mensaje);
		          error=campoVacio(campos.asunto);
		          if (campos.correo.valor!=undefined) //si el campo correo se muestra al usuario
		          {
		              error=campoVacio(campos.correo,true);
		          }
		          console.log(error);

		      });


   

		      /////Limpiar mensajes de error al oprimir una tecla
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

});