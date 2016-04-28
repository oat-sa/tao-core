define([
    'ui/toggler', 
    'ui/disabler', 
    'ui/adder', 
    'ui/deleter', 
    'ui/incrementer', 
    'ui/inplacer', 
    'ui/btngrouper', 
    'ui/durationer',
    'ui/selecter',
    'ui/modal',
    'ui/tooltipster',
    'ui/form',
    'ui/validator',
    'ui/groupvalidator'
], function(toggler, disabler, adder, deleter, incrementer, inplacer, btngrouper, durationer, selecter, modal, tooltipster, form) {
    'use strict';

    function initCrossBrowserSvg() {

        // code taken from svg4everybody
        var newerIEUA = /\bTrident\/[567]\b|\bMSIE (?:9|10)\.0\b/;
        var webkitUA = /\bAppleWebKit\/(\d+)\b/;
        var olderEdgeUA = /\bEdge\/12\.(\d+)\b/;

        if(newerIEUA.test(navigator.userAgent) ||
            (navigator.userAgent.match(olderEdgeUA) || [])[1] < 10547 ||
            (navigator.userAgent.match(webkitUA) || [])[1] < 537){

            require(['lib/polyfill/svg4everybody/svg4everybody'], function(svg4everybody){
                svg4everybody();
            });
        }
    }
        
    /**
     * @author Bertrand Chevrier <bertrand@taotesting.com>
     * @exports ui
     */
     return {
         
        /**
         * Start up the components lookup and data-attr listening 
         * @param {jQueryElement} $container - to lookup within
         */
        startEventComponents : function($container){
            adder($container);
            btngrouper($container);
            deleter($container);
            disabler($container);
            toggler($container);
            inplacer($container);
            modal($container);
            form($container);
            this.startDomComponent($container);
        },
        
        startDomComponent : function($container){
            incrementer($container);
            durationer($container);
            selecter($container);
            tooltipster($container);
            initCrossBrowserSvg();
        }
    };
});
