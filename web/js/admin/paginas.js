$(document).ready(function() {

	$('.tree').jstree();

	$('.delete').click(function(){
		var pagina_id = $(this).attr('data');
		sweetAlertDelete(pagina_id, 'CertiPagina');
	});

	$('.pepa').click(function(){
		var pagina_id = $(this).attr('data');
		console.log('pagina_id: '+pagina_id);
	});

});
