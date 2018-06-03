$(document).ready(function() {

	$('#finish').click(function(){

        // Se verifica que haya elegido la página
        var pagina_padre_id = $('#pagina_padre_id').val();

        if (pagina_padre_id == '')
        {
            $('#pagina_padre_error').show();
        }
        else {
            $('#pagina_padre_error').hide();
            $('#form').submit();
        }
        
    });

    $('.tree').jstree();

    $('.tree').on("select_node.jstree", function (e, data) {
        var id = data.node.id;
        var pagina_padre_id = $('#'+id).attr('p_id');
        var pagina_padre_str = $('#'+id).attr('p_str');
        $('#pagina_padre_str').val(pagina_padre_str);
        $('#pagina_padre_id').val(pagina_padre_id);
     
    });

});
