$(document).ready(function() {

     $('#empresa_id').change(function(){
        var empresa_id = $(this).val();
            $.ajax({
                type: "POST",
                url: $('#url_ajaxProgramasDash').val(),
                async: true,
                data: { empresa_id: empresa_id },
                dataType: "json",
                success: function(data) {
                    $('#listado_programas_empresa').html(data);
                    
                },
                error: function(){
                    $('#active-error').html($('#error_msg-filter').val());
                    $('#div-active-alert').show();
                }
            });
     });
    
    /*cicular progress sidebar home page */   
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