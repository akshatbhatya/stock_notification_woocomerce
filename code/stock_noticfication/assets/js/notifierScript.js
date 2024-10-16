(function() {
  document.addEventListener('DOMContentLoaded', function() {
    // Select the elements required for the modal functionality
    var modal = document.getElementById("notifyMeModal");
    var btn = document.getElementById("notifyMeButton");
    var span = modal.querySelector(".close");
    var form = document.getElementById("notifyMeForm");
    var emailInput = form.querySelector("input[name='email']");
    var errorMessage = modal.querySelector(".error-message");

    btn.addEventListener('click', function() {
      modal.classList.add("show");
      errorMessage.style.display = "none"; 
    });

    span.addEventListener('click', function() {
      modal.classList.remove("show");
    });

   
    window.addEventListener('click', function(event) {
      if (event.target === modal) {
        modal.classList.remove("show");
      }
    });

    
    form.addEventListener('submit', function(event) {
      
      sessionStorage.setItem("isValidate", 'true');

      if (!emailInput.checkValidity()) {
        event.preventDefault(); 
        errorMessage.style.display = "block"; 
        sessionStorage.setItem("isValidate", 'false');
      }
    });
  });
})();  