$(document).ready(function() {

	var subpagina_id = $('#subpagina_id').val();

	$('#filter-button').click(function() {
		let filter = $('#search').val();
		filter = filter.trim()
		if(filter.length >= 3){
			window.location = $('#url_index').val()+'/'+filter;
		}
	});


    $('#filter-means').click(function(){
		$('#search').val('');
		 window.location = $('#url_index').val();
	});

    $( "#search" ).autocomplete({
		source: function(request, response){
			$.ajax({
				url: $('#url_search').val(),
				dataType: "json",
				data: { term: request.term },
				success: function( data ){
					console.log( data)
					response(data)
				}
			});
		},
		minLength: 3
    });

});

