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

		          	console.log(data);
		          	
		          	// $('#user-loader').hide();
		          	// $('#nombre').text(data.usuario_nombre+' '+data.apellido);
		          	// $('#login').text(data.username);
		          	// $('#correo').text((data.correo_personal)? data.correo_personal:data.correo_corporativo);
		          	// $('#empresa').text(data.empresa_nombre);
		          	// $('#nivel').text(data.nivel_nombre);
		          	// $('#status').text((data.status)? 'Activo':'Inactivo');
		          	// $('#priConex').text(data.fecha_primer_acceso);
		          	// $('#ultConex').text(data.fecha_ultimo_acceso);
		          	// $('#cntdConex').text(data.cantidad_accesos);
		          	// $('#prmConex').text(data.promedio_conexiones+' (HH:MM:SS)');
		          	// $('#proNoinic').text(data.programas_sin_iniciar);
		          	// $('#proEncurs').text(data.programas_iniciados);
		          	// $('#proFinal').text(data.programas_culminados);

		          
		         }
		       });
	  });
	  
	  
});

var empresa_id =0;
var lista_usuarios='';
var empresa_id_global=0;