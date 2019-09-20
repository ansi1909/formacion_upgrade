$(document).ready(function() {

	$('#empresa_id').change(function(){
		var desde = $('#desde').val() ? $('#desde').val().replace(/\//g, '-') : 0;
		var hasta = $('#hasta').val() ? $('#hasta').val().replace(/\//g, '-') : 0;
		window.location.replace($('#url_auto').val()+'/'+$('#empresa_id').val()+'/'+desde+'/'+hasta);
		$('#paginas').hide();
		$('#pagina-loader').show();
	});

    $('.datePicker').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es'
	});

	$('.tree').jstree();

	$('.tree').on("select_node.jstree", function (e, data) {
		var id = data.node.id;
		var pagina_id = $('#'+id).attr('p_id');
		var pagina_str = $('#'+id).attr('p_str');
		var tipo_recurso_id = $('#'+id).attr('tipo_recurso_id');
		if(tipo_recurso_id == 4)
		{
			$('#pagina_str').val(pagina_str);
			$('#pagina_id').val(pagina_id);
		}
		
	});

	$('#form').submit(function(e) {
		e.preventDefault();
	});

	$('#search').click(function(){
		var valid = $("#form").valid();
        if (!valid) 
        {
            notify($('#div-error').html());
        }
        else {
        	$('#form').submit();
			return false;
        }
	});

	$('#form').safeform({
		submit: function(e) {
			$('#reporte').show();
        	$('.load1').show();
        	$('#search').hide();
			$.ajax({
				type: "POST",
				url: $('#form').attr('action'),
				async: true,
				data: $("#form").serialize()+'&excel=0',
				dataType: "json",
				success: function(data) {
					$('.load1').hide();
		        	$('#search').show();
					mostrarReporte(data);
					$('#form').safeform('complete');
					return false;
				},
				error: function(){
					$('#div-error-server').html($('#error-msg').val());
					notify($('#div-error-server').html());
					$('#reporte').hide();
		        	$('.load1').hide();
		        	$('#search').show();
					$('#form').safeform('complete');
                    return false;
				}
			});
		}
	});

});

function mostrarReporte(data)
{
	$('#label_desde').html($('#desde').val());
	$('#label_hasta').html($('#hasta').val());
	$('#label_filtro').show();
	$('#archivo').val(data.archivo);
	$('#document_name').html(data.document_name);
	$('#document_size').html(data.document_size);
	$('#resultado').show();
	observeArchivo();
	
	console.log(data);

}

function observeArchivo()
{
	$('#resultado').click(function(){
    	window.open($('#archivo').val(), '_blank');
    });
}
