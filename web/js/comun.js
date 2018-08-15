$(document).ready(function() {

    applyDataTable();

    $(".container_wizard").each(function(){
        var navListItems = $(this).find('div.setup-panel div a'),
            allWells = $(this).find('.setup-content'),
            allNextBtn = $('.nextBtn');
        allWells.hide();
        navListItems.click(function (e) {
            e.preventDefault();
            var $target = $($(this).attr('href')),
                $item = $(this);
            navListItems.removeClass('bttn__fndo').addClass('btn-secondary');
            $item.addClass('bttn__fndo');
            $item.removeClass('btn-secondary');
            $item.removeClass('disabled');
            allWells.hide();
            $target.show();
            $target.find('input:eq(0)').focus();
        });
        allNextBtn.click(function(){
            var curStep = $(this).closest(".setup-content"),
                curStepBtn = curStep.attr("id"),
                nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
                curInputs = curStep.find("input[type='text'],input[type='email']"),
                isValid = true;
            $(".form-group").removeClass("has-danger");
            for(var i=0; i<curInputs.length; i++){
                if (!curInputs[i].validity.valid){
                    isValid = false;
                    $(curInputs[i]).closest(".form-group").addClass("has-danger");
                }
            }
            if (isValid)
                nextStepWizard.removeAttr('disabled').trigger('click');
        });
        $(this).find('.setup-panel div a.bttn__fndo').trigger('click');
    });


    $("#edit, #list, #logout, #next").click(function(){
        var url = $(this).attr('id');
        var parameter = $(this).attr('parameter');
        var p = '';
        if (typeof url === 'undefined')
        {
            url = $(this).attr('data');
        }
        if (typeof parameter !== 'undefined')
        {
            p = '/'+parameter;
        }
        window.location.replace($('#url_'+url).val()+p);
    });

    //$('[data-toggle="tooltip"]').tooltip();

    $('.no-check').click(function(event){
        event.preventDefault();
        event.stopPropagation();
        return false;
    });

    $( ".columorden" )
          .mouseover(function() {
            $( '.columorden' ).css( 'cursor','move' );
          })
          .mouseout(function() {
            $( '.columorden' ).css( 'cursor','auto' );
    });

    $('#qr').click(function(){
        $.ajax({
            type: "POST",
            url: $('#form').attr('action'),
            async: true,
            data: $("#form").serialize(),
            dataType: "json",
            success: function(data) {
                $('#qri').html(data.ruta);
                $('#nombre').val("");
                $('#contenido').val("");
            }
        })
    });

    $('.close, #cancelar').click(function(){
        disableSubmit();
    });

    $( document ).tooltip({
        track: true
    });

});

function initModalShow()
{
    $('#form').hide();
    $('#alert-success').show();
    $('#detail').show();
    $('#aceptar').show();
    $('#guardar').hide();
    $('#cancelar').hide();
}

function initModalEdit()
{
    $('label.error').hide();
    $('#form').show();
    $('#alert-success').hide();
    $('#detail').hide();
    $('#aceptar').hide();
    $('#cancelar').show();
    $('#div-alert').hide();
}

function enableSubmit()
{
    $('#guardar').show();
    $('#guardar').prop('disabled', false);
    //$('.form-control').prop('disabled', false);
    $('#form').safeform('complete');
}

function disableSubmit()
{
    $('#guardar').hide();
    $('#guardar').prop('disabled', true);
    //$('.form-control').prop('disabled', true);
}

// Función que transforma el gráfico en imagen
function getImgFromCanvas(canvas)
{

    var w = canvas.width;
    var h = canvas.height;
    var backgroundColor = '#FFFFFF';
    
    //get the current ImageData for the canvas.
    var context = canvas.getContext("2d");
    var data_img = context.getImageData(0, 0, w, h);
    
    //store the current globalCompositeOperation
    var compositeOperation = context.globalCompositeOperation;

    //set to draw behind current content
    context.globalCompositeOperation = "destination-over";

    //set background color
    context.fillStyle = backgroundColor;

    //draw background / rect on entire canvas
    context.fillRect(0,0,w,h);

    var imageData = canvas.toDataURL("image/png");

    //clear the canvas
    context.clearRect (0,0,w,h);

    //restore it with original / cached ImageData
    context.putImageData(data_img, 0,0);        

    //reset the globalCompositeOperation to what it was
    context.globalCompositeOperation = compositeOperation;

    src_img = imageData.replace('data:image/png;base64,', '');

    return src_img;

}

// Función que resetea el gráfico
function resetCanvas(canvas_id, canvas_class, container)
{
    $('#'+canvas_id).remove();
    $(container).html('<canvas class="'+canvas_class+'" id="'+canvas_id+'"><canvas>');
}