$(document).ready(function() {

	$('.tree').jstree();

	$('.pepa').click(function(){
		var pagina_id = $(this).attr('data');
		console.log('pagina_id: '+pagina_id);
	});

});
