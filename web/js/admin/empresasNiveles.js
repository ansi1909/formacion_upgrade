$(document).ready(function() {

	afterPaginate();

	$('.paginate_button').click(function(){
		afterPaginate();
		console.log('quia');
	});

	$('#guardar').click(function(){
		$('#form').submit();
		return false;
	});

	$('#form').submit(function(e) {
		e.preventDefault();
	});

	$('#form').safeform({
		submit: function(e) {
			
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
						$('.form-control').val('');
						$('#guardar').prop('disabled', false);
						$( "#cancelar" ).trigger( "click" );
						$('#td-'+data.empresa_id).jstree(true).settings.core.data.url = $('#url_tree').val()+'/'+data.empresa_id;
		  				$('#td-'+data.empresa_id).jstree(true).refresh();
						
						// manual complete, reenable form ASAP
						$('#form').safeform('complete');
						return false; // revent real submit
								
					},
					error: function(){
						$('#alert-error').html($('#error_msg-save').val());
						$('#div-alert').show();
						$('#guardar').prop('disabled', false);
						$('#form').safeform('complete');
                        return false; // revent real submit
					}
				});
			}
			else {
				$('#form').safeform('complete');
                return false; // revent real submit
			}
			
		}
	});
    
});

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

	$('.paginate_button').click(function(){
		afterPaginate();
	});
}

function afterPaginate()
{
	$('#tbody-empresas tr').each(function(){
		var tr = $(this).attr('id');
		console.log(tr);
		if (!(typeof tr === 'undefined' || tr === null)){
			var tr_arr = tr.split('tr-');
			var empresa_id = tr_arr[1];
			treeNiveles(empresa_id);
			
		}
	});

	$('.new').unbind('click');
	$('.new').click(function(){
		var empresa_id = $(this).attr('data');
		$('#empresa_id').val(empresa_id);
		$('#header-empresa').html($(this).attr('empresa'));
		$('#nombre').val("");
		enableSubmit();
	});
}
