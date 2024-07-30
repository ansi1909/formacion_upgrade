jQuery(document).ready(function($) {
	$('.modalButton').click(function(event) {
		event.preventDefault();
		var pagina_id = $(this).data('pagina');
		$('#label_modal').text('');
	    $('#pdescripcion').html('');
		$('#radar-img-'+pagina_id).show();
		   $.ajax({
	        type: "POST",
	        url: $('#descripcion_pagina').val(),
	        async: true,
	        data: { pagina_id: pagina_id },
	        dataType: "json",
	        success: function(data) {
	        	$('#label_modal').text(data.nombre);
	        	$('#pdescripcion').html(data.descripcion);
	        	$('#radar-img-'+pagina_id).hide();
	            $('#modalInfo').modal('show');
	        },
	        error: function(){
	            console.log('Error al cargar la descripcion'); 
	        }
	    });
		
        


	});
});