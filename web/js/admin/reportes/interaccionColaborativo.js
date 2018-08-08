$(document).ready(function() {

    $('#search').click(function(){
    	$('#label_filtro').hide();
    	$('.descargable').hide();
    	$('.generable').show();
    	$('#resultado').hide();
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
