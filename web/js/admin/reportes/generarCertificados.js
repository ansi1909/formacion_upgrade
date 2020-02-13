$(document).ready(function() {

    $('#search').click(function(){
    	$('#label_filtro').hide();
    	$('.descargable').hide();
    	$('.generable').show();
    	$('#resultado').hide();
    });

});
var pagina_id = 0;
var empresa_select = $('#empresa_select').val();
var pagina_selected = $('#pagina_select').val();
console.log(empresa_select);
if(empresa_select)
{
    getProgramas(empresa_select,pagina_selected);
}

$('#empresa_id').change(function(){
    $('#div-active-alert').hide();
    var empresa_id = $(this).val();
    getProgramas(empresa_id,pagina_id);
});

$('#programa_id').change(function(){
    $('.card-footer').show();
    
});
$('#resultado').click(function(){
    console.log($('#url_descarga').val());
    window.open($('#url_descarga').val(), '_blank');
});
function observeArchivo()
{
	$('#resultado').click(function(){
        console.log($('#ruta').val());
    	window.open($('#ruta').val(), '_blank');
    });
}

function getProgramas(empresa_id,pagina_selected){
	$('#programa_id').hide();
	$('#pagina-loader').show();
	$.ajax({
		type: "GET",
		url: $('#url_programas').val(),
		async: true,
		data: { empresa_id: empresa_id,pagina_selected: pagina_selected },
		dataType: "json",
		success: function(data) {
			$('#programa_id').html(data.options);
			$('#programa_id').show();
			$('#pagina-loader').hide();
			if(empresa_id == 0)
			{
				$('#usuarios').hide();
				$('#excel').hide();
			}
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}
