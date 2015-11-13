define(['jquery', 'lodash', 'core/dataattrhandler', 'qtip'], function($, _, DataAttrHandler){
    'use strict';

    var themes = ['dark', 'default', 'info', 'warning', 'error', 'success'],
        themesMap = {
            'default' : 'qtip-rounded qtip-plain',
            'dark' : 'qtip-rounded qtip-dark',
            'error' : 'qtip-rounded qtip-red',
            'success' :'qtip-rounded qtip-green',
            'info' : 'qtip-rounded qtip-blue',
            'warning' : 'qtip-rounded qtip-orange',
        };

    /**
     * Look up for tooltips and initialize them
     *
     * @public
     * @param {jQueryElement} $container - the root context to lookup inside
     */
    return function lookupSelecter($container){
        $('[data-tooltip]', $container).each(function(){
            var $elt = $(this),
                $target = DataAttrHandler.getTarget('tooltip', $elt),
                theme = _.contains(themes, $elt.data('tooltip-theme')) ? $elt.data('tooltip-theme') : 'default';

            $elt.qtip({
                position: {
                    my : 'bottom center',
                    at : 'top center',
                    viewport: $(window),
                },
                style: {
                    classes: themesMap[theme]
                },
                content: {
                    text: $target
                }
            });
        });
    };
});
