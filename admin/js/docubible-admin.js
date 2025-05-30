;(($) => {
  $(document).ready(() => {
    // Inicializar datepickers
    if ($.fn.datepicker) {
      $(".docubible-datepicker").datepicker({
        dateFormat: "yy-mm-dd",
      })
    }

    // Manejar cambios en el selector de período
    $("#docubible-period-selector").on("change", function () {
      const period = $(this).val()

      // Actualizar la URL con el nuevo período
      const url = new URL(window.location.href)
      url.searchParams.set("period", period)
      window.history.pushState({}, "", url)

      // Recargar los datos
      loadStatsData(period)
    })

    // Función para cargar datos de estadísticas
    function loadStatsData(period) {
      const $statsContainer = $("#docubible-stats-container")

      if (!$statsContainer.length) {
        return
      }

      $statsContainer.html('<div class="docubible-loading">Cargando datos...</div>')

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "docubible_load_stats",
          period: period,
          nonce: docubible_admin_params.nonce,
        },
        success: (response) => {
          if (response.success) {
            $statsContainer.html(response.data.html)
          } else {
            $statsContainer.html(
              '<div class="docubible-notice docubible-notice-error">' + response.data.message + "</div>",
            )
          }
        },
        error: () => {
          $statsContainer.html(
            '<div class="docubible-notice docubible-notice-error">Error al cargar los datos. Por favor, inténtalo de nuevo.</div>',
          )
        },
      })
    }

    // Manejar envío del formulario de competición
    $("#docubible-competition-form").on("submit", function (e) {
      e.preventDefault()

      const $form = $(this)
      const $submitButton = $form.find('input[type="submit"]')
      const $responseContainer = $("#docubible-form-response")

      $submitButton.prop("disabled", true)
      $responseContainer.html('<div class="docubible-loading">Guardando datos...</div>')

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: $form.serialize(),
        success: (response) => {
          $submitButton.prop("disabled", false)

          if (response.success) {
            $responseContainer.html(
              '<div class="docubible-notice docubible-notice-success">' + response.data.message + "</div>",
            )

            // Redireccionar después de un tiempo
            setTimeout(() => {
              window.location.href = response.data.redirect
            }, 2000)
          } else {
            $responseContainer.html(
              '<div class="docubible-notice docubible-notice-error">' + response.data.message + "</div>",
            )
          }
        },
        error: () => {
          $submitButton.prop("disabled", false)
          $responseContainer.html(
            '<div class="docubible-notice docubible-notice-error">Error al guardar los datos. Por favor, inténtalo de nuevo.</div>',
          )
        },
      })
    })

    // Manejar eliminación de competición
    $(".docubible-delete-competition").on("click", function (e) {
      e.preventDefault()

      if (!confirm("¿Estás seguro de que deseas eliminar esta competición? Esta acción no se puede deshacer.")) {
        return
      }

      const $button = $(this)
      const competitionId = $button.data("id")

      $button.prop("disabled", true)

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "docubible_delete_competition",
          competition_id: competitionId,
          nonce: docubible_admin_params.nonce,
        },
        success: (response) => {
          if (response.success) {
            // Eliminar fila de la tabla
            $button.closest("tr").fadeOut(400, function () {
              $(this).remove()
            })
          } else {
            alert(response.data.message)
            $button.prop("disabled", false)
          }
        },
        error: () => {
          alert("Error al eliminar la competición. Por favor, inténtalo de nuevo.")
          $button.prop("disabled", false)
        },
      })
    })
  })
})(jQuery)
