$(document).ready(function() {
    $(".table-card").paginate({
        perPage: 10,
        autoScroll: true,
        paginatePosition: ['bottom'],
        useHashLocation: true,
        onPageClick: function() {}
    });
});