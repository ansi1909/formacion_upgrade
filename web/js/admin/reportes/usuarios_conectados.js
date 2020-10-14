jQuery(document).ready(function($) {
		getUsuariosConectados();
		getUsuariosConectadosRefresh();
});

function getUsuariosConectados()
{
	$("#labelConectados").hide();
	$('#labelConectadosLoading').show();
	$.ajax({
		type: "POST",
		url: $('#form').attr('action'),
		async: true,
		data: $("#form").serialize(),
		dataType: "json",
		success: function(data) {
			$('#tablaConectados').html(data.html);
			$('#totalConectados').html(data.conectados);
			$('#labelConectadosLoading').hide();
			$("#labelConectados").show();
			$('.data_table').DataTable().destroy();
			applyDataTable();
			if(data.conectados)
			{
				$('#footer_conectados').show();
			}
		},
		error: function(){
			$("#tablaConectados").empty();
			$('#div-error-users').html($('#error-msg').val());
			notify($('#div-error-users').html());

		}
	});
}


function getUsuariosConectadosRefresh()
{
    timer = setInterval(function(){
        getUsuariosConectados();
    }, 30000);

}