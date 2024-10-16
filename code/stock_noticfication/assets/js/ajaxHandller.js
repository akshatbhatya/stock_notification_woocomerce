function handleAjaxResponse(method, data) {
  let urlIs = notifier_ajax_call.ajax_url;

  jQuery.ajax({
    type: method,
    url: `${urlIs}`,
    data: data,
    processData: false,
    contentType: false,
    success: function (res) {
      jQuery(".loader").remove();
      jQuery(".custom-loader").remove();

      Swal.fire({
        title: "Success",
        text: res.data,
        icon: "success",
      }).then(() => location.reload());
    },
    error: function (err) {
      jQuery(".loader").remove();
      jQuery(".custom-loader").remove();

      const message = err.responseJSON
        ? err.responseJSON.data
        : "An unexpected error occurred.";
      Swal.fire({
        title: "Error",
        text: message,
        icon: "error",
      }).then(() => location.reload());
    },
  });
}

(function ($) {
  $(document).ready(function () {
    $("#notifyMeForm").submit(function (e) {
      e.preventDefault();
      $(".loader").remove();
      $(".custom-loader").remove();

      $("body").append(
        '<div class="custom-loader"><div class="loader"></div></div>'
      );
      const data = new FormData(this);
      data.append("action", "send_notification_email");

      if ($(".NotificationEmail").val() == "") {
        $(".loader").remove();
        $(".custom-loader").remove();
      } else {
        handleAjaxResponse("POST", data);
      }
      
    });
  });
})(jQuery);

(function ($) {
  $(document).ready(function () {
    $("#restockProduct").submit(function (e) {
      e.preventDefault();

      $(".loader").remove();
      $(".custom-loader").remove();
      $(".stockWarning").remove();

      $("body").append(
        '<div class="custom-loader"><div class="loader"></div></div>'
      );

      const data = new FormData(this);
      data.append("action", "update_stock");

      if ($("#stock").val() > 0) {
        if ($(".stockWarning").length > 0) {
          $(".stockWarning").remove();
        }

        handleAjaxResponse("POST", data).always(function () {
          $(".loader").remove();
          $(".custom-loader").remove();
        });
      } else {
        $(".loader").remove();
        $(".custom-loader").remove();
        $("#stock").after(
          "<span style='color:red' class='stockWarning'>This Field Is Required or Number Should Be Greater Than Zero<span>"
        );
      }
    });
  });
})(jQuery);

(function ($) {
  $(document).ready(function () {
    $("#configurationForm").submit(function (event) {
      event.preventDefault();

      let isValid = true;
      const inputs = $(this).find("input, textarea");
      $(".err").remove();

      $(inputs).each(function () {
        const value = $(this).val().trim();

        if (value === "") {
          $(this).after(
            "<p style='color:red;margin-bottom:5px;font-size: 16px' class='err'>This field is required</p>"
          );
          isValid = false;
          $(this).focus();
        } else if ($(this).attr("type") === "number" && isNaN(value)) {
          $(this).after(
            "<p style='color:red;margin-bottom:5px;font-size: 16px' class='err'>Please enter a valid number</p>"
          );
          isValid = false;
        }
      });

      const restockInput = document.getElementById("restockVisible");
      const restockError = document.getElementById("restockError");
      const pattern = /^(0|[1-9][0-9]*)$/;

      if (!pattern.test(restockInput.value)) {
        isValid = false;
         $("#restockError").prev().remove();
        $("#restockInput").focus();
        restockError.textContent =
          "Please enter a valid number before submitting.";
      } else {
        restockError.textContent =
          "Please enter a valid number";
        $("#restockError").text("");
      }
      const messageValue = $("#messages").val().trim();

      if (messageValue === "") {
        $(".err").remove();
        $("#messages").after(
          "<p style='color:red;margin-bottom:5px;font-size: 16px' class='err'>This field is required</p>"
        );
        isValid = false;
        $("#messages").focus();
      }

      if (isValid) {
        $(".loader").remove();
        $(".custom-loader").remove();
        $("body").append(
          '<div class="custom-loader"><div class="loader"></div></div>'
        );
        const data = new FormData(this);
        data.append("action", "update_default_configs");
        handleAjaxResponse("POST", data);
      }
    });
  });
})(jQuery);

(function ($) {
  $(document).ready(function () {
    $("#restockProductVariable").submit(function (e) {
      e.preventDefault();

      $(".loader").remove();
      $(".custom-loader").remove();

      $("body").append(
        '<div class="custom-loader"><div class="loader"></div></div>'
      );

      const data = new FormData(this);
      data.append("action", "update_variation_stock");

      if ($("#stock").val() > 0) {
        $(".stockWarning").remove();
        handleAjaxResponse("POST", data).always(function () {
          $(".loader").remove();
          $(".custom-loader").remove();
        });
      } else {
        $(".stockWarning").remove();
        $("#stock").after(
          "<span style='color:red' class='stockWarning'>This Field Is Required or Number Should Be Greater Than Zero</span>"
        );
        $(".loader").remove();
        $(".custom-loader").remove();
      }
    });
  });
})(jQuery);
