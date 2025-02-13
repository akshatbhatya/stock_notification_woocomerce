<?php
global $product;
$defaultButtonTitle = "Notify Me";

$product_id = $product->get_id(); 
$variation_id = null; 

// Check if the product is a variable product
if ($product->is_type('variable')) {
    
    $variations = $product->get_available_variations();
   
} else {
    $variation_id = null; 
}

// Fetch custom button title from the database
global $wpdb;
$table_name = $wpdb->prefix . "notification_configuration";
$query = "SELECT * FROM $table_name";
$updated_data = $wpdb->get_results($query);
$defaultButtonTitle = !empty($updated_data) ? $updated_data[0]->button_title : $defaultButtonTitle;

$currentUser = wp_get_current_user();
$userEmail = $currentUser->user_email;

?>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
      
        document.getElementById("notifyMeButton").style.display = "none";

        <?php if ($product->is_type('variable')): ?>
            var variations = <?php echo json_encode($variations); ?>;

            // Function to check stock for the selected variation
            function checkStockForSelectedVariation(variationId) {
                let selectedVariation = variations.find(variation => variation.variation_id == variationId);
                if (selectedVariation && !selectedVariation.is_in_stock) {
                    document.getElementById("notifyMeButton").style.display = "block";
                    document.getElementById("variation_id").value = variationId; // Set variation ID in the hidden input
                } else {
                    document.getElementById("notifyMeButton").style.display = "none";
                    document.getElementById("variation_id").value = ''; // Clear variation ID
                }
            }

            // Listen for variation change event
            jQuery(document).on('found_variation', function(event, variation) {
                checkStockForSelectedVariation(variation.variation_id);
            });
        <?php else: ?>
            // For simple products, check if out of stock
            if (!<?php echo json_encode($product->is_in_stock()); ?>) {
                document.getElementById("notifyMeButton").style.display = "block";
            } else {
                document.getElementById("notifyMeButton").style.display = "none";
            }
        <?php endif; ?>
    });
</script>

<!-- Notify Me Button and Modal -->
<button class="button" id="notifyMeButton" style="display:none;"><?php echo esc_html($defaultButtonTitle); ?></button>

<div id="notifyMeModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="" id="notifyMeForm" novalidate>
            <?php wp_nonce_field('verifyNotification', 'token'); ?>
            <input type="hidden" name="product_id" value="<?php echo esc_html($product_id?$product_id:0); ?>" /> <!-- Product ID -->
            <input type="hidden" id="variation_id" name="variation_id" value="<?php echo esc_html($variation_id?$variation_id:0); ?>" /> <!-- Hidden input for variation ID -->
            <input type="email" name="email" placeholder="Enter Your Email" class="NotificationEmail" value="<?php echo esc_html($userEmail); ?>" required />
            <input type="hidden" value="<?php echo esc_html($currentUser->user_login?$currentUser->user_login:"Dear user") ?>" name="username">
            <div class="error-message" style="color: red; display: none;">Please enter a valid email address.</div>
            <button type="submit" class="submit-button">Submit</button>
        </form>
    </div>
</div>
