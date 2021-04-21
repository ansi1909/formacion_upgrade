$(document).ready(function() {
    $('.j-btn-achievement').click(function(e) {
        const programId = this.dataset.id;
        

        if (programId) {
            $('.ranking-loader').addClass('d-flex');
            setTimeout(() => {
                $('.ranking-loader').removeClass('d-flex');
                $('#achievements-container').addClass('show');
                $('#study_plan').removeClass('show');
            }, 1000);
        }
    });

    $('.j-btn-back-to-plan').click(function(e) {
        $('#achievements-container').removeClass('show');
        $('#study_plan').addClass('show');
    });
});