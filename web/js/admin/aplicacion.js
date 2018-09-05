$(document).ready(function() {

	$('#div-active-alert').hide();

	$('#form').submit(function(e) {
		e.preventDefault();
	});

	$('#guardar').click(function(){
		$('#form').submit();
		return false;
	});

	$('#form').safeform({
		submit: function(e) {
			$('#div-alert').hide();
			if ($("#form").valid())
			{
				$('#guardar').prop('disabled', true);
				$.ajax({
					type: "POST",
					url: $('#form').attr('action'),
					async: true,
					data: $("#form").serialize(),
					dataType: "json",
					success: function(data) {
						$('.form-control').val('');
						$('#p-nombre').html(data.nombre);
						$('#p-url').html(data.url);
						$('#p-icono').html(data.icono);
						$('#p-activo').html(data.activo);
						$('#p-subaplicacion').html(data.subaplicacion);
						if (data.subaplicacion_id)
						{
							$('#div-subaplicacion').show();
						}
						else {
							$('#div-subaplicacion').hide();
						}
						$( "#detail-edit" ).attr( "data", data.id );
						if (data.delete_disabled != '')
						{
							$( "#detail-delete" ).hide();
							$( "#detail-delete" ).removeClass( "delete" );
						}
						else {
							$( "#detail-delete" ).attr( "data", data.id );
							$( "#detail-delete" ).addClass( "delete" );
							$( "#detail-delete" ).show();
							$('.delete').unbind('click');
							$('.delete').click(function(){
								var app_id = $(this).attr('data');
								sweetAlertDelete(app_id, 'AdminAplicacion');
							});
						}
						
						initModalShow();

						// manual complete, reenable form ASAP
						$('#form').safeform('complete');
						return false; // revent real submit
								
					},
					error: function(){
						$('#alert-error').html($('#error_msg-save').val());
						$('#div-alert').show();
						$('#guardar').prop('disabled', false);
						$('#form').safeform('complete');
                        return false; // revent real submit
					}
				});
			}
			else {
				$('#form').safeform('complete');
                return false; // revent real submit
			}
		}
	});

	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.new').click(function(){
		initModalEdit();
		enableSubmit();
		$('#app_id').val("");
		$('#nombre').val("");
		$('#url').val("");
		$('#icono').val("");
		$('#subaplicacion_id').html($('#aplicaciones_str').val());
	});

	afterPaginate();

	$('.paginate_button').click(function(){
		afterPaginate();
	});

	var table = $('#dt').DataTable( {
		destroy: true,
        rowReorder: true,
        responsive: false,
	    pageLength:10,
	    sPaginationType: "full_numbers",
	    lengthChange: false,
	    info: false,
	    oLanguage: {
            "sProcessing":    "Procesando...",
            "sLengthMenu":    "'Mostrar _MENU_ registros",
            "sZeroRecords":   "No se encontraron resultados",
            "sEmptyTable":    "Ningún dato disponible en esta tabla",
            "sInfo":          "Mostrando registros del _START_ al _END_ de un total de _TOTAL_.",
            "sInfoEmpty":     "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":  "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":   "",
            "sSearch":        "Buscar:",
            "sUrl":           "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            oPaginate: {
                sFirst: "<<",
                sPrevious: "<",
                sNext: ">", 
                sLast: ">>" 
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    } );

    table.on( 'row-reorder', function ( e, diff, edit ) {
        
        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
            var rowData = table.row( diff[i].node ).data();
            // Id del registro está en la segunda columna
        	id = rowData[1];
            reordenar(id, 'AdminAplicacion', diff[i].newData);
        }
 
    });

    $( ".columorden" )
      .mouseover(function() {
        $( '.columorden' ).css( 'cursor','move' );
      })
      .mouseout(function() {
        $( '.columorden' ).css( 'cursor','auto' );
    });

	observe();

	disableSubmit();

});

function observe()
{

	$('.edit').unbind('click');
	$('.edit').click(function(){
		var app_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		initModalEdit();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { app_id: app_id },
			dataType: "json",
			success: function(data) {
				enableSubmit();
				$('#app_id').val(app_id);
				$('#nombre').val(data.nombre);
				$('#url').val(data.url);
				$('#icono').val(data.icono);
				$('#activo').prop('checked', data.activo);
				$('#subaplicacion_id').html(data.subaplicaciones);
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

	$('.cb_activo').unbind('click');
	$('.cb_activo').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f');
		var app_id = id_arr[1];
		$('#div-alert').hide();
		$.ajax({
			type: "POST",
			url: $('#url_active').val(),
			async: true,
			data: { id: app_id, entity: 'AdminAplicacion', checked: checked },
			dataType: "json",
			success: function(data) {
				console.log('Activación/Desactivación realizada. Id '+data.id);
			},
			error: function(){
				$('#active-error').html($('#error_msg-active').val());
				$('#div-active-alert').show();
			}
		});
	});

	$('.delete').unbind('click');
	$('.delete').click(function(){
		var app_id = $(this).attr('data');
		sweetAlertDelete(app_id, 'AdminAplicacion');
	});

	var table2 = $('#dtSub').DataTable( {
        rowReorder: true,
        responsive: false,
        pageLength:10,
        sPaginationType: "full_numbers",
        lengthChange: false,
        info: false,
        oLanguage: {
        	"sProcessing":    "Procesando...",
            "sLengthMenu":    "Mostrar _MENU_ registros",
            "sZeroRecords":   "No se encontraron resultados",
            "sEmptyTable":    "Ningún dato disponible en esta tabla",
            "sInfo":          "Mostrando registros del _START_ al _END_ de un total de _TOTAL_.",
            "sInfoEmpty":     "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":  "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":   "",
            "sSearch":        "Buscar:",
            "sUrl":           "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            oPaginate: {
                sFirst: "<<",
                sPrevious: "<",
                sNext: ">", 
                sLast: ">>" 
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }

    } );

    table2.on( 'row-reorder', function ( e, diff, edit ) {
        
        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
            var rowData = table2.row( diff[i].node ).data();
            // Id del registro está en la segunda columna
        	id = rowData[1];
            reordenar(id, 'AdminAplicacion', diff[i].newData);
        }
 
    });

    $( ".columorden" )
          .mouseover(function() {
            $( '.columorden' ).css( 'cursor','move' );
          })
          .mouseout(function() {
            $( '.columorden' ).css( 'cursor','auto' );
    });

}

function afterPaginate(){

	$('.see').unbind('click');
	$('.see').click(function(){
		var app_id = $(this).attr('data');
		$('#div-active-alert').hide();
		$('#div-subapps, .load1').show();
		$('#cardSub').hide();
		$.ajax({
			type: "GET",
			url: $('#url_subapps').val(),
			async: true,
			data: { app_id: app_id },
			dataType: "json",
			success: function(data) {
				$('.load1').hide();
				$('#subapp').html(data.html);
				$('#appTitle').html(data.nombre);
				$('#div-subapps').show();
				$('#cardSub').show();
				observe();
			},
			error: function(){
				$('.load1').hide();
				$('#active-error').html($('#error_msg-subapps').val());
				$('#div-active-alert').show();
				$('#div-subapps').hide();
			}
		});
	});

}