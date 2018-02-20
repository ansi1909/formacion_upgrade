$(document).ready(function() {
    
    /*$("#form").validate({
        rules: {
            'title': {
                required: true,
            },
            'descripcion': {
                required: true,
            },
            'lugar': {
                required: true,
            },
            'nivel': {
                required: true,
            },
            'empresa': {
                required: true,
            },
            'start_date': {
                required: true,
            },
            'end_date': {
                required: true,
            }
        },
        messages: {
            'title': {
                required: "- {{ 'El título es requerido.'|trans }}",
            },
            'descripcion': {
                required: "- {{ 'La descripción es requerida.'|trans }}",
            },
            'lugar': {
                required: "- {{ 'El lugar es requerido.'|trans }}",
            },
            'nivel': {
                required: "- {{ 'El nivel es requerido'|trans }}",
            },
            'empresa': {
                required: "- {{ 'La empresa es requerida.'|trans }}",
            },
            'start_date': {
                required: "- {{ 'La fecha de inicio es requerida.'|trans }}",
            },
            'end_date': {
                required: "- {{ 'La fecha de fin es requerida.'|trans }}",
            }
        }
    });

   var date = new Date();
   var yyyy = date.getFullYear().toString();
   var mm = (date.getMonth()+1).toString().length == 1 ? "0"+(date.getMonth()+1).toString() : (date.getMonth()+1).toString();
   var dd  = (date.getDate()).toString().length == 1 ? "0"+(date.getDate()).toString() : (date.getDate()).toString();
   var url_eventos = $("#url_eventos").val();
    $('#calendar').fullCalendar({
        header: {
            language: 'es',
            left: 'prev,next today',
            center: 'title',
            right: 'month,basicWeek,basicDay',

        },
        defaultDate: yyyy+"-"+mm+"-"+dd,
        editable: true,
        eventLimit: true, // allow "more" link when too many events
        selectable: true,
        selectHelper: true,
        select: function(start, end) {
            
            $('.error').hide();
            $('#CreateUpdateTitle').html("{{ 'Crear evento'|trans }}");
            $('#Modal #evento_id').val('');
            $('#Modal #title').val('');
            $('#Modal #descripcion').val('');
            $('#Modal #empresa').val('');
            $('#Modal #nivel').val('');
            $('#Modal #lugar').val('');
            $('#Modal #start').val(moment(start).format('DD-MM-YYYY HH:mm:ss'));
            $('#Modal #end').val(moment(end).format('DD-MM-YYYY HH:mm:ss'));
            $('#save_create').show();
            $('#detail-title').hide();
            $('#detail').hide();
            $('#form').show();
            $('#save_update').hide();
            $('#save_show').hide();
            $('#save_show_update').hide();
            $('#save_error').hide();
            $('#Modal').modal('show');
            $('#guardar_save_create').click(function(){
                $('#save_create').hide();
                saveEvento();
            });
        },
        eventRender: function(event, element) {
            element.bind('click', function() {
                var eve_id = event.id;
                var comienzo = $.fullCalendar.formatDate(event.start, 'DD-MM-YYYY HH:mm:ss');
                var final = event.end ? $.fullCalendar.formatDate(event.end, 'DD-MM-YYYY HH:mm:ss') : '';
                $('#Modal #delete_save_update').attr("data",eve_id);
                $('#CreateUpdateTitle').html("{{ 'Modificar evento'|trans }}");
                $('#Modal #evento_id').val(eve_id);
                $('#Modal #delete').show();
                $('#Modal #title').val(event.title);
                $('#Modal #start').val(comienzo);
                $('#Modal #end').val(final);
                $('#Modal #descripcion').val(event.descripcion);
                $('#Modal #empresa').val(event.empresa_id);
                $('#Modal #nivel').val(event.nivel_id);
                $('#Modal #lugar').val(event.lugar);
                $('#save_create').hide();
                $('#save_update').show();
                $('#detail-title').hide();
                $('#detail').hide();
                $('#form').show();
                $('#save_show').hide();
                $('#save_show_update').hide();
                $('#save_error').hide();
                $('#Modal').modal('show');
                $('#guardar_save_update').click(function(){
                    $('#save_update').hide();
                    saveEvento();
                });
            });
        },
        eventDrop: function(event, delta, revertFunc) { // si changement de position

            edit(event);

        },
        eventResize: function(event,dayDelta,minuteDelta,revertFunc) { // si changement de longueur

            edit(event);

        },
        events: "{{ path('_eventos', { 'userID': usuario.id }) }}"
    });
    
    function edit(event){
        start = event.start.format('DD-MM-YYYY HH:mm:ss');
        if(event.end){
            end = event.end.format('DD-MM-YYYY HH:mm:ss');
        }else{
            end = start;
        }
        
        id =  event.id;
        
        Event = [];
        Event[0] = id;
        Event[1] = start;
        Event[2] = end;
        
        $.ajax({
         url: "url_eventos",
         type: "POST",
         data: {Event:Event},
         success: function(data) {
                if(data.status == 'success'){
                    $('#Modal #delete_save_show').attr("data",data.id);
                    $('#p-titulo').html(data.titulo);
                    $('#p-descripcion').html(data.descripcion);
                    $('#p-lugar').html(data.lugar);
                    $('#p-empresa').html(data.empresa);
                    $('#p-nivel').html(data.nivel);
                    $('#p-fechainicio').html(data.fechainicio);
                    $('#p-fechafin').html(data.fechafin);
                    $('.delete').click(function()
                    {
                        var evento_id= $(this).attr('data');
                        sweetAlertDelete(evento_id, 'AdminEvento');
                    });
                    $('#detail').show();
                    $('#detail-title').show();
                    $('#save_create').hide();
                    $('#save_update').hide();
                    $('#save_show').show();
                    $('#save_show_update').hide();
                    $('#save_error').hide();
                    $('#form').hide();
                    $('#Modal').modal('show');
                    
                    clearTimeout( timerId );
                }else{
                    $('#form').hide();
                    $('#detail-title').hide();
                    $('#detail').hide();
                    $('#title-error').show();
                    $('#detail-error').show();
                    $('#save_create').hide();
                    $('#save_update').hide();
                    $('#save_show').hide();
                    $('#save_error').show();
                    $('#save_show_update').hide();
                    $('#Modal').modal('show'); 
                }
            }
        });
    }

    function saveEvento()
    {
        if ($("#form").valid())
        {
            $.ajax({
                type: "POST",
                url: $('#form').attr('action'),
                async: true,
                data: $("#form").serialize(),
                dataType: "json",
                success: function(data) {
                    $('#Modal #delete_save_show_update').attr("data",data.id);
                    $('#p-titulo').html(data.titulo);
                    $('#p-descripcion').html(data.descripcion);
                    $('#p-lugar').html(data.lugar);
                    $('#p-empresa').html(data.empresa);
                    $('#p-nivel').html(data.nivel);
                    $('#p-fechainicio').html(data.fechainicio);
                    $('#p-fechafin').html(data.fechafin);
                    $('.delete').click(function()
                    {
                        var evento_id= $(this).attr('data');
                        sweetAlertDelete(evento_id, 'AdminEvento');
                    });
                    $('#form').hide();
                    $('#detail-title').show();
                    $('#detail').show();
                    $('#save_create').hide();
                    $('#save_update').hide();
                    $('#save_show').hide();
                    $('#save_error').hide();
                    $('#save_show_update').show();
                    clearTimeout( timerId );
                },
                error: function(){
                    $('#guardar').prop('disabled', false);
                    $('#ModalShowEditError').modal('show');
                    $('#form').hide();
                    $('#detail-title').hide();
                    $('#detail').hide();
                    $('#title-error').show();
                    $('#detail-error').show();
                    $('#save_create').hide();
                    $('#save_update').hide();
                    $('#save_error').show();
                    $('#save_show').hide();
                    $('#save_show_update').hide();
                    $('#Modal').modal('show');
                }
            });
        }else{
            $('#form').show();
            $('#detail-title').hide();
            $('#detail').hide();
            $('#save_create').show();
            $('#save_update').hide();
            $('#save_show').hide();
            $('#save_show_update').hide();
            $('#save_error').hide();
        }
    }

    $('#delete_save_update').click(function(){
        var notificacion_id = $(this).attr('data');
        sweetAlertDelete(notificacion_id, 'AdminEvento');
    });

    $('#delete_save_show').click(function(){
        var notificacion_id = $(this).attr('data');
        sweetAlertDelete(notificacion_id, 'AdminEvento');
    });

    $('#delete_save_show_update').click(function(){
        var notificacion_id = $(this).attr('data');
        sweetAlertDelete(notificacion_id, 'AdminEvento');
    });

    $('#aceptar_save_show_update').click(function(){
        location.reload();
    });

    $('#aceptar_save_error').click(function(){
        location.reload();
    });
    /*$('#start').datepicker({
        startView: 1,
        autoclose: true,
        format: 'dd-mm-yyyy',
        language: 'es'
    });
    $('#end').datepicker({
        startView: 1,
        autoclose: true,
        format: 'dd-mm-yyyy',
        language: 'es'
    });*/
});