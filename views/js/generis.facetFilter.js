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
			this.lists = [];

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
			template:	'hbox',	//hbox, vbox, accordion
			callback: {
				onFilter: null
			},
			itemActions: {}
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
		 * 		=> create a list
		 */
		addFilterNode: function(filterNode) {
			/*var self = this;
			/*var filterNodeId = filterNode.id;
			var filterNodeLabel = filterNode.label;
			var filterNodeUrl = filterNode.url;
			var filterNodeOptions = filterNode.options;

			//instantiate the filter node widget

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
			});*/

			listOptions = {
				elem: $('#list-'+filterNode.id),
				id: filterNode.id,
				url: filterNode.url,
				options: filterNode.options
			};
			this.lists[filterNode.id] = listOptions;
			this.loadDataList(listOptions);
		},

		loadDataList: function(listOptions) {
			var self = this;
			$.ajax({
				type: "POST",
				url: listOptions.url,
				data: listOptions.options,
				dataType: 'json',
				success: function(data) {
					$('#list-'+listOptions.id).empty().append('<ul id="'+data.attributes.id+'" class="group-list"></ul>');
					for (c in data.children) {
						$el = $('<li id="'+data.children[c].attributes.id+'" class="selectable"><span class="label">'+data.children[c].data+'</span><span class="selector checkable"></span></li>').appendTo($('#list-'+listOptions.id+' ul.group-list'));
						$('span.label, span.selector', $el).on('click', function(e){
							e.preventDefault();
							if ($(this).parent().hasClass('have-allaccess')) $(this).parent().removeClass('have-allaccess');
							else $(this).parent().addClass('have-allaccess');
							self.propagateChoice();
						});
						//Add actions
						$('<ul class="actions"></ul>').prependTo('span:first', $el);
						for (a in self.options.itemActions) {
							$a = $('<li class="actions '+a+'" style="backgroud-image: url('+self.options.itemActions[a].iconUrl+')"></li>').appendTo('ul.actions', $el);
							$a.on('click', self.options.itemActions[a].callback.click);
						}
					}
				}
			});
		},

		/**
		 * Propagate a choice
		 */
		propagateChoice: function() {
			var filter = {};
			//get the checked nodes
			for (var id in this.lists) {
				//var checked = this.lists[id].getChecked();
				var checked = [];
				$('li.selectable.have-allaccess', this.lists[id].elem).each(function(idx, el) {
					checked.push($(this).prop('id'));
				});
				if (checked.length) filter[$('ul', $(this.lists[id].elem)).prop('id')] = checked;
			}

			//refresh all lists with the new filter
			for (var id in this.lists) {
				// Set the server parameter
				//this.lists[id].setServerParameter('filter', filter, true);
				//Reload
				this.lists[id].options.filter = filter;
				this.loadDataList(this.lists[id]);
			}

			//call the callback function
			if (this.options.callback.onFilter != null) {
				this.options.callback.onFilter(filter, this.filterNodesOptions);
			}
		},

		getFormatedFilterSelection: function() {
			var formatedFilter = {};
			for (var id in this.lists) {
				//var propertyUri = this.filterNodesOptions[id]['propertyUri'];
				//formatedFilter[propertyUri] = this.lists[id].getChecked();
				//var checked = this.lists[id].getChecked();
				var checked = [];
				$('li.selectable.have-allaccess', this.lists[id].elem).each(function(idx, el) {
					checked.push($(this).prop('id'));
				});
				if (checked.length) formatedFilter[$('ul', $(this.lists[id].elem)).prop('id')] = checked;
			}
			return formatedFilter;
		}
	});

	return GenerisFacetFilterClass;
});