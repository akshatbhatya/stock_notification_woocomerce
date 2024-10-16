<?php

class stock_notification_class
{
  public function __construct() {}

  function create_db_for_email_registration()
  {

    global $wpdb;
    $table = $wpdb->prefix . "notification_emails";
    $Encoding = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table(
          id BIGINT UNSIGNED  PRIMARY KEY AUTO_INCREMENT,
          product_id int NOT NULL,
          variation_id INT NOT NULL,
          email VARCHAR(255)  NOT NULL,
          name varchar(255) not null,
          notification TINYINT(1) DEFAULT 0 NOT NULL
  )$Encoding";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }


  function create_db_for_old_updated_stocks_history()
  {

    global $wpdb;
    $table = $wpdb->prefix . "updated_stocks";
    $Encoding = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table(
          id BIGINT UNSIGNED  PRIMARY KEY AUTO_INCREMENT,
          product_id int NOT NULL,
          product_name VARCHAR(255) NOT NULL,
          stock INT NOT NULL,
          timeStamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )$Encoding";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  /* db for configurations */

  function create_db_for_configurations()
  {
    global $wpdb;
    $Encoding = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . "notification_configuration";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
    id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    button_title VARCHAR(255) DEFAULT 'Notify Me' NOT NULL,
    stock int not null,
    message TEXT NOT NULL,
    subject VARCHAR(255) NOT NULL
    )$Encoding ";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  /* add default configs to table */

  function add_default_configs() {
    global $wpdb;
    $table = $wpdb->prefix . 'notification_configuration';

    $message = '<h5>Hi {{username}},</h5>
                <h5><strong>Product Name :</strong> {{product_name}}</h5>
                <h5><strong>Stock Status :</strong> {{stock_status}}</h5>
                <h5><strong>Message :</strong>
                <strong>Great news! The product you wanted is back in stock. Hurry and grab it</strong> before <strong>it</strong><strong>’s gone again now!</strong></h5>';

    $subject = 'Product Back in Stock Notification';

   
    $wpdb->insert(
        $table,
        array(
            'button_title' => 'NOTIFY ME',
            'stock'        => 5,
            'message'      => $message,
            'subject'      => $subject
        ),
        array(
            '%s',  
            '%d',   
            '%s',  
            '%s'    
        )
    );
}


  /* function runs on plugin activation */

  function on_plugin_activation()
  {
    flush_rewrite_rules(true);
    $this->create_db_for_email_registration();
    $this->create_db_for_old_updated_stocks_history();
    $this->create_db_for_configurations();
    $this->add_default_configs();
  }




  /* on plugin  deactivate */

  function on_plugin_deactivate()
  {
    flush_rewrite_rules(true);
    
  }

  /* on delete plugin */


  // function on_plugin_delete()
  // {
  //   flush_rewrite_rules(true);
  //   global $wpdb;
  //   $emailTable = $wpdb->prefix . "notification_emails";
  //   $history = $wpdb->prefix . "updated_stocks";
  //   $configs_table = $wpdb->prefix . "notification_configuration";
  //   $sql = "DROP TABLE IF EXISTS $emailTable";
  //   $historySql = "DROP TABLE IF EXISTS $history";
  //   $configSql = "DROP TABLE IF EXISTS $configs_table";
  //   $wpdb->query($sql);
  //   $wpdb->query($historySql);
  //   $wpdb->query($configSql);
  // }

  function on_plugin_delete() {
    flush_rewrite_rules(true);
    global $wpdb;

    $emailTable = esc_sql($wpdb->prefix . "notification_emails");
    $history = esc_sql($wpdb->prefix . "updated_stocks");
    $configs_table = esc_sql($wpdb->prefix . "notification_configuration");

   
    $wpdb->query("DROP TABLE IF EXISTS $emailTable");
    $wpdb->query("DROP TABLE IF EXISTS $history");
    $wpdb->query("DROP TABLE IF EXISTS $configs_table");
}




  /* to show notify button */

  function show_notify_me_button()
  {
    ob_start();
    require_once  __DIR__ . "/../ui_template/notification_ui.php";
    $output = ob_get_clean();
    return $output;
  }

  
  /* add email for notification */

  // function add_new_email($product_id, $variation_id, $email, $notification, $name)
  // {
  //   global $wpdb;
  //   $table_name = $wpdb->prefix . 'notification_emails';
  //   $prepared_query = $wpdb->prepare(
  //     "INSERT INTO $table_name (product_id, variation_id, email, notification,name) VALUES (%d, %d, %s, %s,%s)",
  //     $product_id,
  //     $variation_id,
  //     $email,
  //     $notification,
  //     $name
  //   );

  //   $res = $wpdb->query($prepared_query);

  //   if ($res) {
  //     wp_send_json_success('Notification Email Sent Successfully', 200);
  //   } else {
  //     wp_send_json_error('Failed to send Notification Email', 500);
  //   }
  // }


  function add_new_email($product_id, $variation_id, $email, $notification, $name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'notification_emails';

    $result = $wpdb->insert(
        $table_name,
        array(
            'product_id'   => $product_id,
            'variation_id' => $variation_id,
            'email'        => $email,
            'notification' => $notification,
            'name'         => $name
        ),
        array(
            '%d', 
            '%d', 
            '%s', 
            '%s',
            '%s'  
        )
    );


    if ($result) {
        wp_send_json_success('Notification Email Sent Successfully', 200);
    } else {
        wp_send_json_error('Failed to send Notification Email', 500);
    }
}



  /* update stocks */

  function update_stock()
  {
    if (!check_ajax_referer('verifyStock', 'token', false)) {
      wp_send_json_error('Invalid token.');
      return;
    }

    if (!isset($_POST['product_id']) || !isset($_POST['stock_quantity'])) {
      wp_send_json_error('Missing product ID or stock quantity.');
      return;
    }

    $product_id = intval($_POST['product_id']);
    $stock_quantity = intval($_POST['stock_quantity']);

    if ($stock_quantity <= 0) {
      wp_send_json_error('Stock quantity must be greater than zero.');
      return;
    }

    $product = wc_get_product($product_id);

    if (!$product) {
      wp_send_json_error('Invalid product.');
      return;
    }

    $product_name = $product->get_title();
    $stock_status = $product->get_stock_status();
    $product->set_stock_quantity($stock_quantity);
    $product->save();

    // Respond with success
    wp_send_json_success('Stock updated successfully.');


    $this->add_restock_history($product_id, $product_name, $stock_quantity);

    global $wpdb;

    $table_name = $wpdb->prefix . 'notification_emails';
    $table_config = $wpdb->prefix . 'notification_configuration';

    // Get notification configuration
    $notification_configs_query =("SELECT * FROM $table_config");
    $notification_configs = $wpdb->get_results($notification_configs_query);

    $messageForUser = $notification_configs[0]->message;
    $subjectForUser = $notification_configs[0]->subject;



    $query = "SELECT * FROM $table_name";
    $all_emails = $wpdb->get_results($query);


    foreach ($all_emails as $user) {
      if ($user->product_id == $product_id) {

        $username = $user->name;
        $replaced_html = str_replace(
          array('{{username}}', '{{product_name}}', '{{stock_status}}'),
          array($username, $product_name, $stock_status),
          $messageForUser
        );

        $updateEmailStatus = $wpdb->prepare(
          "UPDATE $table_name SET notification = 1 WHERE id = %d",
          $user->id
        );

        $wpdb->query($updateEmailStatus);
        $this->mail_sender_to_user($user->email, $subjectForUser, $replaced_html);
      }
    }
  }



  /* update variation stock */

  function update_stock_variable()
  {
    $product_id = intval($_POST['product_id']);
    $stock_quantity = intval($_POST['stock_quantity']);

    if (!$product_id) {
      wp_send_json_error('id is missing', 500);
      return 0;
    }

    if (!check_ajax_referer('verifyStockVariable', 'token', false)) {
      wp_send_json_error('unknown request found ', 500);
      return 0;
    }

    $variation = wc_get_product($product_id);


    if (!$variation) {
      wp_send_json_error('Invalid variation.');
      return;
    }
    $product = wc_get_product($variation->get_parent_id());
    $product_name = $product->get_name();
    $variation_name = $variation->get_attributes();

    $variation_name_string = implode(', ', array_map(function ($key, $value) {
      return wc_attribute_label($key) . ': ' . $value;
    }, array_keys($variation_name), $variation_name));


    $variation->set_stock_quantity($stock_quantity);
    $variation->set_stock_status('instock');
    $variation->save();

    wp_send_json_success('Stock quantity updated successfully.');

    $this->add_restock_history($variation->get_parent_id(), $product_name . ' - ' . $variation_name_string, $stock_quantity);

    global $wpdb;

    $table_name = $wpdb->prefix . 'notification_emails';
    $table_config = $wpdb->prefix . 'notification_configuration';

    // Get notification configuration
    $notification_configs_query = "SELECT * FROM $table_config";
    $notification_configs = $wpdb->get_results($notification_configs_query);

    $messageForUser = $notification_configs[0]->message;
    $subjectForUser = $notification_configs[0]->subject;




    $query = "SELECT * FROM $table_name";
     $all_emails = $wpdb->get_results($query);

    // $all_emails= $wpdb->get_row($wpdb->prepare( $query ));

    if ($stock_quantity > 0) {
      foreach ($all_emails as $user) {

        if ($user->variation_id == $product_id) {
          global $wpdb;
          $username = $user->name;

          $replaced_html = str_replace(
            array('{{username}}', '{{product_name}}', '{{stock_status}}'),
            array($username, $product_name, 'in stock'),
            $messageForUser
          );

          $this->mail_sender_to_user($user->email, $subjectForUser, $replaced_html);
          $updateEmailStatus = $wpdb->prepare(
            "UPDATE $table_name SET notification = 1 WHERE id = %d",
            $user->id
          );
          $wpdb->query($updateEmailStatus);
          
        }
      }
    }
  }

  // function add_restock_history($product_id, $product_name, $stock)
  // {
  //   global $wpdb;
  //   $table = $wpdb->prefix . 'updated_stocks';
  //   $query_prepared = $wpdb->prepare(
  //     "INSERT INTO $table (product_id, product_name, stock) VALUES (%d, %s, %d)",
  //     $product_id,
  //     $product_name,
  //     $stock
  //   );

  //   $wpdb->query($query_prepared);
  // }

  function add_restock_history($product_id, $product_name, $stock)
{
    global $wpdb;
    $table = $wpdb->prefix . 'updated_stocks';

   
    $data = array(
        'product_id' => $product_id,
        'product_name' => $product_name,
        'stock' => $stock,
    );

    $format = array(
        '%d', 
        '%s', 
        '%d', 
    );

 
   $wpdb->insert($table, $data, $format);

}



  /* mail  sent to  client when come back stock  */

  function mail_sender_to_user($to, $subject, $message)
  {
    wp_mail($to, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
  }


  /* update default configs which given in configs */

  // function update_default_configs($id, $button_title, $stock, $subject, $message)
  // {
  //   global $wpdb;
  //   $table_name = $wpdb->prefix . "notification_configuration";

  //   $current_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ID = %d", $id));


  //   if (
  //     $current_data->button_title === $button_title &&
  //     $current_data->stock === $stock &&
  //     $current_data->subject === $subject &&
  //     $current_data->message === $message
  //   ) {
  //     wp_send_json_success("Default changes are added successfully.");
  //     return;
  //   }


  //   $sql = $wpdb->prepare(
  //     "UPDATE $table_name SET button_title = %s, stock = %d, subject = %s, message = %s WHERE ID = %d",
  //     $button_title,
  //     $stock,
  //     $subject,
  //     $message,
  //     $id
  //   );


  //   $response = $wpdb->query($sql);


  //   if ($response !== false) {
  //     wp_send_json_success("Configuration updated successfully", 200);
  //   } else {
  //     wp_send_json_error("Configuration is not updated. You may not have permission to make changes to these fields.", 500);
  //   }
  // }


  function update_default_configs($id, $button_title, $stock, $subject, $message)
{
    global $wpdb;
    $table_name = $wpdb->prefix . "notification_configuration";

  
    $current_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ID = %d", $id));

  
    if (
        $current_data &&
        $current_data->button_title === $button_title &&
        $current_data->stock === $stock &&
        $current_data->subject === $subject &&
        $current_data->message === $message
    ) {
        wp_send_json_success("No changes to update.");
        return;
    }

 
    $data = array(
        'button_title' => $button_title,
        'stock' => $stock,
        'subject' => $subject,
        'message' => $message,
    );


    $where = array('ID' => $id);


    $updated = $wpdb->update($table_name, $data, $where);

   
    if ($updated === false) {
        wp_send_json_error("Configuration is not updated. You may not have permission to make changes to these fields.", 500);
    } else {
        wp_send_json_success("Configuration updated successfully", 200);
    }
}

}
