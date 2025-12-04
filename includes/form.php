<?php

if(!defined('ABSPATH')){
	exit; // Exit if accessed directly
}

/**
 * Handle form submission
 * @return QuickCheckFormResponse Response object containing success status and messages
 */
function quickcheck_form_submit(int $min_content_length, int $max_content_length) : QuickCheckFormResponse {
	
	global $wpdb;

	$content = '';
	if(!empty($_POST['content'])){
		$content = stripslashes($_POST['content']); // obtain the submitted content and strip slashes added by PHP
	}

	$auth_token = '';
	if(!empty($_POST['auth_token'])){
		$auth_token = $_POST['auth_token'];
	}

	$response = new QuickCheckFormResponse();

	// verify nonce
	if(!wp_verify_nonce($auth_token, 'quickcheck_form_submit')){
		$response->add('Security check failed. Please try again.', true);
	}

	// validate content
	$content = trim($content);
	if(empty($content)){
		$response->add('Content cannot be empty.', true);
	} elseif(strlen($content) < $min_content_length){
		$response->add(sprintf('Content must be at least %1$s characters long.', (string)$min_content_length), true);
	} elseif(strlen($content) > $max_content_length){
		$response->add(sprintf('Content cannot be more than %1$s characters long.', (string)$max_content_length), true);
	}

	if(!$response->success){
		return $response;
	}

	// insert the content into the database
	$sql = $wpdb->prepare(
		"INSERT INTO {$wpdb->prefix}qc_entries (content) VALUES (%s)",
		$content
	);
	$inserted = $wpdb->query($sql);

	if($inserted === false){// insertion failed
		$response->add('An error occurred while saving your entry. Please try again.', true);
	} else { // insertion succeeded!
		$response->add('Your entry has been saved successfully.');
	}

	return $response;
	
}

/**
 * Response class for form submissions
 */
class QuickCheckFormResponse {

	public bool $success;
	public array $messages;

	public function add(string $message, ?bool $is_error = false) : void {
		if($is_error){
			$this->success = false;
		}
		$this->messages[] = new QuickCheckFormResponseMessage($message, $is_error);
	}

	public function __construct(){
		$this->success = true;
		$this->messages = [];
	}

	public function __toString(){
		$html = [];
		foreach($this->messages as $message){
			$html[] = (string)$message;
		}
		return implode('', $html);
	}

}

/**
 * Message class for individual response messages
 */
class QuickCheckFormResponseMessage {

	public string $message;
	public bool $is_error;

	public function __construct(string $message, ?bool $is_error = false){
		$this->message = $message;
		$this->is_error = $is_error;
	}

	public function __toString(){
		$html = [];
		$html[] = '<div class="quickcheck__message quickcheck__message--';
		$html[] = $this->is_error ? 'error' : 'success';
		$html[] = '">';
			$html[] = esc_html(__($this->message, 'quickcheck'));
		$html[] = '</div>';
		return implode('', $html);
	}

}

?>