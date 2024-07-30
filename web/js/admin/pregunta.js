$(document).ready(function() {
    var estatus_activo = 2; 
	var root_site = $('#root_site').val();
	$('#form_estatusContenido option[value='+ estatus_activo +']').attr("selected",true);

    $('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

});

function responsive_filemanager_callback(field_id){
	
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);
	
	$('#figure').html('<img src="'+url+'" style="width: 150px; height: auto;">');
	
}