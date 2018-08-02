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

	// Gráfico 1
	var datos1 = {
        type: "pie",
        data: {
            datasets: [{
                data: [
                	data.reporte['now_inactivos_pct'],
                	data.reporte['now_activos_pct']
                ],
                backgroundColor: ["#0070c0", "#ed7d31"],
            }],
            labels: ['Inactivos', 'Activos']
        },
        options: {
            responsive: true,
            legend: {
            	display: true,
            	position: 'right'
            },
            title: {
            	display: true,
            	text: 'Estatus de Participantes '+data.now,
            	position: 'top',
            	fontSize: 14
            },
            tooltips: {
	            callbacks: {
	                label: function(tooltipItem, data1) {
	                	var label = data1.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || '0';
	                    if (label) {
	                        label += '%';
	                    }
	                    return label;
	                }
	            }
	        },
	        pieceLabel: {
	          render: 'percentage',
	          fontColor: ['white', 'white']
	        }
        }
    };
    var canvas1 = document.querySelector('#chart1').getContext('2d');
    window.pie1 = new Chart(canvas1, datos1);

    // Gráfico 2
	var datos2 = {
        type: "pie",
        data: {
            datasets: [{
                data: [
                	data.reporte['now_no_iniciados'],
                	data.reporte['now_en_curso'],
                	data.reporte['now_aprobados']
                ],
                backgroundColor: ["#ed7d31", "#ffc000", "#92d050"],
            }],
            labels: ['No iniciados', 'En curso', 'Aprobados']
        },
        options: {
            responsive: true,
            legend: {
            	display: true,
            	position: 'right'
            },
            title: {
            	display: true,
            	text: 'Avance de Participantes en '+data.programa+' '+data.now,
            	position: 'top',
            	fontSize: 14
            },
            tooltips: {
	            callbacks: {
	                label: function(tooltipItem, data1) {
	                	var label = data1.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || '0';
	                    return label;
	                }
	            }
	        },
	        pieceLabel: {
	          render: 'percentage',
	          fontColor: ['white', '#000000', 'white']
	        }
        }
    };
    var canvas2 = document.querySelector('#chart2').getContext('2d');
    window.pie2 = new Chart(canvas2, datos2);

    // Gráfico 3
	var datos3 = {
        type: "horizontalBar",
        data: {
        	labels: ['Inactivos', 'No iniciados', 'En curso', 'Aprobados'],
            datasets: [{
            	label: data.week_before,
            	backgroundColor: '#f8cbad',
            	borderColor: '#ed7d31',
            	borderWidth: 1,
                data: [
                	data.reporte['week_before_inactivos'],
                	data.reporte['week_before_no_iniciados'],
                	data.reporte['week_before_en_curso'],
                	data.reporte['week_before_aprobados']
                ]
            }, 
            {
            	label: data.now,
            	backgroundColor: '#bdd7ee',
            	borderColor: '#0070c0',
            	borderWidth: 1,
                data: [
                	data.reporte['now_inactivos'],
                	data.reporte['now_no_iniciados'],
                	data.reporte['now_en_curso'],
                	data.reporte['now_aprobados']
                ]
            }]
        },
        options: {
            responsive: true,
            title: {
            	display: true,
            	text: 'Estatus de los participantes',
            	position: 'top',
            	fontSize: 14
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
    var canvas3 = document.querySelector('#chart3').getContext('2d');
    window.pie3 = new Chart(canvas3, datos3);
   	
   	$('#nowf').val(data.nowf);

}

const renderIntoImage = () => {

	// Función que transforma el gráfico en imagen
	for (var i=1; i<=3; i++)
	{


		const canvas = document.getElementById('chart'+i);
	  	var src_img = getImgFromCanvas(canvas);
	  	
	  	// Se almacena la imagen en el servidor
		$.ajax({
			type: "POST",
			url: $('#url_img').val(),
			async: true,
			data: "bin_data="+src_img+"&chart="+i,
			dataType: "json",
			success: function(response) {
				$('#pdf-loader').hide();
				var href = $("#url_pdf").val()+'/'+$('#empresa_id').val()+'/'+$('#pagina_id').val()+'/'+$('#nowf').val();
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

}