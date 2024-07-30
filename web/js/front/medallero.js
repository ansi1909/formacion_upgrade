$(document).ready(function () {
  $(".j-btn-medals").click(function (e) {
    const programId = this.dataset.id;
    const rankingTitle = this.dataset.ranktitle;

    if (programId) {
      $("#achievements-container").addClass("show");
      $.ajax({
        type: "POST",
        url: $("#url_medallas").val(),
        async: true,
        data: { pagina_id: programId, usuario: $("#usuario_medallas").val() },
        dataType: "json",
        success: function (data) {
          $(".ranking-loader").addClass("d-flex");
          $("#achievements-container").addClass("show");
          $("#study_plan").removeClass("show");
          $(".card-achievement").remove();

          data.list.forEach((element) => {
            $("#achievements-container")
              .insertAfter(`<div class="card-achievement green-line">
              <img src="${
                element.image
              }" alt="" class="card-achievement__badge">
              <div class="card-achievement__details">
                  <h4 class="card-achievement__title">${element.title}</h4>
                  <p class="card-achievement__condition">${
                    element.description
                  }</p>
                  ${
                    element.progress
                      ? `<div class="card-achievement__progress">
                  <div class="card-achievement__progress__bar">
                      <div class="card-achievement__progress__bar__fill fill-orange" style="width: ${element.progress}"></div>
                  </div>
                  <div class="card-achievement__progress__number">
                      ${element.progress_number}
                  </div>
                </div>`
                      : ""
                  }
              </div>
          </div>`);
          });
        },
        error: function () {
          console.log("Error en la peticion");
        },
      });
    }
  });

  $(".j-btn-back-to-plan").click(function (e) {
    $("#achievements-container").removeClass("show");
    $("#study_plan").addClass("show");
  });

  $("#modal-ranking-big-notification .btn_close_modal").click(function (e) {
    $("#modal-ranking-big-notification").removeClass("show");
  });
});
