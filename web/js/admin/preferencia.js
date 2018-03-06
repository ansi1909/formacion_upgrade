$(document).ready(function() {
	
	$('.layout').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f');
		var layout_id = id_arr[1];
		if (checked == 1)
		{
			$('.layout').each(function(){
				if ($(this).attr('id') != id)
				{
					$(this).prop('checked', false);
				}
			});
		}
	});

	$('.ch').each(function(){
		var id = $(this).attr('id');
		var id_arr = id.split('colorpickerHolder');
		var atributo_id = id_arr[1];
		$('#colorpickerHolder'+atributo_id).ColorPicker({
	        flat: true,
	        color: $('#atributos_id'+atributo_id).val(),
	        onSubmit: function(hsb, hex, rgb) {
	            $('#colorSelector'+atributo_id+' div').css('backgroundColor', '#' + hex);
	            $('#atributos_id'+atributo_id).val('#' + hex)
	    	}
		});
		$('#colorpickerHolder'+atributo_id+'>div').css('position', 'absolute');
	});

	var widt = false;
	$('.cs').bind('click', function() {
		var id = $(this).attr('id');
		var id_arr = id.split('colorSelector');
		var atributo_id = id_arr[1];
		$('#colorpickerHolder'+atributo_id).stop().animate({height: widt ? 0 : 173}, 500);
	    widt = !widt;
	});

	$('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

    $(".btn_clearImg").on("click",function(event) {
    	var id = $(this).attr('id');
		var id_arr = id.split('btn_clear_');
		var field_id = id_arr[1];
        $('#'+field_id).val("");
        $("#figure_"+field_id).html('<img src="'+$('#default_'+field_id).val()+'" width="100%" height="100%">');
    });

    $('#finish').click(function(){
       $('#form').submit();
    });

});

function responsive_filemanager_callback(field_id){
	
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);
	if (field_id == 'logo'){
		var w = '356px';
		var h = '87px';
	}
	else {
		var w = '32px';
		var h = '32px';
	}
	$('#figure_'+field_id).html('<img src="'+url+'" width="'+w+'" height="'+h+'">');
	
}