$(document).ready(function() {
    applyDataTableProgramados();
	afterPaginate();

	$('#empresa_id').change(function(){
		var empresa_id = $(this).val();
		$('#card-programados').hide();
		$('#load-programados').show();
		$.ajax({
			type: "POST",
			url: $('#url_programados_empresa').val(),
			async: true,
			data: { empresa_id: empresa_id },
			dataType: "json",
			success: function(data) {
				console.log(data.html);
				$('#card-programados').html(data.html);
				$('#card-programados').show();
				$('#load-programados').hide();
				applyDataTableProgramados();
				observe();

/* 				$('#downloadUsuarios'+npId).attr('data-href',data);
				$('#usuariosLoader'+npId).hide();
				$('#downloadUsuarios'+npId).show(); */
			},
			error: function(){
				console.log('Error de comunicacion');
			}
		});
		
	})

	$('.paginate_button').click(function(){
		afterPaginate();
	});

	$( "#programados" ).on( "click",".delete" , function (){
        var programada_id = $(this).attr('data');
		sweetAlertDelete(programada_id,'AdminNotificacionProgramada');
	});

	$('.usuariosCorreos').click(function(){
		var npId = $(this).attr('data');
		console.log(npId);
        $('#usuarios'+npId).hide();
		$('#usuariosLoader'+npId).show();
		$.ajax({
			type: "POST",
			url: $('#url_usuarios_excel').val(),
			async: true,
			data: { notificacion_programada_id: npId },
			dataType: "json",
			success: function(data) {
				$('#downloadUsuarios'+npId).attr('data-href',data);
				$('#usuariosLoader'+npId).hide();
				$('#downloadUsuarios'+npId).show();
			},
			error: function(){
				console.log('Error de comunicacion');
			}
		});
	});

		$('.downloadExcel').click(function(event) {
		window.location.href = $(this).attr('data-href');
		});

});

function observe()
{
	afterPaginate();
	$('#tbody-programados tr').each(function(){
		var tr = $(this).attr('id');
		if (!(typeof tr === 'undefined' || tr === null)){
			var tr_arr = tr.split('tr-');
			var notificacion_programada_id = tr_arr[1];
			//treeGrupoProgramado(notificacion_programada_id);
		}
	});

	$('.failedEmails').click(function(e){
		e.preventDefault();
		var npId = $(this).attr('data');
        $('#excel'+npId).hide();
        $('#excelLoader'+npId).show();
		$.ajax({
			type: "POST",
			url: $('#url_correos_excel').val(),
			async: true,
			data: { notificacion_id: npId },
			dataType: "json",
			success: function(data) {
				$('#downloadExcel'+npId).attr('data-href',data.archivo);
				$('#excelLoader'+npId).hide();
				$('#downloadExcel'+npId).show();
			},
			error: function(){
				console.log('Error de comunicacion');
			}
		});
	});

	   $('.downloadUsuarios').click(function(event) {
	   	window.location.href = $(this).attr('data-href');
   });

   $('.usuariosCorreos').click(function(){
	var npId = $(this).attr('data');
	console.log(npId);
	$('#usuarios'+npId).hide();
	$('#usuariosLoader'+npId).show();
	$.ajax({
		type: "POST",
		url: $('#url_usuarios_excel').val(),
		async: true,
		data: { notificacion_programada_id: npId },
		dataType: "json",
		success: function(data) {
			$('#downloadUsuarios'+npId).attr('data-href',data);
			$('#usuariosLoader'+npId).hide();
			$('#downloadUsuarios'+npId).show();
		},
		error: function(){
			console.log('Error de comunicacion');
		}
	});
});

	$('.downloadExcel').click(function(event) {
	window.location.href = $(this).attr('data-href');
	});



}


/*function treeGrupoProgramado(notificacion_programada_id)
{
    $('#td-'+notificacion_programada_id).jstree({
        'core' : {
            'data' : {
                "url" : $('#url_tree').val()+'/'+notificacion_programada_id,
                "dataType" : "json"
            }
        }
	});
	$('#td-'+notificacion_programada_id).attr('data-cantidad');
}*/

function afterPaginate()
{
	$('.see').unbind('click');
	$('.see').click(function(){
		var notificacion_id = $(this).attr('data');
		$('#div-programados, .load1').show();
		$('#div-programados-alert').hide();
		$('#programados').hide();
		$.ajax({
			type: "GET",
			url: $('#url_programados').val(),
			async: true,
			data: { notificacion_id: notificacion_id },
			dataType: "json",
			success: function(data) {
				$('.load1').hide();
				$('#programados').html(data.html);
				$('#notificacionTitle').html(data.notificacion);
				$('#programados').show();
				applyDataTableProgramados();
				observe();
			},
			error: function(){
				$('.load1').hide();
				$('#programados').hide();
				$('#div-programados-alert').show();
			}
		});
	});
}
