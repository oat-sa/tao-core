/**
 * Jehan Bihin
 * Autogrow for textarea
 */
$.fn.autogrow = function() {
	return this.each(function() {
	$(this).before('<div style="width: '+$(this).width()+'px; display: none;" class="helper"></div>');
	$(this).keyup(function(event) {
		$(this).prev('.helper').html($(this).val().replace(/\r\n|\r|\n/g, '<br>'));
		$(this).height($(this).prev().height() + 18 + "px");
		}).keyup();
	});
};