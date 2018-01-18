$(document).ready(function() {

	$('.tree').jstree();

	$('.delete').click(function(){
		var pagina_id = $(this).attr('data');
		sweetAlertDelete(pagina_id, 'CertiPagina');
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
            reordenar(id, 'CertiPagina', diff[i].newData);
        }
 
    });

});
