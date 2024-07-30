$(document).ready(function() {

	afterPaginate();

	$('.paginate_button').click(function(){
		afterPaginate();
		$('.cardThumb').hide();
	});

});

function afterPaginate(){
	$('.see').click(function(){
		var layout_id = $(this).attr('data');
		$('.cardThumb').hide();
		$('#div-thumbs').show();
		$('#thumbnails-'+layout_id).show();
	});


}