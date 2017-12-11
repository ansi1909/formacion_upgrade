$(document).ready(function() {

	$('.tree').jstree();

	$('.tree').on("select_node.jstree", function (e, data) {
		var id = data.node.id;
		var pagina_id = $('#'+id).attr('p_id');
		var pagina_str = $('#'+id).attr('p_str');
		$('#pagina_str').val(pagina_str);
		$('#pagina_id').val(pagina_id);
	});

});
