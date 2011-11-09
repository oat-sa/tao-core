/**
 * This class is used to render the generis facet filter widget in box mode
 * Horizontal or vertical

 * @see GenerisFacetFilterClass
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * 
 * @author Alfonsi Cédric, <taosupport@tudor.lu>
 */

/**
 * The GenerisFacetFilterHboxAdapter constructor
 * @param {String} selector the jquery selector of the tree container
 * @param {Object} options {}
 */
function GenerisFacetFilterHboxAdapter(){}

/**
 * Render the header
 */ 
GenerisFacetFilterHboxAdapter.prototype.header = function()
{
	var html = '<div>';
	return html;
}

/**
 * Render the footer
 */ 
GenerisFacetFilterHboxAdapter.prototype.footer = function()
{
	var html = '</div>';
	return html;
}

/**
 * Render the content
 */ 
GenerisFacetFilterHboxAdapter.prototype.content = function(id, label)
{
	var html = '<div class="ui-widget ui-facet-filter-node ui-helper-horizontal"> \
		<div class="ui-state-default ui-widget-header ui-corner-top container-title" > \
			' + label + ' \
		</div> \
		<div class="ui-widget-content container-content ui-corner-bottom"> \
			<div id="tree-' + id + '"></div> \
		</div> \
		<!--<div class="ui-state-default ui-corner-bottom" style="text-align:center; padding:4px;"> \
		</div>--> \
	</div>';
	return html;
}

/**
 * Render
 */ 
GenerisFacetFilterHboxAdapter.prototype.render = function(selector, filterNodes)
{
	output = '';
	
	output += this.header();
	for(var i in filterNodes){
		output += this.content(filterNodes[i].id, filterNodes[i].label);
	}
	output += this.footer();
	
	$(selector).append(output);
	var width = 100/Object.keys(filterNodes).length -1;
	$(selector).find('.ui-facet-filter-node').css('width', width+'%');
};
