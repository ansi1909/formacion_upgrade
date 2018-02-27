function closeNav() {
    document.getElementById("mainSidenav").classList.toggle("main-sidenav-close");
    document.getElementById("menuArrow").classList.toggle("right");
    document.getElementById("main").classList.toggle("ml-10");
    if (document.getElementById("pills-tabContent")){
        document.getElementById("pills-tabContent").classList.toggle("hidden-nav-content");
    }
}

window.onload=function(){
    var body = document.getElementsByTagName('body')[0],
    sidebar = document.getElementById('mainSidenav');

    // sidebar.onmouseover = function() {
    //     body.style.overflow = 'hidden';
    //
    // }
    // sidebar.onmouseout = function() {
    //     body.style.overflow = 'auto';
    // }

    // if (document.getElementById('comments')) {
    //
    //     var comments = document.getElementById('comments');
    //     comments.onmouseover = function() {
    //         body.style.overflow = 'hidden';
    //     }
    //     comments.onmouseout = function() {
    //         body.style.overflow = 'auto';
    //     }
    // }

};


$(document).ready(function() {
  // do stuff when DOM is ready
// $("#message").fadeIn("slow");

$("#equis").click(function(){
        $("#message").fadeOut("slow");
    });

$("#arrowDown").click(function(){
        $(".well-message-prog p").toggleClass("up");
        $("#arrowDown").toggleClass("arrow-move");

    });

$("#iconComments").click(function(){
            $("#comments").toggleClass("open-comments");
            $("#main").toggleClass("ml-comments");

        });

});
