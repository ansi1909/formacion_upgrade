$(document).ready(function() {

	$('.new').click(function(){
		$('#empresa_id').val($(this).attr('data'));
		$('#nombre').val("");
		$('#nivel_id').val("");
		$('#guardar').prop('disabled', false);
        $('label.error').hide();
        $('#form').show();
        $('#alert-success').hide();
        $('#detail').hide();
        $('#aceptar').hide();
        $('#guardar').show();
        $('#cancelar').show();
        $('#div-alert').hide();
	});


	$('#guardar').click(function(){
		saveNivel();
	});


	$('#form').submit(function(e) {
		e.preventDefault();
	  	saveNivel();
	});

	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.delete').click(function(){
		var nivel_id = $(this).attr('data');
		sweetAlertDelete(nivel_id, 'AdminNivel');
	});
    
    $('.edit').click(function(){
        var nivel_id = $(this).attr('data');
        var url_edit = $('#url_edit').val();
        $('#nivel_id').val(nivel_id);
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
           type:"GET",
           url: url_edit,
           async: true,
           data: { nivel_id: nivel_id},
           dataType: "json",
           success: function(data){
               $('#nombre').val(data.nombre);
           },
           error: function(){
               $('#alert-error').html($('#error_msg-edit').val());
               $('#div-alert').show();
           }
        });
    });
});

function saveNivel()
{

	$('#div-alert').hide();
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
				$('#guardar').prop('disabled', false);
				$('#p-nombre').html(data.nombre);
				$('#p-empresa').html(data.empresa);
                console.log('Formulario enviado. Id '+data.id);
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
                    $('.delete').click(function(){
                        var nivel_id = $(this).attr('data');
						sweetAlertDelete(nivel_id, 'AdminNivel');
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
