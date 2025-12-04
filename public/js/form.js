
/**
 * Fetches the latest entries via AJAX, calling the provided callback with the results
 * 
 * @param {Function} callback 
 * @return {void}
 */
function quickcheck_latest_entries(callback){

	const url = quickcheck.ajax_url;
	const data = {
		action: 'quickcheck_latest',
		auth_token: quickcheck.auth_token
	};
	fetch(url, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
		},
		body: new URLSearchParams(data)
	}).then((response) => {
		return response.json();
	}).then((data) => {
		if(typeof(callback) === 'function'){
			callback(data);
		} else {
			console.log(data);
		}
	}).catch((error) => {
		console.error('Error fetching latest entries:', error);
	});

}

/**
 * Disables submit button if any input is out of limits
 * 
 * Checks all fields with limit requirements rather than just the subject of the event - better practice for extensibility
 * 
 * @return {void}
 */
function quickcheck_limit_check(event){
	
	const input = event.target;
	const field = input.closest('.quickcheck__field--limit');

	if(field){

		const limit_upper = parseInt(field.dataset.limitUpper);

		(($) => { // brief explicitly says to use jQuery here

			$counter = $(field).find('.quickcheck__counter');
			$counter.removeClass('quickcheck__counter--invalid');
			if(input.value.length > limit_upper){ // add invalid class if over limit
				// (don't check if under limit as user may be heading in the direction of validity so no need to interupt them)
				$counter.addClass('quickcheck__counter--invalid');
			}
			$counter.html(input.value.length + '/' + limit_upper); // update the character count

		})(jQuery);
	
	}

	quickcheck_limit_check_all();

}
/**
 * Disables submit button if any input is out of limits
 * 
 * @return {void}
 */
function quickcheck_limit_check_all(){

	let disable_btn = false;
	
	// loop through all fields, checking limits and updating disable_btn as needed
	const limit_fields = document.querySelectorAll('.quickcheck__field--limit');
	limit_fields.forEach((field) => {
		const limit_upper = parseInt(field.dataset.limitUpper);
		const limit_lower = parseInt(field.dataset.limitLower);
		const input = field.querySelector('.quickcheck__input');
		if(input){
			if(input.value.length > limit_upper || input.value.length < limit_lower){
				disable_btn = true;
			}
		}
	});

	const submit = document.getElementById('quickcheck-submit');
	if(submit){
		submit.disabled = disable_btn;
	}

}

/**
 * Initializes quickcheck form JS functionality
 * 
 * @return {void}
 */
function quickcheck_load(){
	
	const limit_fields = document.querySelectorAll('.quickcheck__field--limit');
	limit_fields.forEach((field) => {
		const input = field.querySelector('.quickcheck__input');
		if(input){
			input.addEventListener('input', quickcheck_limit_check);

			// create counter element if not present
			// it's better if it's not present as the functionality relies on JS
			let counter = field.querySelector('.quickcheck__counter');
			if(!counter){
				counter = document.createElement('div');
				counter.classList.add('quickcheck__counter');
				field.append(counter);
			}
			while(counter.firstChild){
				counter.removeChild(counter.firstChild);
			}
			counter.append(document.createTextNode(input.value.length + '/' + field.dataset.limitUpper));

		}
	});

	quickcheck_limit_check_all();

}

(() => {

	const submit = document.getElementById('quickcheck-submit');
	if(submit){
		submit.disabled = true;
	}

	window.addEventListener('load', quickcheck_load);

})();