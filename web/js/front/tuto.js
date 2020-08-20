$(document).ready(function () {
  console.log($('#paso_actual_intro').val());
});

function startIntro(introSteps, hasInitialModal = false, isAReview = false) {
  const intro = introJs();
  const currentStep = Number(document.querySelector('#paso_actual_intro').value);
  const stepNeededToPlayIntro = Number(document.querySelector('#paso_actual_intro').dataset.pasocorrecto);
  const cancelTutorial = $('#cancelar_intro').val();

  intro.setOptions({
    doneLabel: '¡Listo!',
    nextLabel: 'Siguiente',
    prevLabel: 'Anterior',
    skipLabel: 'Saltar',
    tooltipClass: 'toolTip',
    showBullets: false,
    exitOnEsc: false,
    exitOnOverlayClick: false,
    showStepNumbers : false,
    steps: introSteps,
    scrollToElement: true,
    tooltipPosition: 'auto',
    positionPrecedence: ['left', 'right', 'top', 'bottom'],
    disableInteraction: isAReview ? true : false
  });

  function ajaxIntroEstado(introStep, introCanceled = false) {
    const dataToSend = {
      nextStep: introStep,
      cancel: introCanceled
    }

    $.ajax({
      type: "POST",
      url: $('#url_cambiar_estado').val(),
      async: true,
      data: {
        nextStep: introStep,
        cancel: introCanceled
      },
      dataType: "json",
      success: function (data) {
        console.log('user_id: ' + data.user_id);
        console.log('test: ' + data.test);
        //clearTimeout( timerId );
      },
      error: function (e) {
        console.error('Error guardando el estado del intro/tutorial');
      }
    });
  }

  function startCountDown(e) {
    killCountDown();
    startCircularCountDown();
  }

  function handleClick(e) {
    const clickedElement = $(e.target);
    const introModal = document.querySelector('.tuto-intro-overlay');
    let nextStep = 0;
    let cancelTutorial = false;

    introModal.style.display = 'none';

    if (clickedElement.is($('#tuto-btn-start'))) {
      nextStep = currentStep + 1;
      setTimeout(() => {
        intro.start();
      }, 600);
    } else {
      nextStep = currentStep + 2;
    }

    if (isAReview) {
      cancelTutorial = true;
      showCountDown();
      intro.onskip(startCountDown);
      if (clickedElement.is('#tuto-btn-skip')) {
        startCountDown();
      }
    }

    ajaxIntroEstado(nextStep, cancelTutorial);
  }

  function manageIntroModal() {

    if (hasInitialModal) {
      $('#tuto-btn-start').click(handleClick);
      $('#tuto-btn-skip').click(handleClick);
      const introModal = document.querySelector('.tuto-intro-overlay');
      introModal.style.display = 'flex';
    } else {
      setTimeout(() => {
        ajaxIntroEstado(currentStep + 1);
        intro.start();
      }, 600);
    }
  }

  if (currentStep == stepNeededToPlayIntro && !cancelTutorial) {
    manageIntroModal();
  } else if (isAReview) {
    if (cancelTutorial) {
      startCountDown();
    } else {
      manageIntroModal();
    }
  }
}