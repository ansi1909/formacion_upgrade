$(document).ready(function() {

	var root_site = $('#root_site').val();
	var empresa_id = $('#empresa_id').val();
	var subpagina_id = $('#subpagina_id').val();

	CKEDITOR.replace( 'mensaje',
	{
		toolbar : 'MyToolbar'
	});

    /*CKEDITOR.replace( 'mensaje', {
    	toolbar :
		[
			{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
			{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
			{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
			{ name: 'styles', groups: [ 'styles' ] },
			'/',
			{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
			{ name: 'colors', groups: [ 'colors' ] },
			{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
			{ name: 'tools', items : [ 'Maximize','-','About' ] }
		]
	} );*/

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

	$('#cancelar').click(function(){
		$('#section-form').hide(1000);
        $('#section-list').show(1000);
        $('html, body').animate({
		    scrollTop: 0
		},2000);
	});

	$('.newTopic').each(function(){
		observeTopic($(this));
	});

	$(".table-card").paginate({
        perPage: 5,
        autoScroll: false,
        paginatePosition: ['bottom'],
        useHashLocation: true,
        onPageClick: function() {
        	$('html, body').animate({
			    scrollTop: 0
			},2000);
        }
    });

    $( "#search" ).autocomplete({
    	source: $('#url_search').val(),
      	minLength: 3,
      	select: function( event, ui ) {
        	//console.log( "Selected: " + ui.item.value + " AKAA " + ui.item.id );
        	window.location.replace($('#url_detalle').val()+'/'+ui.item.id+'/'+subpagina_id);
      	}
    });

});

function observeTopic(newTopic)
{
	newTopic.click(function(){
		var foro_id = $(this).attr('data');
		$('#foro_id').val(foro_id);
		$('#mensaje_content').val('');
		$('#section-list').hide(1000);
		$('#wait').show(1000);
		if (foro_id != '0')
		{
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
					$('html, body').animate({
					    scrollTop: ($('#section-form').offset().top-100)
					},2000);
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
    $('#cancelar').hide();
    $('#wait').show(1000);
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
            location.reload();
        },
        error: function(){
            console.log('Error guardando el registro de espacio colaborativo'); // Hay que implementar los mensajes de error para el frontend
            $('#publicar').show();
            $('#cancelar').show();
            $('#wait').hide(1000);
        }
    });
}
