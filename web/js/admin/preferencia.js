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

    $('.btn_addImg').click(function(){
    	var a_data = $(this).attr('data');
    	$('#file_input').val(a_data);
    	$('.error').html('');
    });

    $('.fileupload').fileupload({
        url: $('#url_upload').val(),
        dataType: 'json',
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        add: function (e, data) {
	        var goUpload = true;
	        var uploadFile = data.files[0];
	        var file_input = $('#file_input').val();
	        if (!(/\.(gif|jpg|jpeg|tiff|png)$/i).test(uploadFile.name)) {
	            $('#error_'+file_input).html('Debes seleccionar sólo archivo de imagen');
	            goUpload = false;
	        }
	        if (goUpload == true) {
	            data.submit();
	        }
	    },
        done: function (e, data) {
        	$.each(data.result.response.files, function (index, file) {
        		var file_input = $('#file_input').val();
        		var uploads = $('#uploads').val();
        		var base_upload = $('#base_upload').val();
        		if (file_input == 'logo')
        		{
        			var tipo_logo_id = $('#tipo_logo_id').val();
        			var img = $('#'+file_input+'_'+tipo_logo_id);
        		}
        		else {
        			var img = $('#icono');
        		}
        		$('#'+file_input).val(base_upload+file.name);
        		img.attr("src", uploads+base_upload+file.name);
            });
        }
    });

    $('#tipo_logo_id').change(function(){
    	tipoLogo();
    });

    tipoLogo();

});

function tipoLogo()
{

	var tipo_logo_id = $('#tipo_logo_id').val();

	// Aparición del vista previa correspondiente
	switch (tipo_logo_id)
	{
		case '1':
			$('.imgLogoHor').show();
			$('.imgLogoVer').hide();
			$('.imgLogoCC').hide();
			break;
		case '2':
			$('.imgLogoHor').hide();
			$('.imgLogoVer').show();
			$('.imgLogoCC').hide();
			break;
		case '3':
			$('.imgLogoHor').hide();
			$('.imgLogoVer').hide();
			$('.imgLogoCC').show();
			break;
	}

	var src = $('#logo'+'_'+tipo_logo_id).attr('src');
	var arr = src.split('uploads/');
	var logo = arr[arr.length-1];
	$('#logo').val(logo);

}