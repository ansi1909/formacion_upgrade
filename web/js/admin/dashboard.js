$(document).ready(function() {

     $('#empresa_id').change(function(){
        var empresa_id = $(this).val();
        $('.load1').show();
        $('#listado_programas_empresa').hide();
        $.ajax({
            type: "GET",
            url: $('#url_ajaxProgramasDash').val(),
            async: true,
            data: { empresa_id: empresa_id },
            dataType: "json",
            success: function(data) {
                $('.load1').hide();
                $('#listado_programas_empresa').show();
                $('#listado_programas_empresa').html(data);
                $('.data_table').DataTable().destroy();
                applyDataTable();
            },
            error: function(){
                $('#active-error').html($('#error_msg-filter').val());
                $('#div-active-alert').show();
            }
        });
     });

     $('#pais_id').change(function(){
        var pais_id = $(this).val();
        $('#listado_programas_empresa').hide();
        $.ajax({
            type: "GET",
            url: $('#url_ajaxEmpresasDash').val(),
            async: true,
            data: { pais_id: pais_id },
            dataType: "json",
            success: function(data) {

                $('#empresa_id').html(data);
                
            },
            error: function(){
                $('#active-error').html($('#error_msg-filter').val());
                $('#div-active-alert').show();
            }
        });
     });
    
    /*circular progress sidebar home page */   
     $('.progress_profile').circleProgress({ 
         fill: {gradient: ["#2ec7cb", "#6c8bef"]},
         lineCap: 'butt'
     });

     $('.tree').jstree();
    
     /* Sparklines can also take their values from the first argument   passed to the sparkline() function */
    
    $(window).on('load',function(){
        setTimeout(function(){
            var myvalues = [10,8,5,7,4,2,8,10,8,5,6,4,1,7,4,5,8,10,8,5,6,4,4];
            $('.dynamicsparkline').sparkline(myvalues,{ type: 'bar', width: '100px', height: '20', barColor: '#ffffff', barWidth:'2', barSpacing: 2});
        }, 600);
    });
    
});