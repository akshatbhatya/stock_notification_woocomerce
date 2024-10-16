<?php

global $wpdb;
$table_name = $wpdb->prefix . "notification_emails";
$query = "SELECT * FROM $table_name";
$email_data = $wpdb->get_results($query);

$count = 1;
?>
<div class="custom-container mt-5">
  <h2 class="custom-heading text-center mb-4">User Email Status</h2>
  <table class="custom-table table myTable">
    <thead class="custom-table-header">
      <tr>
        <th scope="col">#</th>
        <th scope="col">Product Id</th>
        <th scope="col">variation Id</th>
        <th scope="col">Email</th>
        <th scope="col">Email Status Sent</th>
      </tr>
    </thead>
    <tbody>
      <?php
      foreach ($email_data as $user) {
      ?>
        <tr class="custom-table-row">
          <th scope="row"><?php echo esc_html($count++) ?></th>
          <td><?php echo esc_html($user->product_id) ?></td>
          <td><?php echo esc_html($user->variation_id) ?></td>
          <td><?php echo esc_html($user->email) ?></td>
          <td><?php echo esc_html($user->notification==0?'❌':"✅") ?></td>
        </tr>
      <?php
      }
      ?>
    </tbody>
  </table>

</div>