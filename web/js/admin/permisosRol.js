$(document).ready(function() {

	$('.aplicacion').click(function(){
		clickAplicacion($(this));
		unload();
	});

	$('.subaplicacion').click(function(){
		clickSubAplicacion($(this));
		unload();
	});

	$('#save').click(function(){
		$('#save').hide();
	});

	afterPaginate();

	$('.paginate_button').click(function(){
		afterPaginate();
	});

	$(document).on("submit", "form", function(event){
	    // disable unload warning
	    $(window).off('beforeunload');
	});

});

function clickAplicacion(cb){
	var id = cb.attr('id');
	var i = id.split('f');
	var aplicacion_id = i[1];
	
	if (cb.is(':checked'))
	{
		$( ".subaplicacion"+aplicacion_id ).each(function( index ) {
			// Activamos las sub-aplicaciones
			$(this).prop('checked', true);
		});
	}
	else {
		$( ".subaplicacion"+aplicacion_id ).each(function( index ) {
			// Desactivamos las sub-aplicaciones
			$(this).prop('checked', false);
		});
	}
}

function clickSubAplicacion(cb){
	var id = cb.attr('id');
	var i = id.split('_');
	var aplicacion_id = i[0];
	
	if (cb.is(':checked'))
	{
		$( "#f"+aplicacion_id ).prop('checked', true);
	}
	
}

function afterPaginate(){
	$('.grant').click(function(){
		var app_id = $(this).attr('data');
		$('.div-app').hide();
		$('#div-'+app_id).show();
	});
}

function unload(){
	$(window).bind('beforeunload', function(){
		return 'Hay datos sin salvar';
	});
}