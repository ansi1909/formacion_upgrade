// traemos el dato lo metemos en esta variable
var datoAvance = $('#porcentaje_avance').val();
var porcentaje = datoAvance;
   if (datoAvance > 94 && datoAvance<100){
           datoAvance= datoAvance - 5
       }
datoAvance= Math.round(datoAvance/10)
datoAvance = (datoAvance * 100)
datoAvance = 1000 - datoAvance
var percent =   document.getElementById("percent")

if (datoAvance > 899) {

    document.getElementById("green").setAttribute("d","M191.69043,70.7143555L244.182129,70.7143555L207");
    document.getElementById("green").style.strokeDashoffset = datoAvance;

} else if (datoAvance > 799) {

   document.getElementById("green").setAttribute("d","M191.69043,70.7143555L244.182129,70.7143555L207,146.380101L107");
    document.getElementById("green").style.strokeDashoffset = datoAvance;

} else if (datoAvance > 699) {
   document.getElementById("green").setAttribute("d","M191.69043,70.7143555L244.182129,70.7143555L207,146.380371L367.880859,146.380371L376.774414");
    document.getElementById("green").style.strokeDashoffset = datoAvance;

} else if (datoAvance > 599) {

document.getElementById("green").setAttribute("d","M191.69043,70.7143555L244.182129,70.7143555L207,146.380371L367.880859,146.380371L376.774414,71.2866211L509.732422");
    document.getElementById("green").style.strokeDashoffset = datoAvance;

} else if (datoAvance > 499) {

   document.getElementById("green").setAttribute("d","M191.69043,70.7143555L244.182129,70.7143555L207,146.380371L367.880859,146.380371L376.774414,71.2866211L509.732422,70.7143555L527.570312");
    document.getElementById("green").style.strokeDashoffset = datoAvance;

} else if (datoAvance > 399) {

   document.getElementById("green").setAttribute("d","M191.69043,70.7143555L244.182129,70.7143555L207,146.380371L367.880859,146.380371L376.774414,71.2866211L509.732422,70.7143555L527.570312");
    document.getElementById("green").style.strokeDashoffset = datoAvance;

} else if (datoAvance > 299) {

       document.getElementById("green").setAttribute("d","M191.69043,70.7143555L244.182129,70.7143555L207,146.380371L367.880859,146.380371L376.774414,71.2866211L509.732422,70.7143555L527.570312,146.380371L794.882813");
        document.getElementById("green").style.strokeDashoffset = datoAvance;

} else if (datoAvance > 199) {

       document.getElementById("green").setAttribute("d","M191.69043,70.7143555L244.182129,70.7143555L207,146.380371L367.880859,146.380371L376.774414,71.2866211L509.732422,70.7143555L527.570312,146.380371L794.882813,146.380371L730.555176");
        document.getElementById("green").style.strokeDashoffset = datoAvance;

} else if (datoAvance > 99) {

           document.getElementById("green").setAttribute("d","M191.69043,70.7143555L244.182129,70.7143555L207,146.380371L367.880859,146.380371L376.774414,71.2866211L509.732422,70.7143555L527.570312,146.380371L794.882813,146.380371L730.555176,70L794.882812");
        document.getElementById("green").style.strokeDashoffset = datoAvance;

} else {

   document.getElementById("green").setAttribute("d","M191.69043,70.7143555L244.182129,70.7143555L207,146.380371L367.880859,146.380371L376.774414,71.2866211L509.732422,70.7143555L527.570312,146.380371L794.882813,146.380371L730.555176,70L794.882812,70.7143555");
        document.getElementById("green").style.strokeDashoffset = datoAvance;
           setTimeout(function(){
               document.getElementById("goal").classList.add("goal");
           }, 3000);
}

function contador(){
   let num = document.getElementById('num');
   let counter = 0;
   let segundos= (40 / porcentaje) * 100;
   let id = setInterval(frame, segundos);

   function frame() {
       if( counter >= porcentaje){
           clearInterval(id);
       } else {
           counter += 1;
           num.textContent = counter + '%';
       }
   }
}

contador();
