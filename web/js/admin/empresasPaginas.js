$(document).ready(function() {

	$('.see').click(function(){
		var empresa_id = $(this).attr('data');
		$('#div-active-alert').hide();
		$.ajax({
			type: "GET",
			url: $('#url_pages').val(),
			async: true,
			data: { empresa_id: empresa_id },
			dataType: "json",
			success: function(data) {
				$('#pages').html(data.html);
				$('#empresaTitle').html(data.empresa);
				$('#div-pages').show();
				observe();
			},
			error: function(){
				$('#active-error').html($('#error_msg-pages').val());
				$('#div-active-alert').show();
				$('#div-pages').hide();
			}
		});
	});

});

function observe()
{

	$('#tbody-pages tr').each(function(){
		var tr = $(this).attr('id');
		if (!(typeof tr === 'undefined' || tr === null)){
			var tr_arr = tr.split('tr-');
			var pagina_id = tr_arr[1];
			treePaginas(pagina_empresa_id);
		}
	});

	$('.cb_activo').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f_active');
		var pagina_empresa_id = id_arr[1];
		$.ajax({
			type: "POST",
			url: $('#url_active').val(),
			async: true,
			data: { id: pagina_empresa_id, entity: 'CertiPaginaEmpresa', checked: checked },
			dataType: "json",
			success: function(data) {
				console.log('Activación/Desactivación realizada. Id '+data.id);
			},
			error: function(){
				$('#active-error').html($('#error_msg-active').val());
				$('#div-active-alert').show();
			}
		});
	});

	$('.cb_acceso').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f_access');
		var pagina_empresa_id = id_arr[1];
		$.ajax({
			type: "POST",
			url: $('#url_acceso').val(),
			async: true,
			data: { id: pagina_empresa_id, entity: 'CertiPaginaEmpresa', checked: checked },
			dataType: "json",
			success: function(data) {
				console.log('Acceso concedido/No concedido realizado. Id '+data.id);
			},
			error: function(){
				$('#active-error').html($('#error_msg-acceso').val());
				$('#div-active-alert').show();
			}
		});
	});

}


function treePaginas(pagina_empresa_id)
{
    $('#td-'+pagina_empresa_id).jstree({
        'core' : {
            'data' : {
                "url" : $('#url_tree').val()+'/'+pagina_empresa_id,
                "dataType" : "json"
            }
        }
    });
}
