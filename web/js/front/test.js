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

	$('.opc, .opc_img').unbind('click');

	$('.opc, .opc_img').click(function(){
		var div_opc = $(this);
		var div_str = div_opc.attr('class');
		var data = div_opc.attr('data');
		var data_arr = data.split('_');
		var tipo_elemento = data_arr[0];
		var tipo_pregunta = data_arr[1];
		var po_id = data_arr[2];
		var css = tipo_elemento==1 ? 'opc_activa' : 'opc_activa-img';
		var div_class = tipo_elemento==1 ? 'opc' : 'opc_img';
		if (tipo_pregunta == 1)
		{
			$('.'+div_class).removeClass(css);
			div_opc.addClass(css);
		}
		else {
			// Si ya estaba activado, se desactiva; y viceversa
			if (div_str.indexOf(css) == -1)
			{
				div_opc.addClass(css);
			}
			else {
				div_opc.removeClass(css);
			}
		}
	});

	$('.opc_lado-a').draggable({ revert: true, helper: "clone" });

	$( ".opc_lado-b" ).droppable({
     	drop: function( event, ui ) {
     		var target = $(this);
      		console.log('me soltaste en: '+ target.attr('data'));
      		console.log(ui.draggable.attr('style'));
      		var style = ui.draggable.attr('style');
      		target.attr("style", style);
        	//$( this ).addClass( "opc_activa" );
      	}
    });

});
