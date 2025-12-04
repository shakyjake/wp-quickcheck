<?php

if(!defined('ABSPATH')){
	exit; // Exit if accessed directly
}

if(!defined('WP_UNINSTALL_PLUGIN')){
    exit; // Exit if uninstall hasn't been called correctly
}

// drop our custom database table
global $wpdb;
$table_name = $wpdb->prefix . 'qc_entries';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

?>