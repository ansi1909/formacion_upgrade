$(document).ready(function() {

    $('#empresa_id').change(function(){
        console.log('Usuario admin');
        var empresa_id = $(this).val();
        var app_id = $('#app_id').val();
        getListadoGrupos(empresa_id, app_id);
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

    afterPaginate();

    $('.paginate_button').click(function(){
        afterPaginate();
    });

    $('#aceptar').click(function(){
        window.location.replace($('#url_list').val());
    });

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

    observe();

    $('.edit').click(function(){
        var grupo_id = $(this).attr('data');
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
            data: { grupo_id: grupo_id },
            dataType: "json",
            success: function(data) {
                $('#grupo_id').val(grupo_id);
                $('#nombre').val(data.nombre);
                $('#id_empresa').val(data.empresa_id);
                $('#orden').val(data.orden);
            },
            error: function(){
                $('alert-error').html($('#error_msg_edit').val());
                $('#div-alert').show();
            }
        });
    });

    $('.delete').click(function(){
        var grupo_id = $(this).attr('data');
        sweetAlertDelete(grupo_id, 'CertiGrupo', $('#url_delete_grupos').val());
    });

    clearTimeout( timerId );

});


function getListadoGrupos(empresa_id, app_id){
    $.ajax({
        type: "GET",
        url: $('#url_grupos').val(),
        async: true,
        data: { empresa_id: empresa_id, app_id: app_id},
        dataType: "json",
        success: function(data) {
            $('#lpe').html(data.grupos);
            $('#id_empresa').val(empresa_id);
            $('#new').removeClass("ocultar");
            $('#grupo_id').val(data.grupo_id);
            $('#div-paginas').hide();
            $('#div-grupos').show();
            $('#nombre-p').html(data.empresa);

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

            $('.edit').click(function(){
                var grupo_id = $(this).attr('data');
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
                    data: { grupo_id: grupo_id },
                    dataType: "json",
                    success: function(data) {
                        $('#grupo_id').val(grupo_id);
                        $('#nombre').val(data.nombre);
                        $('#id_empresa').val(data.empresa_id);
                        $('#orden').val(data.orden);
                    },
                    error: function(){
                $('alert-error').html($('#error_msg_edit').val());
                $('#div-alert').show();
                    }
                });
            });

            $('.delete').click(function(){
                var grupo_id = $(this).attr('data');
                sweetAlertDelete(grupo_id, 'CertiGrupo', $('#url_delete_grupos').val());
            });

            afterPaginate();

        },
        error: function(){
            $('#active-error').html($('#error_msg-filter').val());
            $('#div-active-alert').show();
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
    $('.edit').click(function(){
        var grupo_id = $(this).attr('data');
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
            data: { grupo_id: grupo_id },
            dataType: "json",
            success: function(data) {
                $('#grupo_id').val(grupo_id);
                $('#nombre').val(data.nombre);
                $('#id_empresa').val(data.empresa_id);
                $('#orden').val(data.orden);
            },
            error: function(){
        $('alert-error').html($('#error_msg_edit').val());
        $('#div-alert').show();
            }
        });
    });

    $('.cb_activo').click(function(){
        var checked = $(this).is(':checked') ? 1 : 0;
        var id = $(this).attr('id');
        var id_arr = id.split('f');
        var pagina_id = id_arr[1];
        var grupo_id = $('#id_grupo').val();
        $('#div-alert').hide();
        $.ajax({
            type: "POST",
            url: $('#url_asignar').val(),
            async: true,
            data: { id_pagina: pagina_id, id_grupo: grupo_id, entity: 'CertiGrupoPagina', checked: checked },
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

    var table2 = $('#dtSub').DataTable( {
        rowReorder: true,
        responsive: true,
        pageLength:10,
        sPaginationType: "full_numbers",
        oLanguage: {
            "sProcessing":    "Procesando...",
            "sLengthMenu":    "Mostrar _MENU_ registros",
            "sZeroRecords":   "No se encontraron resultados",
            "sEmptyTable":    "Ningún dato disponible en esta tabla",
            "sInfo":          "Mostrando registros del _START_ al _END_ de un total de _TOTAL_.",
            "sInfoEmpty":     "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":  "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":   "",
            "sSearch":        "Buscar:",
            "sUrl":           "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            oPaginate: {
                sFirst: "<<",
                sPrevious: "<",
                sNext: ">", 
                sLast: ">>" 
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }

    } );

    table2.on( 'row-reorder', function ( e, diff, edit ) {
        
        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
            var rowData = table2.row( diff[i].node ).data();
            // Id del registro está en la segunda columna
            id = rowData[1];
            reordenar(id, 'CertiGrupo', diff[i].newData);
        }
 
    });


}

function afterPaginate(){
    $('.see').click(function(){
        var gp_id = $(this).attr('data');
        $('#div-active-alert').hide();
        $.ajax({
            type: "GET",
            url: $('#url_GrupoP').val(),
            async: true,
            data: { gp_id: gp_id },
            dataType: "json",
            success: function(data) {
                $('#paginas').html(data.paginas);
                $('#grupoTitle').html(data.nombre);
                $('#div-paginas').show();
                observe();
            },
            error: function(){
                $('#active-error').html($('#error_msg-subapps').val());
                $('#div-active-alert').show();
                $('#div-paginas').hide();
            }
        });
    });

}