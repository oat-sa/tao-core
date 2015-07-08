define(['jquery'], function($){
    'use strict';

    /**
     * Toggle radios and checkboxes wrapped into a pseudo label element to simulate the behavior of a label
     * @param {String} selector - to scope the listening
     */
    var pseudoLabel = function pseudoLabel(selector){

        $(document).on('click', selector + ' .pseudo-label-box', function (e) {
            $box.find('input').trigger('click');
        });
    };

   /**
    * Manages general behavior on form elements
    * 
    * @param {jQueryElement} $container - the root context to lookup inside
    */
    return function listenFormBehavior($container){
        var selector = $container.selector || '.tao-scope'; 

        pseudoLabel(selector);
    };
});
