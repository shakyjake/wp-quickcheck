<?php

if(!defined('ABSPATH')){
	exit; // Exit if accessed directly
}

/**
 * Shortcode to display the quickcheck form
 * 
 * @param array $atts Shortcode attributes
 * @param string|null $content Enclosed content (if any)
 * @param string $tag Shortcode tag
 * 
 * @return string HTML output of the form
 */
function quickcheck_form_shortcode($atts = [], $tag_content = null, $tag = '') : string {

	$atts = is_array($atts) ? $atts : [];

	$atts = array_change_key_case($atts, CASE_LOWER); // normalise attribute keys to lowercase

	// override default attributes with user attributes
	$settings = shortcode_atts(
		[
			'minlength' => 3,
			'maxlength' => 255,
		],
		$atts,
		$tag
	);

	$settings = apply_filters('quickcheck_shortcode_arguments', $settings);

	$field_content = '';
	if(!empty($_POST['content'])){
		$field_content = stripslashes($_POST['content']); // obtain the submitted content and strip slashes added by PHP
	}

	$field_submitted = false;
	if(!empty($_POST['submitted'])){
		$field_submitted = true;
	}

	$response = null;
	if($field_submitted){
		$response = quickcheck_form_submit($settings['minlength'], $settings['maxlength']);

		if($response->success){
			$field_content = '';// clear the content field on success
			do_action('quickcheck_submission_success', $response);
		}
	}

	// Enqueue necessary styles/scripts
	wp_enqueue_style('quickcheck-form');
	wp_enqueue_script('quickcheck-form');

	/**
	 * Build the form HTML
	 * 
	 * This might look a bit of a weird approach if you've never had the pleasure of 
	 * learning any low-level languages but it's the most performant way of building large strings
	 */
	$html = [];
	$html[] = '<div class="quickcheck">';

		$html[] = '<form class="quickcheck__form" id="quickcheck-form" method="post" action="">';

			if(!empty($tag_content)){
				$html[] = '<div class="quickcheck__description">';
					$html[] = wp_kses_post($tag_content);
				$html[] = '</div>';
			}

			if(!is_null($response)){
				$html[] = (string)$response;
			}

			$html[] = '<div class="quickcheck__field quickcheck__field--limit" data-limit-upper="';
			$html[] = esc_attr((string) $settings['maxlength']);
			$html[] = '" data-limit-lower="';
			$html[] = esc_attr((string) $settings['minlength']);
			$html[] = '">';
				$html[] = '<label for="content" class="quickcheck__label">';
					$html[] = __('Content:', 'quickcheck');
				$html[] = '</label>';
				$html[] = '<textarea name="content" id="content" class="quickcheck__input quickcheck__input--textarea">';
					$html[] = esc_textarea($field_content);
				$html[] = '</textarea>';
			$html[] = '</div>';

			$html[] = '<div class="quickcheck__field">';
				$html[] = '<button id="quickcheck-submit" type="submit" class="quickcheck__btn">'; // Mustn't be disabled in the HTML in case user has javascript disabled
					$html[] = __('Submit', 'quickcheck');
				$html[] = '</button>';
			$html[] = '</div>';

			$html[] = '<input type="hidden" name="submitted" value="1" />';

			$html[] = wp_nonce_field('quickcheck_form_submit', 'auth_token', true, false);

		$html[] = '</form>';

	$html[] = '</div>';

	return implode('', $html);

}
add_shortcode('qc_form', 'quickcheck_form_shortcode');

/**
 * Logic to be called after a successful form submission
 * 
 * @param QuickCheckFormResponse $response The response object from the form submission
 * @return void
 */
function quickcheck_form_submission_success(QuickCheckFormResponse $response) : void {
	// currently does nothing, but could be used to trigger other actions on successful submission
	// e.g. sending notification emails, logging, redirecting to a "thank you" page, etc.
}
add_action('quickcheck_submission_success', 'quickcheck_form_submission_success', 10, 1);

/**
 * Validate shortcode arguments
 * 
 * @param array $settings Shortcode settings/attributes
 * @return array Validated settings
 */
function quickcheck_shortcode_args_validate($settings){

	/**
	 * Ensure 'maxlength' attribute is valid:
	 * must be numeric
	 * can't be more than 255 as that's the length of the DB column
	 */
	if(empty($settings['maxlength']) || !is_numeric($settings['maxlength'])){
		$settings['maxlength'] = 255;
	} else {
		$settings['maxlength'] = (int) $settings['maxlength'];
		if($settings['maxlength'] > 255){
			$settings['maxlength'] = 255;
		}
	}

	/**
	 * Ensure 'minlength' attribute is valid:
	 * must be numeric
	 * must be greater than zero
	 * can't be 255 as that's the length of the DB column
	 * can't be more than maxlength
	 */
	if(empty($settings['minlength']) || !is_numeric($settings['minlength'])){
		$settings['minlength'] = 3;// default is 3 as that's what's in the brief
	} else {
		$settings['minlength'] = (int) $settings['minlength'];
		if($settings['minlength'] > $settings['maxlength']){
			$settings['minlength'] = $settings['maxlength'] - 1;
		}
		if($settings['minlength'] < 0){
			$settings['minlength'] = 0;
		}
	}

	return $settings;

}
add_filter('quickcheck_shortcode_arguments', 'quickcheck_shortcode_args_validate', 10, 1);

?>