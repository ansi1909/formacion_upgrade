$(document).ready(function() {


	//var root_site = $('#root_site').val();
    $('#select_empresa_id').change(function(){
    	console.log('Usuario admin');
    	var empresa_id = $(this).val();
    	var usuario_empresa = $('#usuario_empresa').val();
		getListadoNotificaciones(empresa_id, usuario_empresa);
		$('#history_programation').hide();
		
	});

    $('.paginate_button').click(function(){
        observe();
    });
	observe();
});

function observe(){
	$('.delete').click(function(){

		var notificacion_id = $(this).attr('data');

		sweetAlertDelete(notificacion_id, 'AdminNotificacion');

	});
	var table = $('#dt').DataTable( {
		destroy: true,
        rowReorder: true

    } );

    table.on( 'row-reorder', function ( e, diff, edit ) {
        
        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
            var rowData = table.row( diff[i].node ).data();
            // Id del registro está en la segunda columna
        	id = rowData[1];
            reordenar(id, 'AdminNotificacion', diff[i].newData);
        }
 
    });
}

function getListadoNotificaciones(empresa_id, usuario_empresa){
	$.ajax({
		type: "GET",
		url: $('#url_notificaciones').val(),
		async: true,
		data: { empresa_id: empresa_id, usuario_empresa: usuario_empresa },
		dataType: "json",
		success: function(data) {
			$('#list_notificaciones').html(data.notificaciones);
			observe();
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}