$(document).ready(function() {
    const podio        = $('#podio').val();
    const culmino      = $('#culmino').val();
    var   modalPodio   = $('#modal-ranking-big-notification');

    if (podio > 0 && culmino){
        console.log('Aqui');
        modalPodio.addClass("show");
    }


    $('#closeModal').click(function(){
        console.log('Aqui');
        modalPodio.removeClass("show");
    });
});