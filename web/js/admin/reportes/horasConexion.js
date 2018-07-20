$(document).ready(function() {

    $('#search').click(function(){
    	$('#label_filtro').hide();
    	$('.dayMaxCon').removeClass('dayMaxCon');
    	$('.hourMaxCon').removeClass('hourMaxCon');
    	$('.descargable').hide();
    	$('.generable').show();
    	$('#resultado').hide();
    	$('#grafico').hide();
    });

    $('#excel').click(function(){
    	$('#excel').hide();
    	$('#excel-loader').show();
    	$.ajax({
			type: "POST",
			url: $('#form').attr('action'),
			async: true,
			data: $("#form").serialize()+'&excel=1&pdf=0',
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

    $("#pdf").click(function(){
    	window.open($('#url_pdf').val()+'/'+$('#empresa_id').val()+'/'+$('#desdef').val()+'/'+$('#hastaf').val()+'/'+$('#graph').val(), '_blank');
    });

});

function mostrarReporte(data)
{
	$('#label_desde').html($('#desde').val());
	$('#label_hasta').html($('#hasta').val());
	$('#label_filtro').show();
	$('#resultado').show();
	
	console.log(data);
	var totales = [];
	var etiquetas = [];
	
	// Mostrar las cantidades
	for (var f = 0; f <= 8; f++)
	{
		for (var c = 0; c <= 25; c++)
		{
			$('#celda_'+f+'_'+c).html(data.conexiones[f][c]);
			if (f > 0 && f != 8 && c == 25)
			{
				totales.push(data.conexiones[f][c]);
			}
		}
		if (f > 0 && f != 8)
		{
			etiquetas.push(data.conexiones[f][0]);
		}
	}

	// Resaltar los mayores
	for (var i = 0; i < data.celda_mayor.length; i++)
	{
		var celda_mayor = data.celda_mayor[i];
		var mayor = celda_mayor.split('_');
		$('#f'+mayor[0]).addClass('dayMaxCon');
		for (var f = 0; f <= 8; f++)
		{
			$('#celda_'+f+'_'+mayor[1]).addClass('hourMaxCon');
		}
		$('#celda_'+mayor[0]+'_25').addClass('hourMaxCon');
	}

	var datos = {
        type: "horizontalBar",
        data: {
            datasets: [{
                label: '',
                data: totales,
                backgroundColor: ["#fd5c63", "#ff9933", "#ed1c24", "#6a67ce", "#ee4c58", "#8aba56", "#a560e8", "#0084DB"],
            }],
            labels: etiquetas
        },
        options: {
            responsive: true,
            legend: {
                display: false
            }
        },
        plugins: [{
		    afterRender: function () {
		      	renderIntoImage();
		    }
		}]
    };
    var canvas = document.querySelector('.barChart').getContext('2d');
    window.horizontalBar = new Chart(canvas, datos);
   	$('#grafico').show();
   	$('#desdef').val(data.desdef);
   	$('#hastaf').val(data.hastaf);

}

const renderIntoImage = () => {
  	const canvas = document.getElementById('myChart');
  	var img = new Image();
  	img.src = canvas.toDataURL("image/png");
  	src_img = img.src;
  	src_img = src_img.replace('data:image/png;base64,', '');
	var data = "bin_data="+src_img;
	$.ajax({
		type: "POST",
		url: $('#url_pdf').val(),
		async: true,
		data: data,
		dataType: "json",
		success: function(response) {
			console.log(response)
		},
		error: function(){
			$('#div-error-server').html($('#error-msg').val());
			notify($('#div-error-server').html());
		}
	});
  	/*console.log(src_slash);
  	var src = src_slash.replace(/\//g, '___');
  	console.log(src);
  	$('#graph').val(src);*/

}