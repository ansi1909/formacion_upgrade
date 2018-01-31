$(document).ready(function() {

	var root_site = $('#root_site').val();

	validarEntidad($('#tipo_certificado_id').val());

	$('#tipo_certificado_id').click(function()
	{
		validarEntidad($(this).val());
	});

    $('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

 });

function validarEntidad(valor)
{
	if(valor>1)
	{
		$('.entidad').show();
	}else
	{
		$('.entidad').hide();
		$('#entidad').attr("value","0");
	}
}

function responsive_filemanager_callback(field_id)
{
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);
	
	if(field_id=="imagen")
		$('#figure').html('<img src="'+url+'" width="100%">');
}