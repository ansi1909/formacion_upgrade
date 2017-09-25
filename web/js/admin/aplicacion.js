$(document).ready(function() {

	$('#guardar').click(function(){
		console.log('Cahoooo');
		$("#form").valid();
	});

	$("#form").validate({
		/*errorPlacement: function(error, element) { 
            if (element.attr("name") == "tipoOrganizacion" ){
            	error.insertAfter("#error-tipo");
            }
		    else {
		    	error.insertAfter(element);
		    }
        },*/
		rules: {
			'nombre': {
				required: true,
				minlength: 3
			}/*,
			'rif': {
				required: true,
				minlength: 10,
				maxlength: 12
			},
			'tipoOrganizacion': {
				required: true
			},
			'countrycode': {
				required: true
			},
			'city': {
				required: true
			},
			'login': {
				required: usuario_id == '' ? true : false
			},
			'clave': {
				required: usuario_id == '' ? true : false
			},
			'clave2': {
				required: usuario_id == '' ? true : false,
				equalTo: "#clave"
			},
			'cedula': {
				required: true,
				digits: true
			},
			'nombre': {
				required: true
			},
			'apellido': {
				required: true
			},
			'telefono1': {
				required: true,
				minlength: 10,
				maxlength: 15
			},
			'telefono2': {
				minlength: 10,
				maxlength: 15
			},
			'correo': {
				required: true,
				email: true
			}*/
		},
		messages: {
			'nombre': {
				required: "El nombre de la organización es requerido.",
				minlength: "El nombre de la organización debe ser mínimo de 3 caracteres."
			}/*,
			'rif': {
				required: "El RIF es requerido.",
				minlength: "El RIF debe contener entre 10 y 12 caracteres.",
				maxlength: "El RIF debe contener entre 10 y 12 caracteres."
			},
			'tipoOrganizacion': {
				required: "El tipo de cliente es requerido."
			},
			'countrycode': {
				required: "El país es requerido."
			},
			'city': {
				required: "La ciudad es requerida."
			},
			'cedula': {
				required: "La cédula es requerida.",
				digits: "La cédula debe ser sólo dígitos"
			},
			'nombre': {
				required: "El nombre es requerido."
			},
			'apellido': {
				required: "El apellido es requerido."
			},
			'telefono1': {
				required: "El celular es requerido.",
				minlength: "El celular debe contener entre 10 y 12 caracteres.",
				maxlength: "El celular debe contener entre 10 y 12 caracteres."
			},
			'telefono2': {
				minlength: "El teléfono debe contener entre 10 y 12 caracteres.",
				maxlength: "El teléfono debe contener entre 10 y 12 caracteres."
			},
			'correo': {
				required: "El correo es requerido.",
				email: "Formato de correo inválido."
			}*/
		},
		submitHandler: function(form, event) {

			var error = 0;
			event.preventDefault();
			$('#btn').hide();
			
			if (usuario_id == '')
			{
				var login = $('#login').val();
				$.ajax({
					type: "GET",
					url: url_validUser,
					async: false,
					data: { login: login },
					dataType: "json",
					success: function(data) {
						if (data.ok != 1)
						{
							error = 1;
							$('#usuario-error').html('Usuario existente');
							$('#usuario-error').show();
							$('#btn').show();
						}
					},
					error: function(){
						error = 1;
						$('#usuario-error').html('Ha ocurrido un error validando el usuario. Contacte con el Administrador.');
						$('#usuario-error').show();
						$('#btn').show();
					}
				});
			}
			
			if (error == 0)
			{
				form.submit();
			}
			
		}
	});

});
