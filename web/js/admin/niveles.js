$(document).ready(function() {

    $('#fechaInicio').datepicker({
      startView: 1,
      autoclose: true,
      format: 'dd/mm/yyyy',
      language: 'es',
      startDate: '0d',
      clearBtn: true
  })
  .on( "changeDate", function(selected) {
    var startDate = new Date(selected.date.valueOf());
      $('#fechaFin').datepicker('setStartDate', startDate);
    });
    
     $('#fechaFin').datepicker({
      startView: 1,
      autoclose: true,
      format: 'dd/mm/yyyy',
      language: 'es',
      startDate: '0d',
      clearBtn: true
  })
  .on( "changeDate", function(selected) {
    var endDate = new Date(selected.date.valueOf());
      $('#fechaInicio').datepicker('setEndDate', endDate);
    });
    $('#nuevo-nivel').click(function(event) {
       $('#nombre').val('');
       $('#fechaInicio').val('');
       $('#fechaFin').val('');
       $('#nombre').removeClass('error');
       $('#fechaInicio').removeClass('error');
       $('#fechaFin').removeClass('error');
    });

    

	$('.new').click(function(){
        initModalEdit();
        enableSubmit();
		$('#nombre').val("");
		$('#nivel_id').val("");
	});

 $('#guardar').click(function(e){
      var valid = ($('#fechaFin').val()=='' && $('#fechaInicio').val()=='') || ($('#fechaFin').val()!='' && $('#fechaInicio').val()!='') ? true:false;
      if (valid) {
        $('#form').submit();
        return false;
      }else{
        var label = $('#fechaInicio').val()==''? $('#fechaInicio').data('label'):$('#fechaFin').data('label');
        $('#'+label).show();
        setTimeout(function(){ 
            $('#'+label).hide();
        }, 3000);
        $('#form').safeform('complete');
        return false; // revent real submit
      }
    });

    $('#form').submit(function(e) {
        e.preventDefault();
    });

	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.delete').click(function(){
		var nivel_id = $(this).attr('data');
		sweetAlertDelete(nivel_id, 'AdminNivel');
	});
    
    $('.edit').click(function(){
        var nivel_id = $(this).attr('data');
        var url_edit = $('#url_edit').val();
        $('#nivel_id').val(nivel_id);
        initModalEdit();
        $.ajax({
           type:"GET",
           url: url_edit,
           async: true,
           data: { nivel_id: nivel_id },
           dataType: "json",
           success: function(data){
                enableSubmit();
               $('#nombre').val(data.nombre);
               $('#fechaInicio').val(data.fechaInicio);
               $('#fechaFin').val(data.fechaFin);
           },
           error: function(){
               $('#alert-error').html($('#error_msg-edit').val());
               $('#div-alert').show();
           }
        });
    });

    $('#form').safeform({
        submit: function(e) {
            
            $('#div-alert').hide();
            if ($("#form").valid())
            {
                $('#guardar').prop('disabled', true);
                $.ajax({
                    type: "POST",
                    url: $('#form').attr('action'),
                    async: true,
                    data: $("#form").serialize(),
                    dataType: "json",
                    success: function(data) {
                        $('.form-control').val('');
                        $('#p-nombre').html(data.nombre);
                        $('#p-empresa').html(data.empresa);
                        console.log('Formulario enviado. Id '+data.id);
                        $( "#detail-edit" ).attr( "data", data.id );
                        if (data.delete_disabled != '') 
                        {
                            $("#detail-delete").hide();
                            $("#detail-delete").removeClass( "delete" );
                        }
                        else
                        {
                            $( "#detail-delete" ).attr("data",data.id);
                            $( "#detail-delete" ).addClass("delete");
                            $( "#detail-delete" ).show();
                            $('.delete').unbind('click');
                            $('.delete').click(function(){
                                var nivel_id = $(this).attr('data');
                                sweetAlertDelete(nivel_id, 'AdminNivel');
                            });
                        }
                        
                        initModalShow();

                        // manual complete, reenable form ASAP
                        $('#form').safeform('complete');
                        return false; // revent real submit
                                
                    },
                    error: function(){
                        $('#alert-error').html($('#error_msg-save').val());
                        $('#div-alert').show();
                        $('#guardar').prop('disabled', false);
                        $('#form').safeform('complete');
                        return false; // revent real submit
                    }
                });
            }
            else {
                $('#form').safeform('complete');
                return false; // revent real submit
            }
            
        }
    });
    $("#fechaInicio,#fechaFin").click(function(e) {
      e.preventDefault();
      $(this).val('');
      $(this).datepicker("show");
    });

    disableSubmit();

});
