$(document).ready(function() {

	var root_site = $('#root_site').val();
	var empresa_id = $('#empresa_id').val();

    CKEDITOR.replace( 'mensaje', {
		filebrowserBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/espacio/'+empresa_id,
		filebrowserUploadUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/espacio/'+empresa_id,
		filebrowserImageBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&rootFolder=recursos/espacio/'+empresa_id
	} );

	$('.iframe-btn').fancybox({	
		'width'		: 900,
		'height'	: 900,
		'type'		: 'iframe',
        'autoScale' : false,
		'autoSize'	: false
    });

	$('#fechaPublicacion').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    startDate: '0d',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var startDate = new Date(selected.date.valueOf());
    	$('#fechaVencimiento').datepicker('setStartDate', startDate);
    });

	$('#fechaVencimiento').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    startDate: '0d',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var endDate = new Date(selected.date.valueOf());
    	$('#fechaPublicacion').datepicker('setEndDate', endDate);
	});

	observeTopic();

});

function observeTopic()
{
	$('.newTopic').click(function(){
		var foro_id = $(this).attr('data');
		$('#foro_id').val(foro_id);
		$('#mensaje_content').val('');
		$('#section-list').hide(1000);
		$('#wait').show(1000);
		if (foro_id != '0')
		{
			console.log('Por alguna razon: '+foro_id);
			$.ajax({
		        type: "GET",
		        url: $('#url_edit').val(),
		        async: true,
		        data: { foro_id: foro_id },
		        dataType: "json",
		        success: function(data) {
		            $('#tema').val(data.tema);
		            $('#fechaPublicacion').val(data.fechaPublicacion);
		            $('#fechaVencimiento').val(data.fechaVencimiento);
		            CKEDITOR.instances.mensaje.setData(data.mensaje);
		            var startDate = new Date(data.fechaPublicacionF);
		            startDate.setDate(startDate.getDate() + 1);
		            var endDate = new Date(data.fechaVencimientoF);
		            endDate.setDate(endDate.getDate() + 1);
		            $('#fechaVencimiento').datepicker('setStartDate', startDate);
			    	$('#fechaPublicacion').datepicker('setEndDate', endDate);
			    	$('#section-form').show(1000);
					$('#wait').hide(1000);
		            //clearTimeout( timerId );
		        },
		        error: function(){
		            console.log('Error editando el espacio colaborativo'); // Hay que implementar los mensajes de error para el frontend
		            $('#button-comment').show();
		        }
		    });
		}
		else {
			$('.form-control').val('');
			CKEDITOR.instances.mensaje.setData('');
			$('#section-form').show(1000);
			$('#wait').hide(1000);
		}
	});

}

function saveForo()
{
	$('label.mensaje-error').hide();
    $('#publicar').hide();
    var foro_id = $('#foro_id').val();
    $.ajax({
        type: "POST",
        url: $('#form').attr('action'),
        async: true,
        data: $("#form").serialize(),
        dataType: "json",
        success: function(data) {
            var none = $('#none_foros');
            if (none.length)
            {
                none.remove();
            }
            if (foro_id != '0')
            {
                $( "#liForo-"+foro_id ).html( data.html );
            }
            else {
            	$( "#ul-foros" ).prepend(data.html);
            }
            $('#publicar').show();
            $('#fechaPublicacion').datepicker('setEndDate', null);
            $('#fechaVencimiento').datepicker('setEndDate', null);
            $('#section-form').hide(1000);
            $('#section-list').show(1000);
            observeTopic();
            //clearTimeout( timerId );
        },
        error: function(){
            console.log('Error guardando el registro de espacio colaborativo'); // Hay que implementar los mensajes de error para el frontend
            $('#publicar').show();
        }
    });
}
