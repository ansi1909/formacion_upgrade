jQuery(document).ready(function($) {

	getUsuariosConectados();
	getUsuariosConectadosRefresh();
});


function getUsuariosConectados()
	{
		$.ajax({
				type: "POST",
				url: $('#form').attr('action'),
				async: true,
				data: $("#form").serialize(),
				dataType: "json",
				success: function(data) {
					$('#tablaConectados').html(data.html);
					$('#totalConectados').html(' '+data.conectados);
					applyDataTable();
				},
				error: function(){
					console.log('Por alguna razon no corre');
					// $('#active-error').html($('#error_msg-filter').val());
					// $('#div-active-alert').show();
				}
			});	
}


function getUsuariosConectadosRefresh()
{
    timer = setInterval(function(){
        getUsuariosConectados();
    }, 30000);
   
}