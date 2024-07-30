$(document).ready(function() {
    var clock;
    var timing = 60 * 30;
    // Instantiate a counter
    clock = new FlipClock($('.clock'), timing, {
        clockFace: 'MinuteCounter',
        autoStart: true,
        countdown: true
    })
});