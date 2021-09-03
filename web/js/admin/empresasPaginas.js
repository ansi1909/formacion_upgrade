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
		 afterPaginate();
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

	afterPaginate();

	$('.paginate_button').click(function(){
		afterPaginate();
	});
    $('#formOrden').submit(function(e) {
		e.preventDefault();
	});

});

function observe()
{


    $('#input_orden').focus(function(event) {
    	$('#error-orden').hide();
    	$('#validar-orden').hide();
    });
    $('.orden').unbind('click');
    $('.orden').click(function(event) {
    	var id = $(this).attr('id');
    	console.log(id);
    	$('#pagina_empresa_id').val(id);
    	$('#input_orden').val($(`#orden-${id}`).val());
    	$('#error-orden').hide();
    	$('#validar-orden').hide();
    	$('#modalOrden').modal('show');
    });

    $('#guardarOrden').unbind('click');
    $('#guardarOrden').click(function(event) {
    	event.preventDefault();
    	var orden = $('#input_orden').val();
    	var pagina_empresa_id = $('#pagina_empresa_id').val();
    	if (orden) {
    		    $.ajax({
					type: "POST",
					url: $('#formOrden').attr('action'),
					async: true,
					data: $("#formOrden").serialize(),
					dataType: "json",
					success: function(data) {
					  console.log('Exito al editar el orden de la pagina');
					  var pagina_id = $('#asignacion_empresa_id').val();
					  $('#modalOrden').modal('hide');
                      $(`#ver-${pagina_id}`).trigger("click");
					},
					error: function(){
						$('#error-orden').show();
					}
				});
    	}else{
    		$('#validar-orden').show();
    	}

    });


	$('.cb_activo').unbind('click');
	$('.cb_activo').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f_active');
		var pagina_empresa_id = id_arr[1];
		$.ajax({
			type: "POST",
			url: $('#url_active').val(),
			async: true,
			data: { id: pagina_empresa_id, entity: 'CertiPaginaEmpresa', checked: checked },
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

	$('.cb_acceso').unbind('click');
	$('.cb_acceso').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f_access');
		var pagina_empresa_id = id_arr[1];
		$.ajax({
			type: "POST",
			url: $('#url_acceso').val(),
			async: true,
			data: { pagina_empresa_id: pagina_empresa_id, checked: checked },
			dataType: "json",
			success: function(data) {
				console.log('Acceso concedido/No concedido realizado. Id '+data.id);
			},
			error: function(){
				$('#active-error').html($('#error_msg-acceso').val());
				$('#div-active-alert').show();
			}
		});
	});

}


function treePaginas(pagina_empresa_id)
{
    $('#td-'+pagina_empresa_id).jstree({
        'core' : {
            'data' : {
                "url" : $('#url_tree').val()+'/'+pagina_empresa_id,
                "dataType" : "json"
            }
        }
    });
}

function afterPaginate()
{
	$('.see').unbind('click');
	$('.see').click(function(){
		var empresa_id = $(this).attr('data');
		$('#asignacion_empresa_id').val(empresa_id);

		$('#div-active-alert').hide();
		$('#div-pages, .load1').show();
		$.ajax({
			type: "GET",
			url: $('#url_pages').val(),
			async: true,
			data: { empresa_id: empresa_id },
			dataType: "json",
			success: function(data) {
				console.log(data.html);
				$('.load1').hide();
				$('#pages').html(data.html);
				$('#empresaTitle').html(data.empresa);
				$('#div-pages').show();
				observe();
			},
			error: function(){
				$('#active-error').html($('#error_msg-pages').val());
				$('#div-active-alert').show();
				$('#div-pages').hide();
			}
		});
	});
}
