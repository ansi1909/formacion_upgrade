$(document).ready(function() {

    $('#search').click(function(){
    	
    	$('#label_filtro').hide();
    	$('.descargable').hide();
    	$('.generable').show();
    	$('#resultado').hide();

    	// Resetear los gráficos
    	for (var i=1; i<=3; i++)
    	{
    		resetCanvas('chart'+i, 'barChart', '#canvasCont'+i);
    	}

    });

    $('#pdf').click(function(){
    	$('#pdf').hide();
    	$('#pdf-loader').show();
    	renderIntoImage();
    });

    $('.form_datetime').datetimepicker({
        language:  'es',
        todayBtn:  true,
        autoclose: true,
        todayHighlight: true,
        showMeridian: true,
        format: 'dd/mm/yyyy HH:ii p',
        endDate: new Date()
    });

});

function mostrarReporte(data)
{
	$('#label_fecha').html($('#desde').val());
	$('#label_filtro').show();
	$('#resultado').show();
	
	console.log(data);
	
	$('.week_before').html(data.week_before);
	$('.now').html(data.now);
	$('.week_before_inactivos').html(data.reporte['week_before_inactivos']);
	$('#week_before_inactivos_pct').html(data.reporte['week_before_inactivos_pct']);
	$('.now_inactivos').html(data.reporte['now_inactivos']);
	$('#now_inactivos_pct').html(data.reporte['now_inactivos_pct']);
	$('.week_before_activos').html(data.reporte['week_before_activos']);
	$('#week_before_activos_pct').html(data.reporte['week_before_activos_pct']);
	$('.now_activos').html(data.reporte['now_activos']);
	$('#now_activos_pct').html(data.reporte['now_activos_pct']);
	$('.week_before_total1').html(data.reporte['week_before_total1']);
	$('#week_before_total1_pct').html(data.reporte['week_before_total1_pct']);
	$('.now_total1').html(data.reporte['now_total1']);
	$('#now_total1_pct').html(data.reporte['now_total1_pct']);
	$('.week_before_no_iniciados').html(data.reporte['week_before_no_iniciados']);
	$('.now_no_iniciados').html(data.reporte['now_no_iniciados']);
	$('.week_before_en_curso').html(data.reporte['week_before_en_curso']);
	$('.now_en_curso').html(data.reporte['now_en_curso']);
	$('.week_before_aprobados').html(data.reporte['week_before_aprobados']);
	$('.now_aprobados').html(data.reporte['now_aprobados']);
	$('#week_before_total2').html(data.reporte['week_before_total2']);
	$('#now_total2').html(data.reporte['now_total2']);
	$('#week_before_total3').html(data.reporte['week_before_total3']);
	$('#now_total3').html(data.reporte['now_total3']);
	$('#label_programa').html(data.programa);

	$('.reporte').show();
	console.log(data.reporte['now_inactivos_pct']);
	console.log(data.reporte['now_activos_pct']);

	var datos1 = {
        type: "pie",
        data: {
            datasets: [{
                label: 'Estatus de Participantes al '+data.now,
                data: [
                	data.reporte['now_inactivos_pct'],
                	data.reporte['now_activos_pct']
                ],
                backgroundColor: ["#0070c0", "#ed7d31"],
            }],
            labels: ['Inactivos', 'Activos']
        },
        options: {
            responsive: true
        }
    };
    var canvas1 = document.querySelector('#chart1').getContext('2d');
    window.pie1 = new Chart(canvas1, datos1);
   	
   	//$('#desdef').val(data.desdef);
   	//$('#hastaf').val(data.hastaf);

}

const renderIntoImage = () => {

	// Función que transforma el gráfico en imagen
  	const canvas = document.getElementById('myChart');
  	var src_img = getImgFromCanvas(canvas);
  	
  	// Se almacena la imagen en el servidor
	$.ajax({
		type: "POST",
		url: $('#url_img').val(),
		async: true,
		data: "bin_data="+src_img,
		dataType: "json",
		success: function(response) {
			$('#pdf-loader').hide();
			var href = $("#url_pdf").val()+'/'+$('#empresa_id').val()+'/'+$('#desdef').val()+'/'+$('#hastaf').val();
        	$("#pdf-link").attr("href", href);
        	$('#pdf-link').show();
		},
		error: function(){
			$('#div-error-server').html($('#error-msg').val());
			notify($('#div-error-server').html());
			$('.descargable').hide();
			$('.generable').show();
		}
	});

}