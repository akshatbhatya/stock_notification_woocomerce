function hello(button) {
  var productId = button.dataset.productId;
  var productName = button.dataset.productName;
  var productQuantity = button.dataset.productQuantity;

  // Populate modal fields
  document.querySelector("#productId").value = productId;
  document.querySelector("#stock").value = productQuantity;
  document.querySelector("#productName").value = productName;
}

// Form validation
function validateForm(event) {
  event.preventDefault();

  let stockInput = document.querySelector("#stock");
  let errorMessageDiv = document.querySelector("#error-message");

  if (stockInput.value === "" || stockInput.value <= 0) {
    errorMessageDiv.style.display = "block";
    return false; // Stop form submission
  } else {
    errorMessageDiv.style.display = "none";
  }
  return true;
}

/* product modals */

function productHistory(button) {
 
  let productId = button.dataset.productid;
  let timesChanges = button.dataset.timechanges;
  let productName = button.dataset.productname;
  let stock = button.dataset.stock;
  let timeStamps = button.dataset.timestamps;

  document.querySelector("#product_id").innerHTML = `<strong>Product ID:</strong> ${productId}`;
  document.querySelector("#product_name").innerHTML = `<strong>Product Name:</strong> ${productName}`;
  document.querySelector("#times_manage").innerHTML = `<strong>Times Managed:</strong> ${timesChanges}`;
  document.querySelector("#now_stock").innerHTML = `<strong>Recently Updated Stock:</strong> ${stock}`;
  document.querySelector("#last_update_time").innerHTML = `<strong>Last Updated:</strong> ${timeStamps}`;
}

