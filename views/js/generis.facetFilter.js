/**
 * GenerisFacetFilterClass is a widget which provides easy screen to filter data by facet
 *
 * @example new GenerisFacetFilterClass('#facetfilter-container', {});
 * @see GenerisFacetFilterClass.defaultOptions for options example
 *
 * @require jquery >= 1.4.2 [http://jquery.com/]
 *
 * @author Alfonsi Cédric, <taosupport@tudor.lu>
 * @author Jehan Bihin (class)
 */

define(['require', 'jquery', 'generis.tree.select'], function(req, $, GenerisTreeSelectClass) {
	var GenerisFacetFilterClass = Class.extend({
		/**
		 * The GenerisFacetFilterClass constructor
		 * @param {String} selector the jquery selector of the tree container
		 * @param {Object} options {}
		 */
		init: function(selector, filterNodes, options) {
			this.options = $.extend(true, {}, this.defaultOptions, options);
			this.selector = selector;
			this.filterNodes = {};
			this.filterNodesOptions = {};
			this.trees = [];

			//If filter nodes
			if (filterNodes) {
				for (var i in filterNodes) {
					this.filterNodes[filterNodes[i]['id']] = filterNodes[i];
					this.filterNodesOptions[filterNodes[i]['id']] = filterNodes[i].options;
				}
				this.renderLayout();
			}
		},
		/**
		 * GenerisFacetFilterClass default options
		 */
		defaultOptions: {
			template:	'hbox',	// hbox, vbox, accordion
			callback: {
				onFilter: null
			}
		},
		//
		renderLayout: function() {
			var instance = this;

			require(['generis.facetFilter.'+this.options['template']], function(adapter) {
				var templateAdapter = new adapter();
				templateAdapter.render(instance.selector, instance.filterNodes);

				for (var i in instance.filterNodes) {
					instance.addFilterNode(instance.filterNodes[i]);
				}
			});
		},
		/**
		 * Add a filter node to the widget
		 * 		=> create a tree
		 */
		addFilterNode: function(filterNode) {
			var self = this;
			var filterNodeId = filterNode.id;
			var filterNodeLabel = filterNode.label;
			var filterNodeUrl = filterNode.url;
			var filterNodeOptions = filterNode.options;

			//instantiate the filter node widget, here a jstree

			//pass to the server options of others filter nodes
			var options = $.extend(true, {}, filterNodeOptions);
			var filterNodesOptions = $.extend(true, {}, self.filterNodesOptions);
			options['filterNodesOptions'] = filterNodesOptions;

			//instantiate the tree
			self.trees[filterNodeId] = new GenerisTreeSelectClass('#tree-'+filterNodeId, filterNodeUrl, {
				'actionId'			: 'filter',
				'serverParameters' 	: options,
				'onChangeCallback' 	: function(NODE, TREE_OBJ) {
					self.propagateChoice();
				}
			});
		},
		/**
		 * Propagate a choice
		 */
		propagateChoice: function() {
			var self = this;
			var filter = {};

			//get the checked nodes
			for (var treeId in this.trees) {
				var checked = this.trees[treeId].getChecked();
				filter[treeId] = checked;
			}
			//refresh all trees with the new filter
			for (var treeId in this.trees){
				// Set the server parameter
				this.trees[treeId].setServerParameter('filter', filter, true);
			}
			//call the callback function
			if(this.options.callback.onFilter != null){
				this.options.callback.onFilter(filter, this.filterNodesOptions);
			}
		},
		getFormatedFilterSelection: function() {

			var formatedFilter = {};
			for (var treeId in this.trees) {
				var propertyUri = this.filterNodesOptions[treeId]['propertyUri'];
				formatedFilter[propertyUri] = this.trees[treeId].getChecked(); 
			}
			
			return formatedFilter;
		}
	});

	return GenerisFacetFilterClass;
});
