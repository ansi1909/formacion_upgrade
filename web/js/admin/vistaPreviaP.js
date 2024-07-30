$(document).ready(function() {

	$( document ).tooltip({
    	track: true
    });

	$('#mas_recientes').click(function(){
		$('#mas_recientes_comments').show(1000);
		$('#mas_valorados_comments').hide(1000);
		$('#mas_recientes').addClass('active-line');
		$('#mas_valorados').removeClass('active-line');
	});

	$('#mas_valorados').click(function(){
		$('#mas_recientes_comments').hide(1000);
		$('#mas_valorados_comments').show(1000);
		$('#mas_recientes').removeClass('active-line');
		$('#mas_valorados').addClass('active-line');
	});

});

function getNotificaciones()
{
   return 0;
}