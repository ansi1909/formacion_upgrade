$(document).ready(function() {
	
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

	
 observe();
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
	$('.downloadDb').click(function(){
		$('#div-active-alert').hide();
		$('#div-active-warning').hide();
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
				  $('#div-active-warning').html(data.html);
				  $('#div-active-warning').show();
				  $('#acciones'+empresaId).append('<a href="#" data-empresa="'+empresaId+'" id="botonExcel'+empresaId+'" class= "btn btn-link btn-sm enlaces downloadDb" ><span class="fa fa-file-excel-o" ></span></a >');
				  activarEnlaces()
				 $( ".downloadDb" ).unbind( "click" );
				  observe()
				}
				
			},
			error: function(){
				$('#excel-loader'+empresaId).hide();
				$('#acciones'+empresaId).append('<a href="#" data-empresa="'+empresaId+'" id="botonExcel'+empresaId+'" class= "btn btn-link btn-sm enlaces downloadDb" ><span class="fa fa-file-excel-o" ></span></a >');
				activarEnlaces()
				$('#div-active-alert').show();
				$( ".downloadDb" ).unbind( "click" );
				observe();
				
			}
		});


	})//fin de la funcion
}