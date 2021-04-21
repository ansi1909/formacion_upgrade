$(document).ready(function() {
    $('.j-btn-achievement').click(function(e) {
        const programId = this.dataset.id;
        

        if (programId) {
            $('.ranking-loader').addClass('d-flex');
            setTimeout(() => {
                $('.ranking-loader').removeClass('d-flex');
                $('#study_plan').removeClass('show');
                $('#achievements-container').addClass('show');
                $("#achievements-container").animate({ scrollTop: 0 }, "fast");
            }, 1000);
        }
    });

    $('.j-btn-back-to-plan').click(function(e) {
        $('#achievements-container').removeClass('show');
        $('#study_plan').addClass('show');
    });

    $('#modal-ranking-big-notification .btn_close_modal').click(function(e) {
        $('#modal-ranking-big-notification').removeClass('show');
    });
});