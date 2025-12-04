<?php

/**
* WP Quickcheck
*
* @wordpress-plugin
* Plugin Name:   WP Quickcheck
* Description:   A wordpress plugin to submit and store quick text entries.
* Version:       1.0.1
* Text Domain:   quickcheck
* Author:        Jake Nicholson
* Author URI:    https://github.com/shakyjake
*/

if(!defined('ABSPATH')){
	exit; // Exit if accessed directly
}

if(!defined('QUICKCHECK_ROOT_FILE')){
	define('QUICKCHECK_ROOT_FILE', __FILE__);
}

/**
 * Initialize the plugin
 * 
 * Defines version constant and includes necessary files
 */
function quickcheck_init() : void {

	if(!defined('QUICKCHECK_VERSION')){ // Define version constant for use in enqueuing assets
		define('QUICKCHECK_VERSION', get_plugin_data(QUICKCHECK_ROOT_FILE)['Version']);
	}

	// include necessary files
	require_once plugin_dir_path(QUICKCHECK_ROOT_FILE) . 'includes/shortcode.php';
	require_once plugin_dir_path(QUICKCHECK_ROOT_FILE) . 'includes/form.php';
	require_once plugin_dir_path(QUICKCHECK_ROOT_FILE) . 'includes/ajax.php';
}
add_action('init', 'quickcheck_init');

/**
 * Register the necessary JS/CSS files to be enqueued later
 */
function quickcheck_enqueue_assets() : void {
	
	wp_register_style('quickcheck-form', plugins_url('public/css/form.css', QUICKCHECK_ROOT_FILE), [], QUICKCHECK_VERSION, 'all');
	wp_register_script('quickcheck-form', plugins_url('public/js/form.js', QUICKCHECK_ROOT_FILE), ['jquery'], QUICKCHECK_VERSION, [
		'in_footer' => true,
		'strategy' => 'defer'
	]);
	wp_localize_script('quickcheck-form', 'quickcheck', [
		'ajax_url' => admin_url('admin-ajax.php'),
		'auth_token' => wp_create_nonce('quickcheck_latest')
	]);

}
add_action('wp_enqueue_scripts', 'quickcheck_enqueue_assets');

/**
 * Activation hook
 * 
 * Creates the necessary database table
 */
function quickcheck_activate() : void {
	
	global $wpdb;

	$table_name = $wpdb->prefix . 'qc_entries';
	
	$collation = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table_name} (
		id INT(11) NOT NULL AUTO_INCREMENT,
		content VARCHAR(255) NOT NULL,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
		PRIMARY KEY  (id)
	) {$collation};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php'; // contains the dbDelta function we need
	dbDelta($sql);

}
register_activation_hook(QUICKCHECK_ROOT_FILE, 'quickcheck_activate');

?>