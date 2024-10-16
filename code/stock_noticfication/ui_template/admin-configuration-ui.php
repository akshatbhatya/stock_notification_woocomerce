<?php
global $wpdb;

$TABLE_NAME = $wpdb->prefix . "notification_configuration";
$query = "SELECT * FROM $TABLE_NAME";
$response = $wpdb->get_results($query);

?>
<div class="form-container">
    <form class="form" id="configurationForm">
        <input type="hidden" name="id" value="<?php echo esc_html($response[0]->id) ?>">
        <span class="heading">Make Configuration</span>
        <?php wp_nonce_field('configurationForm', 'token') ?>

        <label for="buttonText" class="labels">
            Button Text
            <span class="custom-tooltip">?
                <span class="custom-tooltiptext">Enter the text that will appear on the button.</span>
            </span>
        </label>
        <input id="buttonText" placeholder="Button Text" value="<?php echo esc_html($response[0]->button_title) ?>" name="button_title" type="text" class="input">



        <label for="restockVisible" class="labels">Restock When Appears To Be Visible
            <span class="custom-tooltip">?
                <span class="custom-tooltiptext">Specify the minimum stock level that triggers restocking notifications.</span>
            </span>
        </label>
        <input id="restockVisible"  placeholder="Restock When Appears To Be Visible" type="text"  class="input" name="stock" value="<?php echo esc_html($response[0]->stock) ?>">


        <p id="restockError" style="color: red; font-size: 16px;"></p>

        <label for="subject" class="labels">Subject Relevant To User Which is Out of Stock And Now Come Back In Stock

            <span class="custom-tooltip">?
                <span class="custom-tooltiptext">This subject line will inform users that the previously out-of-stock item is now available</span>

        </label>
        <input id="subject" placeholder="Subject Relevant To User" type="text" class="input" value="<?php echo esc_html($response[0]->subject) ?>" name="subject">

        <label for="message" class="labels">A Message When Stock Comes For A User

            <span class="custom-tooltip">?
                <span class="custom-tooltiptext">Enter the message that will be sent to users when the item is back in stock.</span>
        </label>
        <?php
        $message = $response[0]->message;
        $id = "messages";
        $settings = array(
            'media_buttons' => false,
            'textarea_rows' => 10,
            'teeny'         => true,
            'tinymce'       => true
        );
        ?>
        <?php wp_editor($message, $id, $settings); ?>

        <div class="button-container">
            <button type="submit" class="send-button" style="width: 30%;">Make Changes</button>
        </div>
    </form>
</div>