function closeNav() {
    document.getElementById("mainSidenav").classList.toggle("main-sidenav-close");
    document.getElementById("menuArrow").classList.toggle("right");
    document.getElementById("main").classList.toggle("ml-10");
    if (document.getElementById("pills-tabContent")){
        document.getElementById("pills-tabContent").classList.toggle("hidden-nav-content");
    }
    if (!$(".main-sidenav-close")[0] && $(".open-comments")[0]){
        $("#comments").removeClass("open-comments");
        $("#main").removeClass("ml-comments");
    }
    if(!$(".main-sidenav-close")[0] && $(".open-downloads")[0]){
        $(".downloads").removeClass("open-downloads");
        $("#main").removeClass("ml-comments");
    }
}

window.onload=function(){
    //var body = document.getElementsByTagName('body')[0],
    //sidebar = document.getElementById('mainSidenav');

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
    var x = 0, y = 0, z = 0;
    var module = $('.modulo');
    $("#config").on('click',function(e){
        e.preventDefault();
        if(x == 0){
            if(y == 1){
                $(".dropDownNotify").slideUp(50, function(){
                    $(".markNotify").fadeOut(100);
                });
                y = 0;
            }else if(z == 1){
                $(".dropDownApps").slideUp(50);
                $(".markApps").fadeOut(40);
                z = 0;
            }
            $(".dropDownMenu").slideDown(500, function(){
                $(".mark").fadeIn(100).delay(25);
                $(".opcList").show();
            }).delay(100);
            x = 1;
        }else{
            $(".dropDownMenu").slideUp(500, function(){
                $(".mark").fadeOut(600).delay(50);
            });
            x = 0;
        }
    });

    $("#notify").on('click',function(e){
        e.preventDefault();
        if(y == 0){
            if(x == 1){
                $(".dropDownMenu").slideUp(50);
                x = 0;
            }else if(z == 1){
                $(".dropDownApps").slideUp(50);
                $(".markApps").fadeOut(40);
                z = 0;
            }
            $(".dropDownNotify").slideDown(500, function(){
                $(".markNotify").fadeIn(100).delay(25);
                $(".opcListNotify").show();
            });
            y = 1;
        }else {
            $(".dropDownNotify").slideUp(500, function(){
                $(".markNotify").fadeOut(600);
            });
            y = 0;
        }
    });

    $("#apps").on('click',function(e){
        e.preventDefault();
        if(z == 0){
            if(x == 1){
                $(".dropDownMenu").slideUp(50);
                x = 0;
            }else if(y == 1){
                $(".dropDownNotify").slideUp(50, function(){
                    $(".markNotify").fadeOut(100);
                });
                y = 0;
            }
            $(".dropDownApps").slideDown(500, function(){
                $(".markApps").fadeIn(100).delay(25);
                $(".opcApps").show();
            });
            z = 1;
        }else {
            $(".dropDownApps").slideUp(500);
            $(".markApps").fadeOut(550);
            z = 0;
        }
    });

    $("#main, #mainSidenav").on('click', function(){
        if(x == 1){
            $(".dropDownMenu").slideUp(500);
            x = 0;
        }else if(y == 1){
            $(".dropDownNotify").slideUp(500, function(){
                $(".markNotify").fadeOut(300).delay(50);
            });
            y = 0;
        }else if(z == 1){
            $(".dropDownApps").slideUp(500);
            $(".markApps").fadeOut(550);
            z = 0;
        }

    });   
    
    $("#equis").click(function(){
        $("#message").fadeOut("slow");
    });

    $("#arrowDown").click(function(){
        $(".well-message-prog p").toggleClass("up");
        $("#arrowDown").toggleClass("arrow-move");

    });

    $(".iconComments").click(function(){
        event.preventDefault();
        $("#comments").toggleClass("open-comments");
        $('html, body').animate({scrollTop: $('#comments').offset().top -100 }, 'slow');
        $('#ocultarComent').toggle();
        $('#verComent').toggle();
    });
    
    $("#iconDownloads").click(function(){
        $(".downloads").toggleClass("open-downloads");
        $("#main").toggleClass("ml-comments");
        if (!$(".main-sidenav-close")[0]){
            closeNav();
        }
    });
    
    $("#iconCloseDownloads").click(function(){
        $(".downloads").removeClass("open-downloads");
        $("#pills-tabContent").removeClass("hidden-nav-content");
        $("#main").removeClass("ml-10");
        $("#main").removeClass("ml-comments");
        $("#menuArrow").removeClass("right");
        $("#mainSidenav").removeClass("main-sidenav-close");
    });
    
    module.each(function(i, el){
        $(this).on('click',function(){
            module.each(function(){
                $(this).removeClass("active");
            });
            $(this).addClass("active");
        });
    });
    
    $(".bttnDownl").mouseenter(function(){
        $(".tooltipN").slideToggle(300);
    });
    
    $(".bttnDownl").mouseleave(function(){
        $(".tooltipN").slideToggle(300);
    });
    
    $("#btnShowResul").on('click',function(){
        $(".mostrar-resul").slideToggle(300);
    });
    
    document.oncontextmenu = function(){return false;}
    
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
});
