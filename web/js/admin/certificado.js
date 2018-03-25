$(document).ready(function() {

    var root_site = $('#root_site').val();

    if($('#tipo_certificado_id').val()!=1 )
        buscarEntidad($('#tipo_certificado_id').val());

    if($('#certificado_id').val()!='' && $('#tipo_imagen_certificado_id').val()==2)
        $('.resumen_constancia').show();
    else
        $('.resumen_constancia').hide();

    $('#empresa_id').change(function()
    {
        $('#tipo_certificado_id').val('');
        $('#entidad').val('');
        $('.tipo_entidad').html('');
    });

    $('#tipo_certificado_id').change(function()
    {
        var tipo_certificado_id = $(this).val();
        buscarEntidad(tipo_certificado_id);
    });

    $('#tipo_imagen_certificado_id').change(function()
    {
        if($(this).val()==2)
            $('.resumen_constancia').show();
        else
            $('.resumen_constancia').hide();
    });

    $('.iframe-btn').fancybox({ 
        'width'     : 900,
        'height'    : 900,
        'type'      : 'iframe',
        'autoScale' : false,
        'autoSize'  : false
    });

 });

function buscarEntidad(tipo_certificado_id)
{
    var url_tipo_certificado = $('#url_tipo_certificado').val();
    var empresa_id = $('#empresa_id').val();
    var certificado_id = $('#certificado_id').val();
    
    if(empresa_id!="" )//&& tipo_certificado_id!="")
    {
        $.ajax({
            type: "GET",
            url: url_tipo_certificado,
            dataType: "json",
            data: { tipo_certificado_id: tipo_certificado_id, empresa_id: empresa_id, certificado_id: certificado_id },
            success: function(data){

                $('.tipo_entidad').html(data.html);
            },
            error: function(){
                $('#alert-error').html($('#error_msg-edit').val());
                $('#div-alert').show();
            }
        });
    }
}

function responsive_filemanager_callback(field_id)
{
    // Ruta en el campo de texto
    var url=jQuery('#'+field_id).val();
    var arr = url.split('uploads/');
    var new_image = arr[arr.length-1];
    $('#'+field_id).val(new_image);
    
    if(field_id=="imagen")
        $('#figure').html('<img src="'+url+'" width="100%">');
}