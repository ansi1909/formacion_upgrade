$(document).ready(function() {

	$('#tbody-empresas tr').each(function(){
		var tr = $(this).attr('id');
		var tr_arr = tr.split('tr-');
		var empresa_id = tr_arr[1];
		treeNiveles(empresa_id);
	});

	$('#div-active-alert').hide();

	$('.new').click(function(){
		$('#empresa_id').val($(this).attr('data'));
		$('#header-empresa').html($(this).attr('empresa'));
		$('#nombre').val("");
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
		sweetAlertDelete(nivel_id);
	});
    
    $('.edit').click(function(){
        var nivel_id = $(this).attr('data');
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
           type:"GET",
           url: url_edit,
           async: true,
           data: { nivel_id: nivel_id},
           dataType: "json",
           success: function(data){
               $('#empresa_id').val(nivel_id);
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
				$( "#cancelar" ).trigger( "click" );
				$('#td-'+data.empresa_id).jstree(true).settings.core.data.url = $('#url_tree').val()+'/'+data.empresa_id;
  				$('#td-'+data.empresa_id).jstree(true).refresh();
                $('#p-nombre').html(data.nombre);
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
                    $('.delete').click(function()
                                       {
                        var categoria_id= $(this).attr('data');
                        sweetAlertDelete(categoria_id);
                    });
                }
                $('#form').hide();
                $('#alert-success').show();
                $('#detail').show();
                $('#aceptar').show();
			},
			error: function(){
				$('#alert-error').html($('#error_msg-save').val());
				$('#div-alert').show();
			}
		});
	}

}

function treeNiveles(empresa_id)
{
	$('#td-'+empresa_id).jstree({
		'core' : {
			'data' : {
				"url" : $('#url_tree').val()+'/'+empresa_id,
				"dataType" : "json"
			}
		}
	});
}
