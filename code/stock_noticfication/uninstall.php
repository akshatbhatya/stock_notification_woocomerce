<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}


global $wpdb;


$emailTable = $wpdb->prefix . "notification_emails";
$historyTable = $wpdb->prefix . "updated_stocks";

$wpdb->query("DROP TABLE IF EXISTS $emailTable");


$wpdb->query("DROP TABLE IF EXISTS $historyTable");
