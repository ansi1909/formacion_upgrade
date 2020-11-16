$(document).ready(function() {
	var table = $('#dt').DataTable( {
		destroy: true,
        rowReorder: true,
        responsive: false,
        pageLength:10,
        sPaginationType: "full_numbers",
        lengthChange: false,
        info: false,
        fnDrawCallback: function(){
         observe();
        },
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
});



function activarEnlaces()
{
	$('.enlaces').removeClass('enlaceInactivo');
	$('.enlaces').addClass('enlaceActivo');

	return 0;
}

function desactivarEnlaces()
{
	$('.enlaces').removeClass('enlaceActivo');
	$('.enlaces').addClass('enlaceInactivo');

	return 0;
}

function observe()
{
	$('.downloadDb').unbind('click');
	$('.downloadDb').click(function(){
		desactivarEnlaces();
		var empresaId = $(this).attr('data-empresa');
		$('#botonExcel'+empresaId).remove();
		$('#excel-loader'+empresaId).show();
		
		$.ajax({/*------------------------------ peticion para gegerar excel -------------*/
			type: "POST",
			url: $('#formEmpresa'+empresaId).attr('action'),
			async: true,
			data: $('#formEmpresa'+empresaId).serialize(),
			dataType: "json",
			success: function(data) {
				activarEnlaces()
				$('#excel-loader'+empresaId).hide();
				if (data.ok == 1) {
				   $('#acciones'+empresaId).append(data.html);
				}
				else //no existen registros
				{
				  $('#div-warning-empresa').html($('#warning-msg-empresa').val());
			      notify($('#div-warning-empresa').html(),'warning'," ");
				  $('#acciones'+empresaId).append('<a href="#" data-empresa="'+empresaId+'" id="botonExcel'+empresaId+'" class= "btn btn-link btn-sm enlaces downloadDb" ><span class="fa fa-file-excel-o" ></span></a >');
				  activarEnlaces()
				  observe()
				}
				
			},
			error: function(){
				$('#excel-loader'+empresaId).hide();
				$('#acciones'+empresaId).append('<a href="#" data-empresa="'+empresaId+'" id="botonExcel'+empresaId+'" class= "btn btn-link btn-sm enlaces downloadDb" ><span class="fa fa-file-excel-o" ></span></a >');
				activarEnlaces()
				$('#div-error-empresa').html($('#error-msg-empresa').val());
			    notify($('#div-error-empresa').html());
				observe();
				
			}
		});


	})//fin de la funcion

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
			data: { id: app_id, entity: 'AdminEmpresa', checked: checked },
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

	$('.cb_activo2').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f2');
		var app_id = id_arr[1];
		$('#div-alert').hide();
		$.ajax({
			type: "POST",
			url: $('#url_active').val(),
			async: true,
			data: { id: app_id, entity: 'AdminEmpresa', checked: checked },
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

	$('.cb_activo3').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f3');
		var app_id = id_arr[1];
		$('#div-alert').hide();
		$.ajax({
			type: "POST",
			url: $('#url_active').val(),
			async: true,
			data: { id: app_id, entity: 'AdminEmpresa', checked: checked },
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

	$('.delete').click(function(){
		var empresa_id = $(this).attr('data');
		sweetAlertDelete(empresa_id, 'AdminEmpresa');
	});

}