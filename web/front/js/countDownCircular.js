$(document).ready(function() {
    var duracion = $('#duracion').val();
    $('.classCountD').ClassyCountdown({
        labelsOptions: {
            lang: {
                hours: 'Hor',
                minutes: 'Min',
                seconds: 'Seg'
            },
            style: 'font-size: .6rem;'
        },
        end: $.now() + parseInt(duracion),
        labels: true,
        style: {
            element: "",
            textResponsive: .5,
            hours: {
                gauge: {
                    thickness: .1,
                    bgColor: "rgba(0,0,0,0.3)",
                    fgColor: "#FFDC60",
                    lineCap: "round"
                },
                textCSS: 'font-family:\'Roboto\'; font-size:25px; font-weight:700; color:#34495e;'
            },
            minutes: {
                gauge: {
                    thickness: .1,
                    bgColor: "rgba(0,0,0,0.3)",
                    fgColor: "#5CAEE6",
                    lineCap: "round"
                },
                textCSS: 'font-family:\'Roboto\'; font-size:25px; font-weight:700; color:#34495e;'
            },
            seconds: {
                gauge: {
                    thickness: .1,
                    bgColor: "rgba(0,0,0,0.3)",
                    fgColor: "#CFE65C",
                    lineCap: "round"

                },
                textCSS: 'font-family:\'Roboto\'; font-size:25px; font-weight:700; color:#34495e;'
            }

        },
        onEndCallback: function() {
            $('.toHide').hide();
            $('#msgTitulo1').hide();
            $('#msgTitulo2').show();
            $('#msgModal1').hide();
            $('#msgModal2').show();
            $( "#triggerModal" ).trigger( "click" );
            setTimeout(function() {
                window.location.replace($('#url_fin').val());
                //console.log('Redireccionamiento');
            }, 6000);
        }
    });
    
    $('.ClassyCountdown-days').hide();

    if (duracion > 3600)
    {
        div = $('#divCountD');
        $('.ClassyCountdown-hours').hide();
    }

});