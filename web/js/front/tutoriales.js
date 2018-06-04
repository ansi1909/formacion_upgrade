$(document).ready(function() 
{

    $('.descargaTutorial').click(function()
    {
    	location.href =  $(this).data('ruta');
      	
    });

    $(".table-card").paginate({
        perPage: 4,
        autoScroll: false,
        paginatePosition: ['bottom'],
        useHashLocation: true,
        onPageClick: function() {
        	$('html, body').animate({
			    scrollTop: 0
			},2000);
        }
    });






});