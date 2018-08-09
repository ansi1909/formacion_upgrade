jQuery(document).ready(function($) {
	
	
    $('#search').click(function(){
    	$('#label_filtro').hide();
    	$('.descargable').hide();
    	$('.generable').show();
    	$('#resultado').hide();
    });
	
});

function mostrarReporte(data)
{
		console.log(data);
		$('#label_desde').html($('#desde').val());
		$('#label_hasta').html($('#hasta').val());
		$('#label_filtro').show();
		$('#resultado').html(data.html);
		$('#resultado').show();
		applyDataTable();
		observe();
}

function observe()
{
	$('#excel').click(function(){
    	$('#excel').hide();
    	$('#excel-loader').show();
    	$.ajax({/*------------------------------ peticion para gegerar excel -------------*/
			type: "POST",
			url: $('#form').attr('action'),
			async: true,
			data: $("#form").serialize()+'&excel=1',
			dataType: "json",
			success: function(data) {
				$('#excel-loader').hide();
	        	$("#excel-link").attr("href", data.archivo);
	        	$('#excel-link').show();
			},
			error: function(){
				$('#div-error-server').html($('#error-msg').val());
				notify($('#div-error-server').html());
				$('.descargable').hide();
    			$('.generable').show();
			}
		});
    });
}

