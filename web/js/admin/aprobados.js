$(document).ready(function() {
    document.getElementById("check_todos").checked = true;
    desHabilitarFechas();

    $('.datePicker').datepicker("destroy");
    $('.datePicker').each(function(){
        var input = $(this);
        input.removeClass('datePicker');
        input.addClass('form_datetime');
    });

    $('.form_datetime').datetimepicker({
        language:  'es',
        todayBtn:  true,
        autoclose: true,
        todayHighlight: true,
        showMeridian: true,
        format: 'dd/mm/yyyy HH:ii p',
        endDate: new Date()
    });

    $("#check_todos").change(function(){
        if(document.getElementById("check_todos").checked){
            document.getElementById("check_todos").checked = true;
            desHabilitarFechas();
        }else{
            habilitarFechas();
            document.getElementById("check_todos").checked = false;
        }
    })


});

function desHabilitarFechas(){
    document.getElementById("desde").disabled = true;
    document.getElementById("hasta").disabled = true;
}

function habilitarFechas(){
    document.getElementById("desde").disabled = false;
    document.getElementById("hasta").disabled = false;
}