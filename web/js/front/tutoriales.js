$(document).ready(function() 
{

    /////presionar bptpn para enviar mensaje al equipo de soporte

    $('.descargaTutorial').click(function()
    {
      	var id= $(this).attr("id");
      	alert(id);
      	$.ajax({
	        
				        type: "POST",
				        url: $('#url_dowloadpdf').val(),//"/formacion2.0/web/app_dev.php/soporte/_ajaxEnviarMailSoporte",
				        data: { id: id},
				        dataType: "json",
				        success: function(data) 
				        {
				           alert('Hola');
				           
				           
				        },
				        error: function(){
				               console.log('Error al descargar el archivo');
				        }
	               });
    });






});