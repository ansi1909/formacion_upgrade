$(document).ready(function() {

	observeList();

	
	/*var href = $("#url_pdf").val()+'/'+$('#empresa_id').val()+'/'+$('#desdef').val()+'/'+$('#hastaf').val()+'/'+$('#rol').val();

	$('#pdf').click(function(){
		var username = $('#username').val();
    	var empresa_id = $('#empresa_id').val();
		console.log(empresa_id);
		$.ajax({
			type: "POST",
			url: $('#url_pdfdetalle').val(),
			async: true,
			data: { empresa_id: empresa_id, username: username },
			dataType: "json",
			success: function(data) {
				console.log('ok')
			},
			error: function(){
				$('#div-alert-detail').show();
				$('#loadDetail').hide();
			}
		});

	});*/

});

function setDetails(data)
{
	$('#loadDetail').hide();
	$('#contenidoDetail').show();
	var img = $('#foto');
	if (data.usuario.foto != 0)
	{
		var uploads = $('#uploads').val();
		img.attr("src", uploads+data.usuario.foto);
	}
	else {
		img.attr("src", $('#profilePhoto').val());
	}
	$('#login').val(data.usuario.login);
	$('#clave').val(data.usuario.clave);
	$('#nombre').val(data.usuario.nombre);
	$('#apellido').val(data.usuario.apellido);
	$('#correoPersonal').val(data.usuario.correoPersonal);
	$('#fechaNacimiento').val(data.usuario.fechaNacimiento);
	$('#activo').html(data.usuario.activo);
	$('#time_zone').html(data.usuario.timeZone);
	$('#correoCorporativo').val(data.usuario.correoCorporativo);
	$('#campo1').val(data.usuario.campo1);
	$('#campo2').val(data.usuario.campo2);
	$('#campo3').val(data.usuario.campo3);
	$('#campo4').val(data.usuario.campo4);
	$('#nivel').val(data.usuario.nivel);
	$('#primeraConexion').val(data.usuario.ingresos.primeraConexion);
	$('#ultimaConexion').val(data.usuario.ingresos.ultimaConexion);
	$('#cantidadConexiones').val(data.usuario.ingresos.cantidadConexiones);
	$('#promedioConexion').val(data.usuario.ingresos.promedioConexion);
	$('#noIniciados').val(data.usuario.ingresos.noIniciados);
	$('#enCurso').val(data.usuario.ingresos.enCurso);
	$('#finalizados').val(data.usuario.ingresos.finalizados);
	$('#programasAsignados').html(data.html);
	progressCircle();
	var href = $('#url_pdfdetalle').val()+'/'+$('#empresa_id').val()+'/'+$('#username').val();
	console.log('hola');
	$('#pdf-detalle').attr('href', href);
	
}

function progressCircle()
{

	$('.progress-success').circleProgress({
		fill: {gradient: ["#2dc1c9", "#0d769f"]},
		lineCap: 'butt'
	}).on('circle-animation-progress', function(event, progress,stepValue) {
		$(this).find('strong').html(Math.round(100 * progress * stepValue) + '<i>%</i>');
	});

	$('.progress-danger').circleProgress({
     	fill: {gradient:["#f6775a", "#ed5a7c"]},
	}).on('circle-animation-progress', function(event, progress,stepValue) {
    	$(this).find('strong').html(Math.round(100 * progress * stepValue) + '<i>%</i>');
  	});

	$('.progress-warning').circleProgress({
    	fill: {gradient: ["#ff9300", "#ff5800"]},
    	lineCap: 'butt'
	}).on('circle-animation-progress', function(event, progress,stepValue) {
    	$(this).find('strong').html(Math.round(100 * progress * stepValue) + '<i>%</i>');
  	});

}

function observeList()
{
	$('.detail').unbind('click');
	$('.detail').click(function(){
		$('#reporteDetail').show();
		$('#contenidoDetail').hide();
    	$('#loadDetail').show();
    	$('#headerDetail').hide();
    	$('#div-alert-detail').hide();
    	var username = $(this).attr('data');
    	var empresa_id = $(this).attr('empresa_id');
		$.ajax({
			type: "POST",
			url: $('#url_detail').val(),
			async: true,
			data: { empresa_id: empresa_id, username: username },
			dataType: "json",
			success: function(data) {
				setDetails(data);	
			},
			error: function(){
				$('#div-alert-detail').show();
				$('#loadDetail').hide();
			}
		});
	});

}
