$(document).ready(function() {

	var table = $('#dt').DataTable( {
		destroy: true,
        rowReorder: true
    } );

    table.on( 'row-reorder', function ( e, diff, edit ) {
        
        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
            var rowData = table.row( diff[i].node ).data();
            // Id del registro está en la segunda columna
        	id = rowData[1];
            reordenar(id, 'CertiPagina', diff[i].newData);
        }
 
    });

    $('#guardar').click(function(){
        $('#form').submit();
        return false;
    });

    $('#form').submit(function(e) {
        e.preventDefault();
    });

    $('#form').safeform({
        submit: function(e) {
            
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
                        $('.form-control').val('');
                        $('.form-control').prop('disabled', true);
                        $('#inserts').html(data.inserts);
                        treePaginas(data.id);
                        initModalShow();

                        // manual complete, reenable form ASAP
                        $('#form').safeform('complete');
                        return false; // revent real submit

                    },
                    error: function(){
                        $('#alert-error').html($('#error_msg-save').val());
                        $('#div-alert').show();
                        $('#guardar').prop('disabled', false);
                        $('#form').safeform('complete');
                        return false; // revent real submit
                    }
                });
            }
            else {
                $('#form').safeform('complete');
                return false; // revent real submit
            }
            
        }
    });

    $('#aceptar').click(function(){
        window.location.replace($('#url_list').val());
    });

    $('.paginate_button').click(function(){
        afterPaginate();
    });

    observe();

    disableSubmit();

});

function treePaginas(pagina_id)
{
    $('#tree_paginas').jstree({
        'core' : {
            'data' : {
                "url" : $('#url_tree').val()+'/'+pagina_id,
                "dataType" : "json"
            }
        }
    });
}

function afterPaginate(){
    observe();
}

function observe()
{

    $('.tree').jstree();

    $('.delete').unbind('click');
    $('.delete').click(function(){
        var pagina_id = $(this).attr('data');
        sweetAlertDelete(pagina_id, 'CertiPagina');
    });

    $('.duplicate').unbind('click');
    $('.duplicate').click(function(){
        var pagina_id = $(this).attr('data');
        $('#pagina_id').val(pagina_id);
        initModalEdit();
        $.ajax({
           type:"GET",
           url: $('#url_edit').val(),
           async: true,
           data: { pagina_id: pagina_id},
           dataType: "json",
           success: function(data){
                enableSubmit();
                $('#nombre').val(data.nombre);
           },
           error: function(){
                $('#alert-error').html($('#error_msg-edit').val());
                $('#div-alert').show();
           }
        });
    });

}