$(document).ready(function() {

    $('#search').click(function(){
    	$('#label_filtro').hide();
    	$('.dayMaxCon').removeClass('dayMaxCon');
    	$('.hourMaxCon').removeClass('hourMaxCon');
    	$('.descargable').hide();
    	$('.generable').show();
    	$('#resultado').hide();
    	resetCanvas('myChart', 'barChart', '.canvasCont');
    	$('#grafico').hide();
    });

    $('#excel').click(function(){
    	$('#excel').hide();
    	$('#excel-loader').show();
    	$.ajax({
			type: "POST",
			url: $('#form').attr('action'),
			async: true,
			data: $("#form").serialize()+'&excel=1',
			dataType: "json",
			success: function(data) {
				$('#excel-loader').hide();
	        	$("#excel-link").attr("href", data.archivo);
	        	$('#excel-link').show();
			},
			error: function(){
				$('#div-error-server').html($('#error-msg').val());
				notify($('#div-error-server').html());
				$('.descargable').hide();
    			$('.generable').show();
			}
		});
    });

    $('#pdf').click(function(){
    	$('#pdf').hide();
    	$('#pdf-loader').show();
    	renderIntoImage();
    });

});

function mostrarReporte(data)
{
	console.log(data);
	$('#label_desde').html($('#desde').val());
	$('#label_hasta').html($('#hasta').val());
	$('#label_horario').html(data.timeZone);
	$('#label_filtro').show();
	$('#resultado').show();
	
	console.log(data);
	var totales = [];
	var etiquetas = [];
	
	// Mostrar las cantidades
	for (var f = 0; f <= 8; f++)
	{
		if (jQuery.inArray(f, data.filas_mayores) !== -1)
		{
			$('#f'+f).addClass('dayMaxCon');
			$('#celda_'+f+'_25').addClass('hourMaxCon');
		}
		for (var c = 0; c <= 25; c++)
		{
			$('#celda_'+f+'_'+c).html(data.conexiones[f][c]);
			if (f > 0 && f != 8 && c == 25)
			{
				totales.push(data.conexiones[f][c]);
			}
			if (jQuery.inArray(c, data.columnas_mayores) !== -1)
			{
				$('#celda_'+f+'_'+c).addClass('hourMaxCon');
			}
		}
		if (f > 0 && f != 8)
		{
			etiquetas.push(data.conexiones[f][0]);
		}
	}

	var datos = {
        type: "horizontalBar",
        data: {
            datasets: [{
                label: '',
                data: totales,
                backgroundColor: ["#f9b1b4", "#f9ba7a", "#6fa8f2", "#9e9cd1", "#f7e894", "#abbc98", "#cbb0e5"],
                borderColor: ['#fd5c63', '#ff9933', '#1a67cc', '#6a67ce', '#d8bc1e', '#8aba56', '#a560e8'],
                borderWidth: 1
            }],
            labels: etiquetas
        },
        options: {
            responsive: true,
            legend: {
                display: false
            },
            animation: {
	            duration: 500,
	            easing: "easeOutQuart",
	            onComplete: function () {
	                var ctx = this.chart.ctx;
	                ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontFamily, 'normal', Chart.defaults.global.defaultFontFamily);
	                ctx.textAlign = 'center';
	                ctx.textBaseline = 'bottom';

	                this.data.datasets.forEach(function (dataset) {
	                    for (var i = 0; i < dataset.data.length; i++) {
	                        var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model,
	                            scale_max = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._yScale.maxHeight;
	                        ctx.fillStyle = '#444';
	                        var y_pos = model.y + 7;
	                        var x_pos = model.x - 15;
	                        // Make sure data value does not get overflown and hidden
	                        // when the bar's value is too close to max value of scale
	                        // Note: The y value is reverse, it counts from top down
	                        if ((scale_max - model.y) / scale_max >= 0.93){
	                            y_pos = model.y + 20;
	                        }
	                        if (dataset.data[i] < 1)
	                        {
	                        	x_pos = model.x + 5;
	                        }
	                        ctx.fillText(dataset.data[i], x_pos, y_pos);
	                    }
	                });               
	            }
	        },
	        scales: {
		        xAxes: [{
		            ticks: {
		                beginAtZero: true
		            }
		        }]
		    }
        }
    };
    var canvas = document.querySelector('.barChart').getContext('2d');
    window.horizontalBar = new Chart(canvas, datos);
   	
   	$('#grafico').show();
   	$('#desdef').val(data.desdef);
   	$('#hastaf').val(data.hastaf);

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