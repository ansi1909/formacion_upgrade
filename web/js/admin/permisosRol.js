$(document).ready(function() {

	$('.aplicacion').click(function(){
		clickAplicacion($(this));
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
		$('.tr-subaplicacion'+aplicacion_id).show(1000);
		$( ".subaplicacion"+aplicacion_id ).each(function( index ) {
			// Activamos las sub-aplicaciones
			$(this).prop('checked', true);
		});
	}
	else {
		$('.tr-subaplicacion'+aplicacion_id).hide(1000);
		$( ".subaplicacion"+aplicacion_id ).each(function( index ) {
			// Desactivamos las sub-aplicaciones
			$(this).prop('checked', false);
		});
	}
}
