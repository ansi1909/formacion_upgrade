$(document).ready(function() {

	var total = $('#total').val();

	$('.btn_sp').click(function(){

		var next = $(this);
		next.hide();
		var nro = $('#nro').val();

		// Escondemos la corriente pregunta
		$('#pregunta-'+nro).hide(1000);

		// Porcentaje de avance
		var porcentaje = parseInt(((nro/total)*100), 10);
		$("#progreso").attr("style", 'width: '+porcentaje+'%');

		if (nro < total)
		{
			nro = parseInt(nro)+parseInt(1);
			$('#nro').val(nro);
			var nro_pregunta = nro < 10 ? '0'+nro : nro;
			$('#nro_pregunta').html(nro_pregunta);
			$('#pregunta-'+nro).show(1000); // Se muestra la siguiente pregunta
			next.show();
		}

	});

});
