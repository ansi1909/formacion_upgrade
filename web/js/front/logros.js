$(document).ready(function() {
    $('.j-btn-achievement').click(function(e) {
        const programId = this.dataset.id;
        console.log(programId);
        const rankingTitle =  this.dataset.ranktitle;

        if (programId) {
            $('.ranking-loader').addClass('d-flex');
            $.ajax({
                type: "POST",
                url: $('#url_ranking').val(),
                async: true,
                data: { pagina_id: programId },
                dataType: "json",
                success: function(data) {
                    if (data.ok){
                        const keys = Object.keys(data.list);
                        const users = data.list;
                        const userContainer = document.getElementById("userContainer");
                        const sTotal = document.getElementById("totalParticipantes");
                        const leagueName = document.getElementById("leagueName");
                        const puntosProximaLiga = document.getElementById("siguienteLiga");
                        const divLeagues = document.getElementById("categories");
                        const leagues = data.leagues;
                        var divUser;
                        var divLeague;
                        var imgUser;
                        var imgLeague;
                        var pUser;
                        var pUserpoints;
                        var pLeague;
                        while(divLeagues.hasChildNodes()){
                            divLeagues.removeChild(divLeagues.firstChild)
                        }
                        for(const index in leagues){
                            
                            divLeague = document.createElement("div");
                            imgLeague = document.createElement("img");
                            pLeague = document.createElement("p");
                            pLeague.innerHTML = leagues[index].puntos_min+'+';
                            if(leagues[index].puntos_min == 0){
                                pLeague.style.visibility = "hidden";
                            }
                            
                            divLeague.classList.add("ranking_categories__category" ,"px-2" ,"pt-2", "pb-4");
                            if(leagues[index].lograda){
                                imgLeague.src = leagues[index].imagen;
                            }else{
                                imgLeague.src = data.imgLeagueLocked;
                            }
                            if(index == data.currentLeague){
                                divLeague.classList.add("ranking_categories__category--current");
                            }
                            imgLeague.classList.add("rank-badge-number" ,"px-2" ,"pt-2", "mb-2");
                            
                            divLeague.appendChild(imgLeague);
                            divLeague.appendChild(pLeague);
                            divLeagues.appendChild(divLeague);

                        }
                
                        console.log(data.leagues);
                        while(userContainer.hasChildNodes()){
                            userContainer.removeChild(userContainer.firstChild)
                        }
                        if(data.total > 1){
                            sTotal.innerHTML = (data.total - 1);
                        }else{
                            document.getElementById('p-totalParticipantes').style.display='none';
                        }
                        
                        leagueName.innerHTML = data.leagueName;
                        if (data.puntosProximaLiga > 0 ){
                            document.getElementById("puntosProximaLiga").innerHTML =  data.puntosProximaLiga;
                            puntosProximaLiga.style.visibility = "visible";
                        }

                        userContainer.classList.add("ranking_members_list", "d-flex", "justify-content-evenly","flex-wrap")
                        
                        for (const index in users) {
                            console.log(`${index}: ${users[index].nombre}`);
                            divUser = document.createElement("div");
                            imgUser = document.createElement("img");
                            if(users[index].foto){
                                imgUser.src = `${data.uploads}${users[index].foto}`;
                            }else{
                                imgUser.src = `${data.imgUser}`;
                            }

                            
                            pUser = document.createElement("p");
                            pUserpoints =  document.createElement("p");
                            pUser.textContent =`${users[index].nombre} ${users[index].apellido}`;
                            pUser.classList.add("font-bold", "mt-2");
                            pUserpoints.classList.add("mt-2","mb-0");
                            pUserpoints.textContent =`${users[index].puntos} puntos`;
                            divUser.classList.add("ranking_members_list__member","len","my-3","mx-4","d-flex","flex-column","justify-content-end","align-items-center");
                           console.log(index);
                            if(index == data.my_user){
                                divUser.classList.add("my_user");
                            }
                            divUser.appendChild(imgUser);
                            divUser.appendChild(pUserpoints);
                            divUser.appendChild(pUser);
                            userContainer.appendChild(divUser);
                        }

                            $('.ranking-loader').removeClass('d-flex');
                            $('#study_plan').removeClass('show');
                            $('#achievements-container').addClass('show');
                            $("#achievements-container").animate({ scrollTop: 0 }, "fast");

                            if (rankingTitle) {
                                $('.j-ranking-title').text(rankingTitle);
                            }
                    }


                },
                error: function(){
                    console.log('Error en la peticion');

                }
            });
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