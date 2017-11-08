$(document).ready(function() {

	$('#dt').DataTable({
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
    });

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


    $("#edit, #list").click(function(){
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

    $('[data-toggle="tooltip"]').tooltip();

    $('.no-check').click(function(event){
        event.preventDefault();
        event.stopPropagation();
        return false;
    });

});
