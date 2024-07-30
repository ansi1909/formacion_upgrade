$(document).ready(function() {

	$('#empresa_id').change(function(){
    	var empresa_id = $(this).val();
    	$('#div-active-alert').hide();
    	$('#notificaciones').hide();
    	$('.load1').show();
    	$.ajax({
			type: "GET",
			url: $('#url_notificaciones').val(),
			async: true,
			data: { empresa_id: empresa_id },
			dataType: "json",
			success: function(data) {
				$('.load1').hide();
				$('#notificaciones').show();
				$('#notificaciones').html(data.html);
				applyDataTable();
				observe();
				clearTimeout( timerId );
			},
			error: function(){
				$('#active-error').html($('#error_msg-filter').val());
				$('#div-active-alert').show();
			}
		});
	});

    $('.paginate_button').click(function(){
        observe();
    });

	observe();
	
});

function observe(){

	$('.delete').unbind('click');
	$('.delete').click(function(){
		var notificacion_id = $(this).attr('data');
		sweetAlertDelete(notificacion_id, 'AdminNotificacion');
	});

}
