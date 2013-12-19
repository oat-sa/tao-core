define([
    'ui/toggler', 
    'ui/disabler', 
    'ui/adder', 
    'ui/closer', 
    'ui/incrementer', 
    'ui/inplacer', 
    'ui/btngrouper', 
    'ui/flipper',
    'ui/durationer'
], function(toggler, disabler, adder, closer, incrementer, inplacer, btngrouper, flipper, durationer) {
    
    
    /**
     * @author Bertrand Chevrier <bertrand@taotesting.com>
     * @exports cards
     */
     var cards = {
         
        /**
         * Start up the components lookup and data-attr listening 
         * @param {jQUeryElement} $container - to lookup within
         */
        start : function($container){
            adder($container);
            btngrouper($container);
            closer($container);
            disabler($container);
            toggler($container);
            incrementer($container);
            inplacer($container);
            flipper($container);
            durationer($container);
        }
    };
    
    return cards;
});
