define([
    'jquery',
    'lodash',
    'core/pluginifier',
    'tpl!ui/itemsmgr/tpl/layout'
], function($, _, Pluginifier, layout){

    'use strict';

    var ns = 'itemsmgr';
    var defaults = {
        'start': 0,
        'rows': 25
    };

    /**
     * The itemsMgr component makes you able to browse itemss and bind specific
     * actions to undertake for edition and removal of them.
     *
     * @exports ui/itemsmgr
     */
    var itemsMgr = {

        /**
         * Initialize the plugin.
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').itemsmgr({});
         *
         * @constructor
         * @param {Object} options - the plugin options
         * @param {String} options.url - the URL of the service used to retrieve the resources.
         * @param {Function} options.actions.xxx - the callback function for items xxx, with a single parameter representing the identifier of the items.
         * @fires itemsMgr#create.itemsmgr
         * @returns {jQueryElement} for chaining
         */
        init: function(options) {

            return this.each(function() {
                var $elt = $(this);

                options = _.defaults(options, defaults);

                var data = {
                    'rows': options.rows,
                    'page': 1,
                    'sidx': '',
                    'sord': 'asc'
                };

                itemsMgr._query($elt, options, data);
            });
        },

        _query: function($elt, options, data) {

            $.ajax({
                url: options.url,
                data: data,
                type: 'GET'
            }).done(function(response) {
                response.actions = _.keys(options.actions);
                var $rendering = $(layout(response));

                $rendering
                    .off('click', '.edit')
                    .on('click', '.edit', function(e){
                        e.preventDefault();
                        var $editElt = $(this);
                        options.actions.edit.apply($editElt, [$editElt.parent().data('items-identifier')]);
                    });
                $rendering
                    .off('click', '.remove')
                    .on('click', '.remove', function(e){
                        e.preventDefault();
                        var $removeElt = $(this);
                        options.actions.remove.apply($removeElt, [$removeElt.parent().data('items-identifier')]);
                    });

                // Now $rendering takes the place of $elt...
                var $forwardBtn = $rendering.find('.itemsmgr-forward');
                var $backwardBtn = $rendering.find('.itemsmgr-backward');

                $forwardBtn.click(function() {
                    itemsMgr._next($rendering, options, data);
                });

                $backwardBtn.click(function() {
                    itemsMgr._previous($rendering, options, data);
                });

                if (data.page === 1) {
                    $backwardBtn.attr('disabled', '');
                } else {
                    $backwardBtn.removeAttr('disabled');
                }

                if (response.page >= response.total) {
                    $forwardBtn.attr('disabled', '');
                } else {
                    $forwardBtn.removeAttr('disabled');
                }

                $elt.replaceWith($rendering);
            });
        },

        _next: function($elt, options, data) {
            data.page +=1;
            itemsMgr._query($elt, options, data);
        },

        _previous: function($elt, options, data) {
            data.page -= 1;
            itemsMgr._query($elt, options, data);
        }
    };

    Pluginifier.register(ns, itemsMgr);
});
