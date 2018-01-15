$(document).ready(function() {

	$('.tree').jstree();

	$('.delete').click(function(){
		var pregunta_id = $(this).attr('data');
		sweetAlertDelete(pregunta_id, 'CertiPregunta');
	});

});
