$(document).ready(function() {

	$('#tbody-empresas tr').each(function(){
		var tr = $(this).attr('id');
		var tr_arr = tr.split('tr-');
		var empresa_id = tr_arr[1];
		treeNiveles(empresa_id);
	});

	$('#div-active-alert').hide();

	$('.new').click(function(){
		$('#empresa_id').val($(this).attr('data'));
		$('#header-empresa').html($(this).attr('empresa'));
		$('#nombre').val("");
		$('#div-alert').hide();
	});


	$('#guardar').click(function(){
		saveNivel();
	});


	$('#form').submit(function(e) {
		e.preventDefault();
	  	saveNivel();
	});

	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

});

function saveNivel()
{

	$('#div-alert').hide();
	if ($("#form").valid())
	{
		$('#guardar').prop('disabled', true);
		$.ajax({
			type: "POST",
			url: $('#form').attr('action'),
			async: true,
			data: $("#form").serialize(),
			dataType: "json",
			success: function(data) {
				$('#guardar').prop('disabled', false);
				$( "#cancelar" ).trigger( "click" );
				$('#td-'+data.empresa_id).jstree(true).settings.core.data.url = $('#url_tree').val()+'/'+data.empresa_id;
  				$('#td-'+data.empresa_id).jstree(true).refresh();
			},
			error: function(){
				$('#alert-error').html($('#error_msg-save').val());
				$('#div-alert').show();
			}
		});
	}

}

function treeNiveles(empresa_id)
{
	$('#td-'+empresa_id).jstree({
		'core' : {
			'data' : {
				"url" : $('#url_tree').val()+'/'+empresa_id,
				"dataType" : "json"
			}
		}
	});
}
