$(document).ready(function() {

	$('.aplicacion').click(function(){
		clickAplicacion($(this));
	});

	$( ".aplicacion" ).each(function( index ) {
		clickAplicacion($(this));
	});

	$('.subaplicacion').click(function(){
		var cl = $(this).attr('class');
		var c = cl.split('subaplicacion');
		var aplicacion_id = $.trim(c[1]);
		clickAplicacion($('#f'+aplicacion_id));
	});

	$('#save').click(function(){
		$('#save').hide();
	});

});

function clickAplicacion(cb){
	var id = cb.attr('id');
	var i = id.split('f');
	var aplicacion_id = i[1];
	if (cb.is(':checked'))
	{
		$('.tr-subaplicacion'+aplicacion_id).show();
	}
	else {
		$('.tr-subaplicacion'+aplicacion_id).hide();
		$( ".subaplicacion"+aplicacion_id ).each(function( index ) {
			// Desactivamos las sub-aplicaciones
			$(this).prop('checked', false);
		});
	}
}
