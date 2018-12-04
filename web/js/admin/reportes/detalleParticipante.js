$(document).ready(function() {
      $('#username').prop('readonly',true);
	  $('#empresa_id').change(function(event) 
	  {
           var empresa_id = $(this).val();
           if(empresa_id>0)
           {
           	  $('#hiddenEmpresa').val(empresa_id);
           	  $('#username').prop('readonly',false);
           }
           else
           {
           	 $('#username').prop('readonly',true);
           }
          
	  });

	  $('#search').click(function()
	  {
         var empresa_id = $('#empresa_id').val();
         var username = $('#username').val();

         $.ajax({
		          url: $('#url_infoUsuario').val(),
		          dataType: "json",
		          data:{login:username, empresa_id: empresa_id},
		          type:'GET',
		          beforeSend:function (){
		          $('#user-loader').show();
		          },
		          success:function(data){
                    $('#programasAsignados').html(data.html);
		          	
		          	$('#user-loader').hide();
		          	$('#nombre').text(data.usuario.usuario_nombre+' '+data.usuario.apellido);
		          	$('#login').text(data.usuario.username);
		          	$('#correo').text((data.usuario.correo_personal)? data.usuario.correo_personal:data.usuario.correo_corporativo);
		          	$('#empresa').text(data.usuario.empresa_nombre);
		          	$('#nivel').text(data.usuario.nivel_nombre);
		          	$('#status').text((data.usuario.status)? 'Activo':'Inactivo');
		          	$('#priConex').text(data.usuario.fecha_primer_acceso);
		          	$('#ultConex').text(data.usuario.fecha_ultimo_acceso);
		          	$('#cntdConex').text(data.usuario.cantidad_accesos);
		          	$('#prmConex').text(data.usuario.promedio_conexiones+' (HH:MM:SS)');
		          	$('#proNoinic').text(data.usuario.programas_sin_iniciar);
		          	$('#proEncurs').text(data.usuario.programas_iniciados);
		          	$('#proFinal').text(data.usuario.programas_culminados);
		          	

		          
		         }
		       });
	  });
	  
	  
});

var empresa_id =0;
var lista_usuarios='';
var empresa_id_global=0;