$(document).ready(function() {

	///Variables globales ////////////

	window.table=$('#tablaTutoriales').DataTable( //inicializacion de la tabla que contendra los registros	
		{	paging: true, 
			searching: true, 
			ajax: $('#url_update').val(),
			order: [[ 0, "desc" ]]
		} );

	window.urlsHref=
		{ //href de los input para cargar archivos
		    'pdf_':'/formacion2.0/web/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&field_id=pdf&rootFolder=recursos/tutoriales',
		    'imagen_':'/formacion2.0/web/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&field_id=imagen&rootFolder=recursos/tutoriales',
		    'video_':'/formacion2.0/web/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&field_id=video&rootFolder=recursos/tutoriales'
		};

	
	

	$('#div-active-alert').hide();

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
	});


	$('#guardar').click(function(){
		
		console.log('Tutorial gurdar');
		saveTutorial();
	});

	$('#nuevoTutorial').click(function()
	{
		document.getElementById("form").reset();
		$('#guardar').prop('disabled',false);
		$('#pdf_').attr('href',window.urlsHref['pdf_']);
		$('#imagen_').attr('href',window.urlsHref['imagen_']);
		$('#video_').attr('href',window.urlsHref['video_']);

		
	});

	

	$('#aceptar').click(function()
	{
		var tutorial_id=$('#tutorial_id').val();
		if(tutorial_id!='')//si se edita un tutorial
		{
			window.table.ajax.reload(null,false);//recarga los datos de la tabla manteniendose en la pagina actual
		}
		else
		{
			window.table.ajax.reload(null,true)//recarga los datos de la tabla y la muestra desde la pagina inicial
		}

		
	});

	$('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });


	$( "#BodyTable" ).on( "click",".edit" , function (){
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
				
				$('#pdf_').attr('href',window.urlsHref['pdf_']+'/'+tutorial_id);
				$('#imagen_').attr('href',window.urlsHref['imagen_']+'/'+tutorial_id);
				$('#video_').attr('href',window.urlsHref['video_']+'/'+tutorial_id);
			},
			error: function(){
				$('alert-error').html($('#error_msg_edit').val());
				$('#div-alert').show();
			}
		});
	});


	$( "#BodyTable" ).on( "click",".delete" , function (){
		var tutorial_id = $(this).attr('data');
		sweetAlertDeleteTutorial(tutorial_id, 'AdminTutorial');	
     });
});

function responsive_filemanager_callback(field_id){
	
	// Ruta en el campo de texto
	var url=jQuery('#'+field_id).val();
	var arr = url.split('uploads/');
	var new_image = arr[arr.length-1];
	$('#'+field_id).val(new_image);

	
	
}

function saveTutorial()
{
    console.log('Save tutorial');
	$('#div-alert').hide();
	if ($("#form").valid())
	{
		$('#guardar').prop('disabled', true);
		$.ajax({
			type: "POST",
			url: $('#url_updateTutorial').val(),
			async: true,
			data: $("#form").serialize(),
			dataType: "json",
			success: function(data) {
				$('#p-nombre').html(data.nombre);
				$('#p-pdf').html(data.pdf);
				$('#p-video').html(data.video);
				$( "#detail-edit" ).attr( "data", data.id );
				if (data.delete_disabled != '') 
				{
					$("#detail-delete").hide();
					$("#detail-delete").removeClass( "delete" );
				}
				else
				{
					$( "#detail-delete" ).attr("data",data.id);
					$( "#detail-delete" ).addClass("delete");
					$( "#detail-delete" ).show();
					$('.delete').click(function()
					{
						var tutorial_id= $(this).attr('data');
                        sweetAlertDelete(tutorial_id,'AdminTutorial');
					});
				}
				$('#form').hide();
				$('#alert-success').show();
				$('#detail').show();
				$('#aceptar').show();
				$('#guardar').hide();
				$('#cancelar').hide();
			},
			error: function(){
				$('#alert-error').html($('#error_msg-save').val());
				$('#div-alert').show();
			}
		});
		 
	}

}
