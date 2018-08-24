$(document).ready(function() {

	afterPaginate();

	$('.paginate_button').click(function(){
		afterPaginate();
	});

});

function observe()
{

	$('#tbody-programados tr').each(function(){
		var tr = $(this).attr('id');
		if (!(typeof tr === 'undefined' || tr === null)){
			var tr_arr = tr.split('tr-');
			var notificacion_programada_id = tr_arr[1];
			treeGrupoProgramado(notificacion_programada_id);
		}
	});

}


function treeGrupoProgramado(notificacion_programada_id)
{
    $('#td-'+notificacion_programada_id).jstree({
        'core' : {
            'data' : {
                "url" : $('#url_tree').val()+'/'+notificacion_programada_id,
                "dataType" : "json"
            }
        }
    });
}

function afterPaginate()
{
	$('.see').unbind('click');
	$('.see').click(function(){
		var notificacion_id = $(this).attr('data');
		$('#div-programados, .load1').show();
		$('#div-programados-alert').hide();
		$('#programados').hide();
		$.ajax({
			type: "GET",
			url: $('#url_programados').val(),
			async: true,
			data: { notificacion_id: notificacion_id },
			dataType: "json",
			success: function(data) {
				$('.load1').hide();
				$('#programados').html(data.html);
				$('#notificacionTitle').html(data.notificacion);
				$('#programados').show();
				observe();
			},
			error: function(){
				$('.load1').hide();
				$('#programados').hide();
				$('#div-programados-alert').show();
			}
		});
	});
}
