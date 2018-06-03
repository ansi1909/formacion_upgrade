$(document).ready(function() {
    $(".audioP").mediaelementplayer({
        alwaysShowControls: true,
        features: ['playpause','volume','progress','current','duration'],
        audioVolume: 'vertical',
        audioWidth: "100%",
        audioHeight: 40
    });
});