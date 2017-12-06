$(document).ready(function() {

	if ($('#errores').val() > 0)
	{
		$(document).scrollTop( $("#div-errores").offset().top );
	}

	$('#aceptar').show();
	$('#guardar').hide();
	$('#cancelar').hide();

	$('#aceptar').click(function(){
		window.location.replace($('#url_niveles').val());
	});

});
