$(document).ready(function() {
    
    /*cicular progress sidebar home page */   
     $('.progress_profile').circleProgress({ 
         fill: {gradient: ["#2ec7cb", "#6c8bef"]},
         lineCap: 'butt'
     });
    
     /* Sparklines can also take their values from the first argument   passed to the sparkline() function */
    var myvalues = [10,8,5,7,4,2,8,10,8,5,6,4,1,7,4,5,8,10,8,5,6,4,4];
    $('.dynamicsparkline').sparkline(myvalues,{ type: 'bar', width: '100px', height: '20', barColor: '#ffffff', barWidth:'2', barSpacing: 2});
    
    var myvalues2 = [10,8,5,7,4,2,8,10,8,5,6,4,1,7,4,5,8,10,8,5,6,4,4,1,7,4,5,8,10,8,5,6,4,4];
    $('.dynamicsparkline2').sparkline(myvalues2,{ type: 'bar', width: '200px', height: '60', barColor: '#ffffff', barWidth:'3', barSpacing: 3});
    
    $(function(){
        Morris.Bar({
            element: 'morris-bar-chart',
            data: [
                { y: 'Lun', a: 5, b: 10 },
                { y: 'Mar', a: 12,  b: 15 },
                { y: 'Mie', a: 3,  b: 9 },
                { y: 'Jue', a: 10,  b: 4 },
                { y: 'Vie', a: 5,  b: 7 },
                { y: 'Sab', a: 2,  b: 4 },
                { y: 'Dom', a: 10, b: 16 }
            ],
            resize: true,
            xkey: 'y',
            ykeys: ['a', 'b'],
            labels: ['Promedio de conexion por participante', 'Total de horas de conexion diarias'],
            barColors: ["#03A9F4", "#8BC34A", "#FFC107", "#F44336"]
        });
        
        Morris.Bar({
            element: 'morris-bar-chart2',
            data: [
                { y: 'Lun', a: 5, b: 10 },
                { y: 'Mar', a: 12,  b: 15 },
                { y: 'Mie', a: 3,  b: 9 },
                { y: 'Jue', a: 10,  b: 4 },
                { y: 'Vie', a: 5,  b: 7 },
                { y: 'Sab', a: 2,  b: 4 },
                { y: 'Dom', a: 10, b: 16 }
            ],
            resize: true,
            xkey: 'y',
            ykeys: ['a', 'b'],
            labels: ['Promedio de conexion por participante', 'Total de horas de conexion diarias'],
            barColors: ["#03A9F4", "#8BC34A", "#FFC107", "#F44336"]
        });
    });
});