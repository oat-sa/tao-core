/**
 * This class is used to render the generis facet filter widget in accordion mode

 * @see GenerisFacetFilterClass
 *
 * @require jquery >= 1.3.2 [http://jquery.com/]
 *
 * @author Alfonsi Cédric, <taosupport@tudor.lu>
 * @author Jehan Bihin (class)
 */

define(['require', 'jquery'], function(req, $) {
	var GenerisFacetFilterAccordionAdapter = Class.extend({
		init: function() {
			//
		},
		/**
		 * Render the header
		 */
		header: function() {
			var html = '<div class="accordion">';
			return html;
		},
		/**
		 * Render the footer
		 */
		footer: function() {
			var html = '</div>';
			return html;
		},
		/**
		 * Render the content
		 */
		content: function(id, label) {
			var html = '<h3><a href="#">' + label + '</a></h3> \
				<div style="padding:5px 0 5px 12px;"> \
					<div id="tree-' + id + '"></div> \
				</div>';
			return html;
		},
		/**
		 * Render
		 */
		render: function(selector, filterNodes) {
			var output = '';
			output += this.header();
			for (var i in filterNodes) {
				output += this.content(filterNodes[i].id, filterNodes[i].label);
			}
			output += this.footer();

			$(selector).append(output);
			//$(selector).find('.accordion').accordion({
			//	autoHeight: false,collapsible: true
			//});

			$(selector).find('.accordion')
					.addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")
					.find("h3")
					.addClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-top ui-corner-bottom")
					.hover(function() {
						$(this).toggleClass("ui-state-hover");
					})
					.prepend('<span class="ui-icon ui-icon-triangle-1-e"></span>')
					.click(
						function() {
							$(this).toggleClass("ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom")
								.find("> .ui-icon")
								.toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s")
								.end().next().toggleClass("ui-accordion-content-active")
								.slideToggle();
							return false;
						})
					.next()
					.addClass("ui-accordion-content  ui-helper-reset ui-widget-content ui-corner-bottom")
					.hide();
		}
	});

	return GenerisFacetFilterAccordionAdapter;
});