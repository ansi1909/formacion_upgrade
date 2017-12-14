$(document).ready(function() {
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
    });
});