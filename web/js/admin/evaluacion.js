$(document).ready(function() {
	var estatus_activo = 2;
	var prueba_id = $('#prueba_id').val();
	if(!prueba_id){
		$('#form_estatusContenido option[value='+ estatus_activo +']').attr("selected",true);
	}

	$('.timePicker').timepicker({
	    timeFormat: 'H:mm',
	    interval: 15,
	    dynamic: false,
	    dropdown: true,
	    scrollbar: true
	});

	$('.tree').jstree();

	$('.tree').on("select_node.jstree", function (e, data) {
		var id = data.node.id;
		var pagina_id = $('#'+id).attr('p_id');
		var pagina_str = $('#'+id).attr('p_str');
		$('#pagina_str').val(pagina_str);
		$('#pagina_id').val(pagina_id);
	});
    $('#form_paginaPadre').change(function(event){
    	$("#form_categoria option[value='0']").prop("selected",true);
    	$('#form_paginaSeleccionada').html('');
    	$('#form_nombre').val('');
    });
	$('#form_categoria').change(function(event) {
	    $('#form_paginaSeleccionada').html('');
	    $('#form_nombre').val('');
		if($('#form_categoria').val() != 0){
			$('#form_paginaSeleccionada').prop('disabled',true);
		    $('#select-loader').show();
		    $.ajax({
		    	type: "POST",
		    	url: $("#url_paginaSeleccion").val(),
		    	async: true,
		    	data: {pagina_id: $('#form_paginaPadre').val(),categoria_id: $('#form_categoria').val(),pagina_seleccionada_id: $('#pagina_seleccionada').val()} ,
		    	dataType: "json",
		    	success: function(data) {
		    		$('#form_paginaSeleccionada').html('');
		    		$('#form_paginaSeleccionada').html(data.html);
		    		$('#form_paginaSeleccionada').prop('disabled',false);
		    		$('#select-loader').hide();
					},
				error: function(){
						console.log('Error');
					}
				});
		}
    });
    $('#form_paginaSeleccionada').change(function(event){
    	if($('select[name="form[paginaSeleccionada]"] option:selected').val()){
    		$('#form_nombre').val( $('select[name="form[paginaPadre]"] option:selected').data("categoria")+' '+$('select[name="form[paginaPadre]"] option:selected').text() + ' | '+$('select[name="form[categoria]"] option:selected').text() +''+' : '+$('select[name="form[paginaSeleccionada]"] option:selected').text());
    	    $('#pagina_id').val($('select[name="form[paginaSeleccionada]"] option:selected').val());
    	}else{
    		$('#form_nombre').val('');
    		$('#pagina_id').val('');
    	}
    });
});
