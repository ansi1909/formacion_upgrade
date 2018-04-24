$(document).ready(function() {

	$('#fechaPublicacion').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    startDate: '0d',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var startDate = new Date(selected.date.valueOf());
    	$('#fechaVencimiento').datepicker('setStartDate', startDate);
    });

	$('#fechaVencimiento').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    startDate: '0d',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var endDate = new Date(selected.date.valueOf());
    	$('#fechaPublicacion').datepicker('setEndDate', endDate);
	});

});
