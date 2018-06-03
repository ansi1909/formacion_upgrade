$(document).ready(function() {

	$('.tree').jstree();

	$('.delete').click(function(){
		var prueba_id = $(this).attr('data');
		sweetAlertDelete(prueba_id, 'CertiPrueba');
	});

});
