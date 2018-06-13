$(document).ready(function() {


    $( "#search" ).autocomplete({
    	source: $('#url_search').val(),
      	minLength: 3,
      	select: function( event, ui ) {
        	//console.log( "Selected: " + ui.item.value + " AKAA " + ui.item.id );
        	window.location.replace($('#url_detalle').val()+'/'+ui.item.id);
      	}
    });

});

