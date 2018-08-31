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
				$('#acciones'+empresaId).append(data.html);
				// $('#excel-loader').hide();
	   //      	$("#excel-link").attr("href", data.archivo);
	   //      	$('#excel-link').show();
			},
			error: function(){
				// $('#div-error-server').html($('#error-msg').val());
				// notify($('#div-error-server').html());
				// $('.descargable').hide();
    // 			$('.generable').show();
			}
		});


	})//fin de la funcion

});

function agregarBotonDescarga(empresaId)
{
	// $('#'+'empresa'+empresaId).append(" <a href="#" data-empresa="+empresaId+" class="+'btn btn-link btn-sm enlaces downloadDb'+" ><span class="+'fa fa-download'
	// 	"></span></a >");
	// activarEnlaces();
	// $('#excel-loader').hide();
}

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