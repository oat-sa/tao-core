define([
    'jquery',
    'lodash', 
    'core/pluginifier', 
    'tpl!ui/usermgr/tpl/layout'
], function($, _, Pluginifier, layout){

    'use strict';
    
    var ns = 'usermgr';
    var defaults = {
        'start': 0,
        'rows': 25
    };
    
    /** 
     * The UserMgr component makes you able to browse users and bind specific
     * actions to undertake for edition and removal of them.
     * 
     * @exports ui/usermgr
     */
    var userMgr = {
        
        /**
         * Initialize the plugin.
         * 
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').usermgr({});
         * 
         * @constructor
         * @param {Object} options - the plugin options
         * @param {String} options.url - the URL of the service used to retrieve the user resources.
         * @param {Function} options.edit - the callback function for user edition, with a single parameter representing the identifier of the user.
         * @param {Function} options.remove - the callback function for user removal, with a single parameter representing the identifier of the user.
         * @fires UserMgr#create.usermgr
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

                userMgr._query($elt, options, data);
            });
        },
        
        _query: function($elt, options, data) {
            
            $.ajax({
                url: options.url,
                data: data,
                type: 'GET'
            }).done(function(response) {
                
                var $rendering = $(layout(response));
                var $edits = $rendering.find('.icon-edit');
                var $removes = $rendering.find('.icon-result-nok');
                
                for (var i = 0; i < response.records; i++) {
                    $edits.eq(i).click(function() {
                        $editElt = $(this);
                        options.edit.apply($editElt, [$editElt.parent().data('user-identifier')]);
                    });
                    
                    $removes.eq(i).click(function() {
                        $removeElt = $(this);
                        options.remove.apply($removeElt, [$removeElt.parent().data('user-identifier')]);
                    });
                }
                
                // Now $rendering takes the place of $elt...
                var $forwardBtn = $rendering.find('.usermgr-forward');
                var $backwardBtn = $rendering.find('.usermgr-backward');
                
                $forwardBtn.click(function() {
                    userMgr._next($rendering, options, data);
                });
                
                $backwardBtn.click(function() {
                   userMgr._previous($rendering, options, data); 
                });
                
                if (data.page == 1) {
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
            userMgr._query($elt, options, data);
        },
        
        _previous: function($elt, options, data) {
            data.page -= 1;
            userMgr._query($elt, options, data);
        }
    };
    
    Pluginifier.register(ns, userMgr);
});