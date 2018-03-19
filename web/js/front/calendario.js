$(document).ready(function() {
   var url_eventos = $('#url_eventos').val()
   var date = new Date();
   var yyyy = date.getFullYear().toString();
   var mm = (date.getMonth()+1).toString().length == 1 ? "0"+(date.getMonth()+1).toString() : (date.getMonth()+1).toString();
   var dd  = (date.getDate()).toString().length == 1 ? "0"+(date.getDate()).toString() : (date.getDate()).toString();
    
    $('#calendar').fullCalendar({
        header: {
            language: 'es',
            left: 'prev,next today',
            center: 'title',
            right: 'month,basicWeek,basicDay',

        },
        defaultDate: yyyy+"-"+mm+"-"+dd,
        editable: false,
        eventLimit: true, // allow "more" link when too many events
        selectable: false,
        selectHelper: true,
        eventRender: function(event, element) {
            element.bind('click', function() {
                var eve_id = event.id;
                var comienzo = $.fullCalendar.formatDate(event.start, 'DD-MM-YYYY hh:mm a');
                var final = event.end ? $.fullCalendar.formatDate(event.end, 'DD-MM-YYYY hh:mm a') : '';
                $('#Modal #p-titulo').html(event.title);
                $('#Modal #p-fechainicio').html(comienzo);
                $('#Modal #p-fechafin').html(final);
                $('#Modal #p-descripcion').html(event.descripcion);
                $('#Modal #p-lugar').html(event.lugar);
                $('#Modal #p-nivel').html(event.nivel);
                $('#Modal').modal('show');
            });
        },
        events: url_eventos,
        timeFormat: 'hh:mm a'
    });
});