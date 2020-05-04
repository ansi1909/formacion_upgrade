jQuery(document).ready(function($) {
	$('#modalButton').click(function(event) {
		event.preventDefault();
		$('#modalInfo').modal('show');
		console.log($(this).data('pagina'));

	});
});