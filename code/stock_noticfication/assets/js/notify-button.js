document.addEventListener("DOMContentLoaded", function() {

  var productType = stockNotificationData.product_type;
  var isInStock = stockNotificationData.is_in_stock;
  var variations = stockNotificationData.variations;

  document.getElementById("notifyMeButton").style.display = "none";

  if (productType === 'variable') {
    
      function checkStockForSelectedVariation(variationId) {
          let selectedVariation = variations.find(variation => variation.variation_id == variationId);
          if (selectedVariation && !selectedVariation.is_in_stock) {
              document.getElementById("notifyMeButton").style.display = "block";
              document.getElementById("variation_id").value = variationId; // Set 
          } else {
              document.getElementById("notifyMeButton").style.display = "none";
              document.getElementById("variation_id").value = ''; // Clear variation ID
          }
      }

      
      jQuery(document).on('found_variation', function(event, variation) {
          checkStockForSelectedVariation(variation.variation_id);
      });

  } else {
      
      if (!isInStock) {
          document.getElementById("notifyMeButton").style.display = "block";
      } else {
          document.getElementById("notifyMeButton").style.display = "none";
      }
  }
});
