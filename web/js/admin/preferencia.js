$(document).ready(function() {
	
	$('.layout').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f');
		var layout_id = id_arr[1];
		if (checked == 1)
		{
			$('.layout').each(function(){
				if ($(this).attr('id') != id)
				{
					$(this).prop('checked', false);
				}
			});
		}
	});

});