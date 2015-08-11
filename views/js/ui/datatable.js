define([
    'jquery',
    'lodash',
    'i18n',
    'core/pluginifier',
    'tpl!ui/datatable/tpl/layout'
], function($, _, __, Pluginifier, layout){

    'use strict';

    var ns = 'datatable';

    var dataNs = 'ui.' + ns;

    var defaults = {
        'start'   : 0,
        'rows': 25,
        'page': 1,
        'sortby': 'id',
        'sortorder': 'asc',
        'model'   : null,
        'actions' : null
    };

    /**
     * The CSS class used to hide an element
     * @type {String}
     */
    var hiddenCls = 'hidden';

    /**
     * The dataTable component makes you able to browse itemss and bind specific
     * actions to undertake for edition and removal of them.
     *
     * @exports ui/datatable
     */
    var dataTable = {

        /**
         * Initialize the plugin.
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').datatable({});
         *
         * @constructor
         * @param {Object} options - the plugin options
         * @param {String} options.url - the URL of the service used to retrieve the resources.
         * @param {Function} options.actions.xxx - the callback function for items xxx, with a single parameter representing the identifier of the items.
         * @param {Boolean} options.selectable - enables the selection of rows using checkboxes.
         * @param {Object} options.data - inject predefined data to avoid the first query.
         * @param {Object} options.tools - a list of tool buttons to display above the table
         * @param {Object|Boolean} options.status - allow to display a status bar
         * @param {Object|Boolean} options.filter - allow to display a filter bar
         * @fires dataTable#create.datatable
         * @returns {jQueryElement} for chaining
         */
        init: function(options) {

            var self = dataTable;
            options = _.defaults(options, defaults);

            return this.each(function() {
                var $elt = $(this);

                if(!$elt.data(dataNs)){

                    //add data to the element
                    $elt.data(dataNs, options);

                    $elt.one('load.' + ns , function(){
                        /**
                         * @event dataTable#create.datatable
                         */
                        $elt.trigger('create.' + ns);
                    });

                    if (options.data) {
                        self._render($elt, options.data);
                    } else {
                        self._query($elt);
                    }
                } else {
                    self._refresh($elt);
                }
            });
        },

        /**
         * Refresh the data table using current options
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').datatable('refresh');
         *
         * @param {jQueryElement} $elt - plugin's element
         */
        _refresh : function($elt){
            this._query($elt);
        },

        /**
         * Query the server for data and load the table.
         *
         * @private
         * @param {jQueryElement} $elt - plugin's element
         * @fires dataTable#query.datatable
         */
        _query: function($elt){
            var self = this;
            var options = $elt.data(dataNs);
            var parameters = _.merge({},_.pick(options, ['rows', 'page', 'sortby', 'sortorder']), options.params || {});
            var ajaxConfig = {
                url: options.url,
                data: parameters,
                dataType : 'json',
                type: options.querytype || 'GET'
            };

            // add current filter if any
            if (options.filter && options.filter.value) {
                ajaxConfig.data.filter = options.filter.value;
            }

            /**
             * @event dataTable#query.dataTable
             * @param {Object} ajaxConfig - The config object used to setup the AJAX request
             */
            $elt.trigger('query.datatable', [ajaxConfig]);

            // display the loading state
            if (options.status) {
                $elt.find('.loading').removeClass(hiddenCls);
            }

            $.ajax(ajaxConfig).done(function(response) {
                self._render($elt, response);
            });
        },

        /**
         * Renders the table using the provided data set
         *
         * @param {jQueryElement} $elt - plugin's element
         * @param {Object} dataset - the data set to render
         * @private
         * @fires dataTable#beforeload.datatable
         * @fires dataTable#load.datatable
         */
        _render: function($elt, dataset) {
            var self = this;
            var options = $elt.data(dataNs);
            var $rendering;
            var $statusEmpty;
            var $statusAvailable;
            var $statusCount;
            var $searchFilter;
            var $searchInput;
            var $searchBtn;
            var $forwardBtn;
            var $backwardBtn;
            var $sortBy;
            var $sortElement;
            var $checkAll;
            var $checkboxes;
            var $massActionBtns = $();
            var amount;

            dataset = dataset || {};

            // Add the list of custom actions to the data set for the tpl
            _(['actions', 'tools', 'status', 'filter']).forEach(function(prop) {
                if (options[prop]) {
                    dataset[prop] = options[prop];
                }
            });

            // Add the model to the data set for the tpl
            dataset.model = options.model;

            // Forward options to the data set
            dataset.selectable = !!options.selectable;
            if (dataset.rows) {
                options.rows = dataset.rows;
            }
            if (dataset.sortby) {
                options = this._sortOptions($elt, dataset.sortby, dataset.sortorder);
            }

            /**
             * @event dataTable#beforeload.dataTable
             * @param {Object} dataset - The data set object used to render the table
             */
            $elt.trigger('beforeload.datatable', [dataset]);

            // Call the rendering
            $rendering = $(layout(dataset));

            // the readonly property contains an associative array where keys are the ids of the items (lines)
            // the value can be a boolean (true for disable buttons, false to enable)
            // it can also bo an array that let you disable/enable the action you want
            // readonly = {
            //  id1 : {'view':true, 'delete':false},
            //  id2 : true
            //}
            _.forEach(dataset.readonly, function(values, id){
                if(values === true){
                    $('[data-item-identifier="'+id+'"] button', $rendering).addClass('disabled');
                }
                else if(values && typeof values === 'object'){
                    for (var action in values) {
                        if (values.hasOwnProperty(action)) {
                            if(values[action] === true){
                                $('[data-item-identifier="'+id+'"] button.'+action, $rendering).addClass('disabled');
                            }
                        }
                    }
                }
            });

            // Attach a listener to every action button created
            _.forEach(options.actions, function(action, name){
                var css;

                if (!_.isFunction(action)) {
                    name = action.id || name;
                    action = action.action || function() {};
                }

                css = '.' + name;

                $rendering
                    .off('click', css)
                    .on('click', css, function(e) {
                        var $btn = $(this);
                        e.preventDefault();
                        if (!$btn.hasClass('disabled')) {
                            action.apply($btn, [$btn.closest('[data-item-identifier]').data('item-identifier')]);
                        }
                    });
            });

            // Attach a listener to every tool button created
            _.forEach(options.tools, function(action, name) {
                var massAction = false;
                var css;

                if (!_.isFunction(action)) {
                    name = action.id || name;
                    massAction = action.massAction;
                    action = action.action || function() {};
                }

                css = '.tool-' + name;
                if (massAction) {
                    $massActionBtns = $massActionBtns.add($rendering.find(css));
                }

                $rendering
                    .off('click', css)
                    .on('click', css, function(e) {
                        var $btn = $(this);
                        e.preventDefault();
                        if (!$btn.hasClass('disabled')) {
                            action.apply($btn, [self._selection($elt)]);
                        }
                    });
            });

            // Now $rendering takes the place of $elt...
            $forwardBtn = $rendering.find('.datatable-forward');
            $backwardBtn = $rendering.find('.datatable-backward');
            $sortBy = $rendering.find('th[data-sort-by]');
            $sortElement = $rendering.find('[data-sort-by="'+ options.sortby +'"]');
            $checkAll = $rendering.find('th.checkboxes input');
            $checkboxes = $rendering.find('td.checkboxes input');

            $forwardBtn.click(function() {
                self._next($elt);
            });

            $backwardBtn.click(function() {
                self._previous($elt);
            });

            $sortBy.click(function() {
                var column = $(this).data('sort-by');
                self._sort($elt, column);
            });

            // Add the filter behavior
            if (options.filter) {
                $searchFilter = $rendering.find('.filter');
                $searchInput = $('input' , $searchFilter);
                $searchBtn = $('button' , $searchFilter);

                // clicking the button trigger the request
                $searchBtn.off('click').on('click', function(e) {
                    e.preventDefault();
                    self._filter($elt, $searchInput.val());
                });

                // or press ENTER
                $searchInput.off('keypress').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        self._filter($elt, $searchInput.val());
                    }
                });
            }

            // check/uncheck all checkboxes
            $checkAll.click(function() {
                if (this.checked) {
                    $checkAll.attr('checked', 'checked');
                    $checkboxes.attr('checked', 'checked');
                } else {
                    $checkAll.removeAttr('checked');
                    $checkboxes.removeAttr('checked');
                }

                if ($massActionBtns.length) {
                    $massActionBtns.toggleClass('invisible', !$checkboxes.filter(':checked').length);
                }

                /**
                 * @event dataTable#select.dataTable
                 */
                $elt.trigger('select.datatable');
            });

            // when check/uncheck a box, toggle the check/uncheck all
            $checkboxes.click(function() {
                var $checked = $checkboxes.filter(':checked');
                if ($checked.length === $checkboxes.length) {
                    $checkAll.attr('checked', 'checked');
                } else {
                    $checkAll.removeAttr('checked');
                }

                if ($massActionBtns.length) {
                    $massActionBtns.toggleClass('invisible', !$checkboxes.filter(':checked').length);
                }

                /**
                 * @event dataTable#select.dataTable
                 */
                $elt.trigger('select.datatable');
            });

            // Remove sorted class from all th
            $('th.sorted',$rendering).removeClass('sorted');
            // Add the sorted class to the sorted element and the order class
            $sortElement.addClass('sorted').addClass('sorted_'+options.sortorder);

            if (dataset.page === 1) {
                $backwardBtn.attr('disabled', '');
            } else {
                $backwardBtn.removeAttr('disabled');
            }

            if (dataset.page >= dataset.total) {
                $forwardBtn.attr('disabled', '');
            } else {
                $forwardBtn.removeAttr('disabled');
            }

            // Update the status
            if (options.status) {
                $statusEmpty = $rendering.find('.empty-list');
                $statusAvailable = $rendering.find('.available-list');
                $statusCount = $statusAvailable.find('.count');

                $rendering.find('.loading').addClass(hiddenCls);

                // when the status is enabled, the response must contain the total amount of records
                amount = dataset.amount || dataset.length;
                if (amount) {
                    $statusCount.text(amount);
                    $statusAvailable.removeClass(hiddenCls);
                    $statusEmpty.addClass(hiddenCls);
                } else {
                    $statusEmpty.removeClass(hiddenCls);
                    $statusAvailable.addClass(hiddenCls);
                }
            }

            $elt.html($rendering);

            // if the filter is enabled and a value is present, set the focus on the input field
            if (options.filter && options.filter.value) {
                $searchInput.focus();
            }

            /**
             * @event dataTable#load.dataTable
             * @param {Object} dataset - The data set used to render the table
             */
            $elt.trigger('load.datatable', [dataset]);
        },

        /**
         * Query next page
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').datatable('next');
         *
         * @param {jQueryElement} $elt - plugin's element
         * @fires dataTable#forward.datatable
         */
        _next: function($elt) {
            var options = $elt.data(dataNs);

            //increase page number
            options.page += 1;

            //rebind options to the elt
            $elt.data(dataNs, options);

            /**
             * @event dataTable#forward.dataTable
             */
            $elt.trigger('forward.datatable');

            // Call the query
            this._query($elt);
        },

        /**
         * Query the previous page
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').datatable('previous');
         *
         * @param {jQueryElement} $elt - plugin's element
         * @fires dataTable#backward.datatable
         */
        _previous: function($elt) {
            var options = $elt.data(dataNs);
            if(options.page > 1){

                //decrease page number
                options.page -= 1;

                //rebind options to the elt
                $elt.data(dataNs, options);

                /**
                 * @event dataTable#backward.dataTable
                 */
                $elt.trigger('backward.datatable');

                // Call the query
                this._query($elt);
            }
        },

        /**
         * Query the current page with filter value
         *
         * @param {jQueryElement} $elt - plugin's element
         * @param {String} query - the filter value
         * @fires dataTable#filter.datatable
         * @private
         */
        _filter: function($elt, query) {
            var options = $elt.data(dataNs);

            //set the filter
            if (!_.isObject(options.filter)) {
                options.filter = {};
            }
            options.filter.value = query;

            //rebind options to the elt
            $elt.data(dataNs, options);

            /**
             * @event dataTable#sort.dataTable
             * @param {String} query - The filter value
             */
            $elt.trigger('sort.datatable', [query]);

            // Call the query
            this._query($elt);
        },

        /**
         * Query the previous page
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').datatable('sort', 'firstname', false);
         *
         * @param {jQueryElement} $elt - plugin's element
         * @param {String} sortBy - the model id of the col to sort
         * @param {Boolean} [asc] - sort direction true for asc of deduced
         * @fires dataTable#sort.datatable
         */
        _sort: function($elt, sortBy, asc) {
            /**
             * @event dataTable#sort.dataTable
             * @param {String} column - The name of the column to sort
             * @param {String} direction - The sort direction
             */
            $elt.trigger('sort.datatable', [sortBy, asc]);

            this._sortOptions($elt, sortBy, asc);
            this._query($elt);
        },

        /**
         * Set the sort options.
         *
         * @param {jQueryElement} $elt - plugin's element
         * @param {String} sortBy - the model id of the col to sort
         * @param {Boolean|String} [asc] - sort direction true for asc of deduced
         * @returns {Object} - returns the options
         * @private
         */
        _sortOptions: function($elt, sortBy, asc) {
            var options = $elt.data(dataNs);

            if (typeof asc !== 'undefined') {
                if ('asc' !== asc && 'desc' !== asc) {
                    asc = (!!asc) ? 'asc' : 'desc';
                }
                options.sortorder = asc;
            } else if (options.sortorder === 'asc' && options.sortby === sortBy) {
                // If I already sort asc this element
                options.sortorder = 'desc';
            } else {
                // If I never sort by this element or
                // I sort by this element & the order was desc
                options.sortorder = 'asc';
            }

            // Change the sorting element anyway.
            options.sortby = sortBy;

            //rebind options to the elt
            $elt.data(dataNs, options);
            return options;
        },

        /**
         * Gets the selected items. Returns an array of identifiers.
         *
         * @param {jQueryElement} $elt - plugin's element
         * @returns {Array} - Returns an array of identifiers.
         */
        _selection: function($elt) {
            var $selected = $elt.find('[data-item-identifier]').has('td.checkboxes input:checked');
            var selection = [];

            $selected.each(function() {
                selection.push($(this).data('item-identifier'));
            });

            return selection;
        }
    };

    Pluginifier.register(ns, dataTable, {
         expose : ['refresh', 'next', 'previous', 'sort', 'filter', 'selection', 'render']
    });
});
