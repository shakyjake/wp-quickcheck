<?php

if(!defined('ABSPATH')){
	exit; // Exit if accessed directly
}

/**
 * Do nothing
 * 
 * Used to handle unauthenticated requests to the 'quickcheck_latest' action
 * 
 * @return void
 */
function quickcheck_noop() : void {
	exit;
}

/**
 * Obtain and echo the latest entries in JSON format
 * @return void
 */
function quickcheck_latest() : void {

	global $wpdb;

	if(current_user_can('administrator') || current_user_can('editor')){
		if(!empty($_POST['auth_token']) && wp_verify_nonce($_POST['auth_token'], 'quickcheck_latest')){
			// fetch the latest 5 entries
			$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qc_entries ORDER BY created_at DESC LIMIT 5", ARRAY_A);
			wp_send_json_success($results);
			exit;
		}
	}

	wp_send_json_error([], 403);
	exit;

}
add_action('wp_ajax_quickcheck_latest', 'quickcheck_latest');
add_action('wp_ajax_nopriv_quickcheck_latest', 'quickcheck_noop');


?>