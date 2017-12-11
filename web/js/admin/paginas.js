$(document).ready(function() {

	$('.tree').jstree();

	$('.delete').click(function(){
		var pagina_id = $(this).attr('data');
		sweetAlertDelete(pagina_id, 'CertiPagina');
	});

});
