$(document).ready(function() {

    $('#empresa_id').change(function(){
        var empresa_id = $(this).val();
        if (empresa_id != '0')
        {
            $('#lpe').hide();
            $('#div-grupos').show();
            $('#loadGrupos').show();
            $('#nombre-p').html('');
            $('#div-paginas').hide();
            getListadoGrupos(empresa_id);
        }
        else {
            $('#new').addClass("ocultar");
            $('#div-grupos').hide();
            $('#div-paginas').hide();
        }
    });

    $('.new').click(function(){
        var empresa_id =$('#empresa_id').val();
        $('label.error').hide();
        $('#form').show();
        $('#alert-success').hide();
        $('#detail').hide();
        $('#aceptar').hide();
        $('#guardar').show();
        $('#cancelar').show();
        $('#grupo_id').val("");
        $('#nombre').val("");
        $('#id_empresa').val(empresa_id);
        $('#div-alert').hide();
    });

    $('#guardar').click(function(){
        saveGrupo();
    });

    observe();

    $('.paginate_button').click(function(){
        observe();
    });

    $('#aceptar').click(function(){
        window.location.replace($('#url_list').val());
    });

    clearTimeout( timerId );

});


function getListadoGrupos(empresa_id){
    $.ajax({
        type: "GET",
        url: $('#url_grupos').val(),
        async: true,
        data: { empresa_id: empresa_id},
        dataType: "json",
        success: function(data) {
            $('.load1').hide();
            $('#lpe').show();
            $('#lpe').html(data.html);
            $('#new').removeClass("ocultar");
            $('#nombre-p').html(data.empresa);
            observe();
        },
        error: function(){
            $('#active-error').html($('#error_msg-filter').val());
            $('#div-active-alert').show();
            $('.load1').hide();
        }
    });
}

function saveGrupo()
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
                        $('.delete').click(function(){
                            var grupo_id = $(this).attr('data');
                            sweetAlertDelete(grupo_id, 'CertiGrupo', $('#url_delete_grupos').val());
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

function observe()
{

    var table = $('#dt').DataTable( {
        destroy: true,
        rowReorder: true
    } );

    table.on( 'row-reorder', function ( e, diff, edit ) {
        
        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
            var rowData = table.row( diff[i].node ).data();
            // Id del registro está en la segunda columna
            id = rowData[1];
            reordenar(id, 'CertiGrupo', diff[i].newData);
        }
 
    }); 

    $( ".columorden" )
          .mouseover(function() {
            $( '.columorden' ).css( 'cursor','move' );
          })
          .mouseout(function() {
            $( '.columorden' ).css( 'cursor','auto' );
    });

    $('.edit').unbind('click');
    $('.edit').click(function(){
        var grupo_id = $(this).attr('data');
        var url_edit = $('#url_edit').val();
        initModalEdit();
        $.ajax({
            type: "GET",
            url: url_edit,
            async: true,
            data: { grupo_id: grupo_id },
            dataType: "json",
            success: function(data) {
                enableSubmit();
                $('#grupo_id').val(grupo_id);
                $('#nombre').val(data.nombre);
            },
            error: function(){
                $('alert-error').html($('#error_msg_edit').val());
                $('#div-alert').show();
            }
        });
    });

    $('.delete').unbind('click');
    $('.delete').click(function(){
        var grupo_id = $(this).attr('data');
        sweetAlertDelete(grupo_id, 'CertiGrupo', $('#url_delete_grupos').val());
    });

    $('.see').unbind('click');
    $('.see').click(function(){
        var grupo_id = $(this).attr('data');
        $('#div-active-alert').hide();
        $('#paginas').hide();
        $('#grupoTitle').html('');
        $('#div-paginas').show();
        $('#loadPaginas').show();
        $.ajax({
            type: "GET",
            url: $('#url_GrupoP').val(),
            async: true,
            data: { grupo_id: grupo_id },
            dataType: "json",
            success: function(data) {
                $('.load1').hide();
                $('#paginas').show();
                $('#paginas').html(data.html);
                $('#grupoTitle').html(data.nombre);
                observePaginas();
            },
            error: function(){
                $('#active-error').html($('#error_msg-subapps').val());
                $('#div-active-alert').show();
                $('.load1').hide();
            }
        });
    });

}

function observePaginas()
{

    $('.cb_activo').click(function(){
        var checked = $(this).is(':checked') ? 1 : 0;
        var id = $(this).attr('id');
        var id_arr = id.split('f');
        var pagina_id = id_arr[1];
        var grupo_id = $('#grupo_id'+pagina_id).val();
        $('#div-alert').hide();
        $.ajax({
            type: "POST",
            url: $('#url_asignar').val(),
            async: true,
            data: { pagina_id: pagina_id, grupo_id: grupo_id, checked: checked },
            dataType: "json",
            success: function(data) {
                console.log('Activación/Desactivación realizada. Id '+data.id);
            },
            error: function(){
                $('#active-error').html($('#error_msg-active').val());
                $('#div-active-alert').show();
            }
        });
    });

}

function afterPaginate(){
    observe();
}