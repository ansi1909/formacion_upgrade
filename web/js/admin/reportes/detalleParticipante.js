$(document).ready(function() {

	var empresa_id = $('#empresa_id').val();
	readonlyUsername(empresa_id);

    $('#form').submit(function(e) {
		e.preventDefault();
	});

	$('#search').click(function(){
		var valid = $("#form").valid();
        if (!valid) 
        {
            notify($('#div-error').html());
        }
        else {
        	$('#form').submit();
			return false;
        }
	});

	$('#form').safeform({
		submit: function(e) {
			$('#reporteDetail').show();
			$('#contenidoDetail').hide();
        	$('#loadDetail').show();
        	$('#search').hide();
        	$('#participante').html($('#username').val());
			$.ajax({
				type: "POST",
				url: $('#form').attr('action'),
				async: true,
				data: $("#form").serialize(),
				dataType: "json",
				success: function(data) {
					$('#search').show();
					setDetails(data);
					$('#form').safeform('complete');
					return false;
				},
				error: function(){
					$('#div-error-server').html($('#error-msg').val());
					notify($('#div-error-server').html());
					$('#reporteDetail').hide();
		        	$('#loadDetail').hide();
		        	$('#search').show();
					$('#form').safeform('complete');
                    return false;
				}
			});
		}
	});
      
	$('#empresa_id').change(function(event){
		readonlyUsername($(this).val());
	});

	$('#username').autocomplete({ 
        source: function(request,response){
          	$.ajax({
          		url: $('#url_username').val(),
          		dataType: "json",
          		data: { term:request.term, empresa_id: $('#empresa_id').val() },
          		type:'GET',
          		beforeSend:function (){
          			$('#user-loader').show();
          		},
          		success:function(data){
          			$('#user-loader').hide();
          			response(data);
          		}
          	});
        },
        minLength: 1
    });
	  
});

function readonlyUsername(empresa_id)
{
	if (empresa_id > 0)
   	{
   		$('#username').prop('readonly',false);
   	}
   	else {
   		$('#username').prop('readonly',true);
   	}
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