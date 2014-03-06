define(['jquery', 'select2'], function($){
    'use strict';

    /**
     * Look up for element in the container that will be used as select2 widgets
     *
     * @public
     * @example define(['ui/selecter'], function(selecter){ selecter($('rootContainer')); });
     * @param {jQueryElement} $container - the root context to lookup inside
     */
    return function lookupSelecter($container) {
        $('select.select2', $container).each(function () {
            var $elt = $(this),
                hasSearch = false === $elt.data('has-search'),
                hasPlaceholder = !!($elt.attr('placeholder') || $elt.data('placeholder')),
                hasSelectedIndex = (function(options) {
                    var i = options.length,
                        selected = false;
                    while(i--) {
                        if(typeof (options[i].getAttribute('selected')) === 'string'){
                            selected = true;
                            break;
                        }
                    }
                    return selected;
                }(this.options)),
                settings = {
                    width: '100%'
                };
            if(hasPlaceholder && this.options[0].text) {
                $elt.prepend('<option>');
                if(!hasSelectedIndex) {
                    this.selectedIndex = this.options[0];
                }
            }
            if (hasSearch) {
                settings.minimumResultsForSearch = -1;
            }
            $elt.select2(settings);
        });
    };
});
