<?php

/**
 * Plugin Name: akshat 1
 * Plugin URI: https://www.google.com
 * Description: Sends notification to users when the product is back in stock.
 * Version: 1.0
 * Author: NetWeb
 * Author URI: https://www.google.com
 * License: GPL 
 * Text Domain: stock_noticfication
 */


require_once  plugin_dir_path(__FILE__) . "./includes/class-stock-notification.php";

/* scripts and css register */

function add_scripts_local()
{
  wp_enqueue_script('sweet_alert', "https://cdn.jsdelivr.net/npm/sweetalert2@11", array('jquery'), false);
  wp_enqueue_script('notifierScript', plugin_dir_url(__FILE__) . "assets/js/notifierScript.js");
  wp_enqueue_style('notifierStyle', plugin_dir_url(__FILE__) . "assets/css/notifierStyle.css");

  // remove code 


  // wp_enqueue_script('leave', plugin_dir_url(__FILE__) . "assets/js/configuration-validation.js",array("jquery"),false,true);

  // remove code end
  wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
}
add_action("wp_enqueue_scripts", 'add_scripts_local');

function add_scripts_admin()
{
  wp_enqueue_script('sweet_alert', "https://cdn.jsdelivr.net/npm/sweetalert2@11", array('jquery'), false);
  wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
  wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js');
  wp_enqueue_script("admin-restock-js", plugin_dir_url(__FILE__) . "assets/js/admin-restock.js");
  wp_enqueue_style('admin-configs-css', plugin_dir_url(__FILE__) . 'assets/css/admin-configs.css');

  wp_enqueue_style('font-awesome-css', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css");
  wp_enqueue_script('font-awesome-js', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js", array('jquery'), false);

  wp_enqueue_style('restock-history-css', plugin_dir_url(__FILE__) . 'assets/css/restock-history.css');
  wp_enqueue_style('admin-restock-css', plugin_dir_url(__FILE__) . 'assets/css/admin-restock.css');

  wp_enqueue_style('DATA - TABLE-CSS', "https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css");
  wp_enqueue_script('DATA-SCRIPT', "https://cdn.datatables.net/2.1.8/js/dataTables.js");
  wp_enqueue_script('leave', plugin_dir_url(__FILE__) . "assets/js/configuration-validation.js", array("jquery"), false, true);
}

add_action("admin_enqueue_scripts", 'add_scripts_admin');



function on_plugin_activation()
{
  $notifier = new stock_notification_class();
  $notifier->on_plugin_activation();
}
register_activation_hook(__FILE__, 'on_plugin_activation');

function on_plugin_deactivation()
{
  $notifier = new stock_notification_class();
  $notifier->on_plugin_deactivate();
}
register_deactivation_hook(__FILE__, 'on_plugin_deactivation');

// function uninstall_custom_plugin(){
//   $notifier = new stock_notification_class();
//   $notifier->on_plugin_delete();
// }
// register_uninstall_hook(__FILE__,'uninstall_custom_plugin');

// Show the "Notify Me" button when the product is out of stock

function show_notify_me_button() {
  $notifier = new stock_notification_class();
  echo ( $notifier->show_notify_me_button() );
}



add_action('woocommerce_single_product_summary', 'show_notify_me_button', 20);

add_action('admin_menu', 'notification_menu');

/* create custom  menu for notification */

function configuration_ui()
{
  ob_start();
  require_once plugin_dir_path(__FILE__) . "./ui_template/admin-configuration-ui.php";
  $output = ob_get_clean();
  echo $output;

}

function restock_ui_variation()
{
  ob_start();
  require_once plugin_dir_path(__FILE__) . "./ui_template/admin-variable-restock.php";
  $output = ob_get_clean();
  echo $output;
}

function restock_ui()
{
  ob_start();
  require_once plugin_dir_path(__FILE__) . "./ui_template/admin-restock.php";
  $output = ob_get_clean();
  echo $output;
}

function restock_history_ui()
{
  ob_start();
  require_once plugin_dir_path(__FILE__) . "/ui_template/admin-restock-history-ui.php";
  $output = ob_get_clean();
  echo $output;
}

function Email_Status_ui()
{
  require_once plugin_dir_path(__FILE__) . "/ui_template/email-admin-ui.php";
  $output = ob_get_clean();
  echo $output;
}

/* add admin menus */

function notification_menu()
{
  $slug = "notification";
  add_menu_page(
    'Stock Notification',
    'Notification',
    'manage_options',
    $slug,
    'configuration_ui',
    'dashicons-bell'
  );

  add_submenu_page($slug, 'Restock Product', 'Restock product', 'manage_options', 'restock', 'restock_ui');
  add_submenu_page($slug, 'Product History', 'Product History', 'manage_options', 'history', 'restock_history_ui');

  add_submenu_page($slug, 'Restock Variable', 'Restock Variable', 'manage_options', 'restockvar', 'restock_ui_variation');

  add_submenu_page($slug, 'Email Status', 'Email Status', 'manage_options', 'email_status', 'Email_Status_ui');
}

/* add ajax to hit */

function add_ajax_request()
{
  wp_enqueue_script('notifier_ajax', plugin_dir_url(__FILE__) . "./assets/js/ajaxHandller.js", array('jquery'), null, true);
  wp_localize_script(
    'notifier_ajax',
    'notifier_ajax_call',
    [
      'ajax_url' => admin_url('admin-ajax.php')
    ]

  );
}

add_action('admin_enqueue_scripts', 'add_ajax_request');
add_action('wp_enqueue_scripts', 'add_ajax_request');

/* add notify email to db */

function handle_send_notification_email()
{


  $product_id   = intval($_POST['product_id']);
  $variation_id = intval($_POST['variation_id']);
  $email        = sanitize_email($_POST['email']);
  $token        = sanitize_text_field($_POST['token']);
  $name = sanitize_text_field($_POST['username']);

  if (check_ajax_referer('verifyNotification', 'token', false)) {
    $emailSender = new stock_notification_class();
    $Notification = 0;

    $result = $emailSender->add_new_email($product_id, $variation_id, $email, $Notification, $name);

    if ($result) {
      wp_send_json_success('your Email is added Successfully', 200);
    } else {
      wp_send_json_error('Failed to add Notification Email', 500);
    }
  } else {
    wp_send_json_error('unknown request found', 500);
  }
}

/* update stock  */

function handle_update_stocks()
{

  $update_stocks = new stock_notification_class();
  $update_stocks->update_stock();
}

/* handle  */

// Add this function to your theme's functions.php or a custom plugin

add_action('woocommerce_product_set_stock', 'handle_stock_update', 10, 1);

function handle_stock_update($product)
{

  if (!$product instanceof WC_Product) {
    return;
  }


  $product_id = $product->get_id();
  $stock_quantity = $product->get_stock_quantity();
  $product_name = $product->get_title();
  $stock_status = $product->get_stock_status();
  $stock_Quantity = $product->get_stock_quantity();

  $update_history = new stock_notification_class();
  $update_history->add_restock_history($product_id, $product_name, $stock_quantity);

  global $wpdb;

  $table_name = $wpdb->prefix . 'notification_emails';

  $table_config = $wpdb->prefix . 'notification_configuration';

  $notification_configs_query = "SELECT * FROM $table_config";

  $notification_configs = $wpdb->get_results($notification_configs_query);

  $messageForUser = $notification_configs[0]->message;
  $subjectForUser = $notification_configs[0]->subject;


  $query = "SELECT * FROM $table_name";

  $all_emails = $wpdb->get_results($query);

  if ($stock_Quantity > 0) {
    foreach ($all_emails as $user) {
      if ($user->product_id == $product_id) {
        $username=$user->name;
        $replaced_html = str_replace(
          array( '{{username}}','{{product_name}}', '{{stock_status}}'),
          array($username,$product_name, $stock_status),
          $messageForUser
        );

        $data = array(
          'notification' => 1 
      );
      
      $where = array('id' => $user->id);
      
    
       $wpdb->update($table_name, $data, $where);

        // $updateEmailStatus = $wpdb->prepare(
        //   "UPDATE $table_name SET notification = 1 WHERE id = %d",
        //   $user->id
        // );

        // $wpdb->query($updateEmailStatus);

        $update_history->mail_sender_to_user($user->email, $subjectForUser, $replaced_html);
      }
    }
  }
}

/* handle variation history and stock */

add_action('woocommerce_variation_set_stock', 'handle_variation_stock_update', 10, 1);

function handle_variation_stock_update($product)
{
  if (!$product instanceof WC_Product) {
    return;
  }

  $product_id = $product->get_id();
  $stock_quantity = $product->get_stock_quantity();
  $product_name = $product->get_title();
  $stock_status = $product->get_stock_status();


  if ($product instanceof WC_Product_Variation) {
    $parent_id = $product->get_parent_id();
    $parent_product = wc_get_product($parent_id);
    $parent_product_name = $parent_product ? $parent_product->get_title() : '';
    $product_name = $parent_product_name . ' (' . implode(', ', $product->get_variation_attributes()) . ')'; // Display variation attributes


    $variation_stock_quantity = $product->get_stock_quantity();
  }

  $update_history = new stock_notification_class();
  $update_history->add_restock_history($parent_id, $product_name, $variation_stock_quantity);

  global $wpdb;

  // Fetch notification configuration and emails
  $table_name = $wpdb->prefix . 'notification_emails';
  $table_config = $wpdb->prefix . 'notification_configuration';
  $notification_configs_query = "SELECT * FROM $table_config";
  $notification_configs = $wpdb->get_results($notification_configs_query);

  if (empty($notification_configs)) {
    return;
  }

  $messageForUser = $notification_configs[0]->message;
  $subjectForUser = $notification_configs[0]->subject;

 


  $query = "SELECT * FROM $table_name";

  $all_emails = $wpdb->get_results($query);



  if ($variation_stock_quantity > 0) {
    $email_sender = new stock_notification_class();
    foreach ($all_emails as $user) {


      if ($user->variation_id == $product_id) {
        $username=$user->name;
        $replaced_html = str_replace(
          array( '{{username}}','{{product_name}}', '{{stock_status}}'),
          array($username,$product_name, $stock_status),
          $messageForUser
        );

        $data = array(
          'notification' => 1 
      );
      
      
      $where = array('id' => $user->id);
      

       $wpdb->update($table_name, $data, $where);

        // $updateEmailStatus = $wpdb->prepare(
        //   "UPDATE $table_name SET notification = 1 WHERE id = %d",
        //   $user->id
        // );
        // $wpdb->query($updateEmailStatus);

        $email_sender->mail_sender_to_user($user->email, $subjectForUser, $replaced_html);
      }
    }
  }
}


function to_update_stock_variable()
{
  $variations = new stock_notification_class();
  $variations->update_stock_variable();
}

add_action('wp_ajax_update_variation_stock', 'to_update_stock_variable'); // For logged-in users

add_action('wp_ajax_update_stock', 'handle_update_stocks'); // For logged-in users


add_action('wp_ajax_send_notification_email', 'handle_send_notification_email');
add_action('wp_ajax_nopriv_send_notification_email', 'handle_send_notification_email');


function update_default_configs()
{

  $message = isset($_POST['messages']) ? wp_kses($_POST['messages'], array(
    'p' => array(),
    'a' => array(
      'href' => array(),
      'title' => array(),
    ),
    'strong' => array(),
    'em' => array(),
    'br' => array(),
    'ul' => array(),
    'ol' => array(),
    'li' => array(),
    'h1' => array(),
    'h2' => array(),
    'h3' => array(),
    'h4' => array(),
    'h5' => array(),
    'h6' => array()

  )) : '';
  $id = intval($_POST['id']);
  $button_title = sanitize_text_field($_POST['button_title']);
  $stock = sanitize_text_field($_POST['stock']);
  $subject = sanitize_text_field($_POST['subject']);

  if (check_ajax_referer('configurationForm', 'token', false)) {
    $update_configs = new stock_notification_class();
    $update_configs->update_default_configs($id, $button_title, $stock, $subject, $message);
  }
}

add_action('wp_ajax_update_default_configs', 'update_default_configs'); // For logged-in users
