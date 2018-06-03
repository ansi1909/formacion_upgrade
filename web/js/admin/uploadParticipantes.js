$(document).ready(function() {

	if ($('#errores').val() > 0)
	{
		$(document).scrollTop( $("#div-errores").offset().top );
	}

	$('#aceptar').hide();
	$('#guardar').hide();
	$('#cancelar').hide();
	$('#procesar').show();

	$('#procesar').click(function(){
        var file = $('#file').val().split('/').join(',');
		window.location.replace($('#url_procesar').val()+'/'+$('#empresa_id').val()+'/'+file);
	});

	$('#save').click(function(){
		$('#div-e').hide();
        var valid = $("#form").valid();
        if (!valid) 
        {
            notify($('#div-error').html());
        }
        else {
        	$('#save').hide();
            $('#form').submit();
        }
    });

    observe();

    $('.paginate_button').click(function(){
        observe();
    });

});

function observe()
{
    $('.tree').jstree();
}
