$(document).ready(function() {

    $(".date_picker").datepicker({
		startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es'
    });

    $('#f_evaluacion').click(function(){
		if ($(this).is(':checked'))
		{
			// Se activan los campos de evaluación
			$('#puntajeAprueba').prop('disabled', false);
			$('#maxIntentos').prop('disabled', false);
		}
		else {
			// Se desactivan los campos de evaluación
			$('#puntajeAprueba').prop('disabled', true);
			$('#maxIntentos').prop('disabled', true);
		}
	});

});
