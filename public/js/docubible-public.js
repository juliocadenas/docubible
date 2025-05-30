;((window, $) => {
  // Verificar que jQuery está disponible
  if (typeof $ === "undefined") {
    console.error("DocuBible: jQuery no está disponible")
    return
  }

  // Verificar que docubible_params está definido
  if (typeof window.docubible_params === "undefined") {
    console.error("DocuBible: docubible_params no está definido")
    window.docubible_params = {
      ajax_url: "",
      nonce: "",
      i18n: {
        select_option: "Por favor, selecciona una opción.",
        loading: "Cargando...",
        correct: "¡Correcto!",
        incorrect: "Incorrecto. Inténtalo de nuevo.",
        error: "Ha ocurrido un error. Por favor, inténtalo de nuevo.",
        time_up: "¡Tiempo agotado!",
      },
    }
  }

  // Objeto principal
  var DocuBible = {
    // Inicializar
    init: function () {
      this.bindEvents()
      this.initTimers()
    },

    // Vincular eventos
    bindEvents: function () {
      // Usar delegación de eventos para manejar elementos cargados dinámicamente
      $(document).on("click", ".docubible-check-answer", this.handleCheckAnswer)
      $(document).on("click", ".docubible-show-answer", this.handleShowAnswer)
      $(document).on("click", ".docubible-next-question", this.handleNextQuestion)
    },

    // Manejar clic en botón de evaluar respuesta
    handleCheckAnswer: function (e) {
      e.preventDefault()

      var $button = $(this)
      var $trivia = $button.closest(".docubible-trivia")
      var questionId = $trivia.data("question-id")
      var selectedOption = $trivia.find('input[name="docubible_answer"]:checked').val()

      // Verificar que se ha seleccionado una opción
      if (!selectedOption) {
        $trivia
          .find(".docubible-result")
          .html('<div class="docubible-error">' + window.docubible_params.i18n.select_option + "</div>")
        return
      }

      // Mostrar estado de carga
      $trivia
        .find(".docubible-result")
        .html('<div class="docubible-loading">' + window.docubible_params.i18n.loading + "</div>")

      // Detener temporizador
      DocuBible.stopTimer($trivia)

      // Deshabilitar botones durante la petición
      $trivia.find(".docubible-button").prop("disabled", true)

      // Realizar petición AJAX
      $.ajax({
        url: window.docubible_params.ajax_url,
        type: "POST",
        data: {
          action: "docubible_check_answer",
          question_id: questionId,
          answer: selectedOption,
          nonce: window.docubible_params.nonce,
        },
        success: (response) => {
          // Habilitar botones
          $trivia.find(".docubible-button").prop("disabled", false)

          if (response.success) {
            if (response.data.is_correct) {
              // Respuesta correcta
              $trivia
                .find(".docubible-result")
                .html('<div class="docubible-correct">' + window.docubible_params.i18n.correct + "</div>")

              // Mostrar referencia
              $trivia.find(".docubible-verse-reference").show()

              // Deshabilitar botones de respuesta
              $trivia.find(".docubible-check-answer, .docubible-show-answer").prop("disabled", true)

              // Resaltar opción correcta
              $trivia
                .find('input[value="' + selectedOption + '"]')
                .closest(".docubible-option")
                .addClass("docubible-correct-option")
            } else {
              // Respuesta incorrecta
              $trivia
                .find(".docubible-result")
                .html('<div class="docubible-incorrect">' + window.docubible_params.i18n.incorrect + "</div>")

              // Resaltar opción incorrecta
              $trivia
                .find('input[value="' + selectedOption + '"]')
                .closest(".docubible-option")
                .addClass("docubible-incorrect-option")
            }
          } else {
            // Error en la respuesta
            $trivia.find(".docubible-result").html('<div class="docubible-error">' + response.data.message + "</div>")
          }
        },
        error: (xhr, status, error) => {
          // Error en la petición
          console.error("DocuBible AJAX Error:", status, error)

          // Habilitar botones
          $trivia.find(".docubible-button").prop("disabled", false)

          // Mostrar mensaje de error
          $trivia
            .find(".docubible-result")
            .html('<div class="docubible-error">' + window.docubible_params.i18n.error + "</div>")
        },
        timeout: 15000, // 15 segundos de timeout
      })
    },

    // Manejar clic en botón de ver respuesta
    handleShowAnswer: function (e) {
      e.preventDefault()

      var $button = $(this)
      var $trivia = $button.closest(".docubible-trivia")
      var questionId = $trivia.data("question-id")

      // Mostrar estado de carga
      $trivia
        .find(".docubible-result")
        .html('<div class="docubible-loading">' + window.docubible_params.i18n.loading + "</div>")

      // Detener temporizador
      DocuBible.stopTimer($trivia)

      // Deshabilitar botones durante la petición
      $trivia.find(".docubible-button").prop("disabled", true)

      // Realizar petición AJAX
      $.ajax({
        url: window.docubible_params.ajax_url,
        type: "POST",
        data: {
          action: "docubible_show_answer",
          question_id: questionId,
          nonce: window.docubible_params.nonce,
        },
        success: (response) => {
          // Habilitar botón siguiente
          $trivia.find(".docubible-next-question").prop("disabled", false)

          if (response.success) {
            // Mostrar respuesta correcta
            $trivia
              .find(".docubible-result")
              .html('<div class="docubible-answer">' + response.data.correct_answer + "</div>")

            // Mostrar referencia y versículo completo
            $trivia.find(".docubible-verse-reference").show()
            if ($trivia.find(".docubible-verse-full").length) {
              $trivia.find(".docubible-verse-full").show()
            }

            // Resaltar opción correcta
            $trivia
              .find('input[value="' + response.data.correct_option + '"]')
              .closest(".docubible-option")
              .addClass("docubible-correct-option")

            // Mantener deshabilitados los botones de respuesta
            $trivia.find(".docubible-check-answer, .docubible-show-answer").prop("disabled", true)
          } else {
            // Error en la respuesta
            $trivia.find(".docubible-result").html('<div class="docubible-error">' + response.data.message + "</div>")

            // Habilitar todos los botones en caso de error
            $trivia.find(".docubible-button").prop("disabled", false)
          }
        },
        error: (xhr, status, error) => {
          // Error en la petición
          console.error("DocuBible AJAX Error:", status, error)

          // Habilitar todos los botones en caso de error
          $trivia.find(".docubible-button").prop("disabled", false)

          // Mostrar mensaje de error
          $trivia
            .find(".docubible-result")
            .html('<div class="docubible-error">' + window.docubible_params.i18n.error + "</div>")
        },
        timeout: 15000, // 15 segundos de timeout
      })
    },

    // Manejar clic en botón de siguiente pregunta
    handleNextQuestion: function (e) {
      e.preventDefault()

      var $button = $(this)
      var $trivia = $button.closest(".docubible-trivia")
      var triviaType = ""

      // Determinar el tipo de trivia basado en las clases CSS
      if ($trivia.hasClass("docubible-complete-verse")) {
        triviaType = "complete_verse"
      } else if ($trivia.hasClass("docubible-fill-blanks")) {
        triviaType = "fill_blanks"
      } else if ($trivia.hasClass("docubible-identify-book")) {
        triviaType = "identify_book"
      } else {
        // Fallback para compatibilidad con versiones anteriores
        triviaType = "complete_verse"
      }

      // Mostrar overlay de carga
      DocuBible.showLoadingOverlay($trivia)

      // Detener temporizador
      DocuBible.stopTimer($trivia)

      // Deshabilitar todos los botones durante la carga
      $trivia.find(".docubible-button").prop("disabled", true)

      // Realizar petición AJAX
      $.ajax({
        url: window.docubible_params.ajax_url,
        type: "POST",
        data: {
          action: "docubible_next_question",
          trivia_type: triviaType,
          nonce: window.docubible_params.nonce,
        },
        success: (response) => {
          if (response.success) {
            // Reemplazar trivia con la nueva
            $trivia.replaceWith(response.data.html)

            // Iniciar temporizador para la nueva pregunta
            DocuBible.initTimers()
          } else {
            // Ocultar overlay de carga
            DocuBible.hideLoadingOverlay($trivia)

            // Mostrar mensaje de error
            $trivia
              .find(".docubible-result")
              .html('<div class="docubible-notice docubible-notice-error">' + response.data.message + "</div>")

            // Habilitar botones en caso de error
            $trivia.find(".docubible-button").prop("disabled", false)
          }
        },
        error: (xhr, status, error) => {
          // Error en la petición
          console.error("DocuBible AJAX Error:", status, error)

          // Ocultar overlay de carga
          DocuBible.hideLoadingOverlay($trivia)

          // Mostrar mensaje de error
          $trivia
            .find(".docubible-result")
            .html('<div class="docubible-error">' + window.docubible_params.i18n.error + "</div>")

          // Habilitar botones en caso de error
          $trivia.find(".docubible-button").prop("disabled", false)
        },
        timeout: 15000, // 15 segundos de timeout
      })
    },

    // Inicializar temporizadores
    initTimers: () => {
      $(".docubible-timer-value").each(function () {
        var $timer = $(this)
        var $trivia = $timer.closest(".docubible-trivia")
        var initialTime = Number.parseInt($timer.data("time"), 10)

        if (initialTime <= 0) {
          return
        }

        // Limpiar intervalo existente si hay alguno
        if ($timer.data("interval")) {
          clearInterval($timer.data("interval"))
        }

        // Establecer nuevo intervalo
        $timer.data(
          "interval",
          setInterval(() => {
            var timeLeft = Number.parseInt($timer.text(), 10)

            if (timeLeft <= 0) {
              DocuBible.stopTimer($trivia)
              DocuBible.timeUp($trivia)
              return
            }

            timeLeft--
            $timer.text(timeLeft)

            // Cambiar color según el tiempo restante
            if (timeLeft <= 5) {
              $timer
                .closest(".docubible-timer")
                .removeClass("docubible-timer-warning")
                .addClass("docubible-timer-danger")
            } else if (timeLeft <= 10) {
              $timer.closest(".docubible-timer").addClass("docubible-timer-warning")
            }
          }, 1000),
        )
      })
    },

    // Detener temporizador
    stopTimer: ($trivia) => {
      var $timer = $trivia.find(".docubible-timer-value")

      if ($timer.length && $timer.data("interval")) {
        clearInterval($timer.data("interval"))
      }
    },

    // Manejar tiempo agotado
    timeUp: ($trivia) => {
      $trivia
        .find(".docubible-result")
        .html('<div class="docubible-error">' + window.docubible_params.i18n.time_up + "</div>")

      $trivia.find(".docubible-check-answer").prop("disabled", true)
      $trivia.find(".docubible-show-answer").trigger("click")
    },

    // Mostrar overlay de carga
    showLoadingOverlay: ($trivia) => {
      // Eliminar overlay existente si hay alguno
      $trivia.find(".docubible-loading-overlay").remove()

      // Crear y añadir nuevo overlay
      var $overlay = $(
        '<div class="docubible-loading-overlay">' +
          '<div class="docubible-spinner"></div>' +
          window.docubible_params.i18n.loading +
          "</div>",
      )

      $trivia.append($overlay)
    },

    // Ocultar overlay de carga
    hideLoadingOverlay: ($trivia) => {
      $trivia.find(".docubible-loading-overlay").remove()
    },
  }

  // Inicializar cuando el documento esté listo
  $(document).ready(() => {
    DocuBible.init()
  })
})(window, jQuery)
