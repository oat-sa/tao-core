/**
 * Set focus on the login field.
 */
function focusFirstField() {
	$('input[name="login"]').focus();
}

$(document).ready(function() {
	focusFirstField();	
});