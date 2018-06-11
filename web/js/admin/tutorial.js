$(document).ready(function() {

	$('.uploadFileHref').click(function(){
	  $('#fileUpload').val($(this).attr('data-etiqueta'));
	});

	$('.uploadFile').fileupload({

		url: $('#url_uploadFiles_tutorial').val(),
        dataType: 'json',
        done: function (e, data) {
        	$.each(data.result.response.files, function (index, file) 
        	{
        		var id = $('#fileUpload').val();
        		$('#'+id).val(file.name);
            });
        }});

	$('.form-control').focus(function(){
		$('#div-alert').hide();
		$('#div-error').hide();
		$('.form-control').removeClass('error');
	});

	$('.iframe-btn').click(function(){
		$('#div-alert').hide();
		$('#div-error').hide();
		$('.form-control').removeClass('error');
	});

	$('.new').click(function(){
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$('#tutorial_id').val("");
		$('#nombre').val("");
		$('#pdf').val("");
		$('#video').val("");
		$('#imagen').val("");
		$('#descripcion').val("");
		$('#div-alert').hide();
		$('#div-error').hide();
	});


	$('#guardar').click(function(){
		saveTutorial();
	});

	$('#nuevoTutorial').click(function(){
		document.getElementById("form").reset();
		$('#guardar').prop('disabled',false);
	});

	$('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

	$( "#BodyTable, #buttons" ).on( "click",".edit" , function (){
		document.getElementById("form").reset();
		var tutorial_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		$('#guardar').prop('disabled', false);
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$('#div-alert').hide();
		$('#div-error').hide();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { tutorial_id: tutorial_id },
			dataType: "json",
			success: function(data) {
				$('#tutorial_id').val(tutorial_id);
				$('#nombre').val(data.nombre);
				$('#pdf').val(data.pdf);
				$('#video').val(data.video);
				$('#imagen').val(data.imagen);
				$('#descripcion').val(data.descripcion);
			},
			error: function(){
				$('alert-error').html($('#error_msg_edit').val());
				$('#div-alert').show();
			}
		});
	});


	$( "#BodyTable, #buttons" ).on( "click",".delete" , function (){
		var tutorial_id = $(this).attr('data');
		sweetAlertDeleteTutorial(tutorial_id);	
     });
});

function responsive_filemanager_callback(field_id){
	
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);

	
	
}

var table = $('#tablaTutoriales').DataTable( //inicializacion de la tabla que contendra los registros	
{	
	paging: true, 
	searching: true, 
	ajax: $('#url_update').val(),
	order: [[ 0, "desc" ]]
} );



function saveTutorial()
{
	
    $('#div-alert').hide();
	$('#div-error').hide();
	if ($("#form").valid())
	{
		$('#guardar').prop('disabled', true);
		$.ajax({
			type: "POST",
			url: $('#form').attr('action'),
			async: true,
			data: $("#form").serialize(),
			dataType: "json",
			success: function(data) {

				$('#p-nombre').html(data.nombre);
				$('#p-pdf').html(data.pdf);
				$('#p-imagen').html(data.pdf);
				$('#p-video').html(data.video);
				$( "#detail-edit" ).attr( "data", data.id );
				$( "#detail-delete" ).attr("data",data.id);
				$('#form').hide();
				$('#alert-success').show();
				$('#detail').show();
				$('#aceptar').show();
				$('#guardar').hide();
				$('#cancelar').hide();
				if($('#tutorial_id').val() != '')//si se edita un tutorial
				{
					table.ajax.reload(null,false);//recarga los datos de la tabla manteniendose en la pagina actual
				}
				else
				{
					table.ajax.reload(null,true)//recarga los datos de la tabla y la muestra desde la pagina inicial
				}
			},
			error: function(){
				$('#alert-error').html($('#error_msg-save').val());
				$('#div-alert').show();
				$('#guardar').prop('disabled', false);
			}
		}); 
	}
	else {
		$('#div-error').show();
	}

}
