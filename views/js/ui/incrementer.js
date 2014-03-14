/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @requires jquery
 * @requires core/pluginifier
 */
define(['jquery', 'lodash', 'core/pluginifier'], function($, _, Pluginifier){
    'use strict';

    var ns = 'incrementer';
    var dataNs = 'ui.' + ns;

    var defaults = {
        step : 1,
        min : null,
        max : null,
        incrementerClass : 'incrementer',
        incrementerCtrlClass : 'incrementer-ctrl',
        decimal : 0
    };

    /** 
     * The Incrementer component, it transforms a text input in an number input, the data-attr way 
     * (has the HTML5 number input type is not yet very well supported, we don't use polyfill to have a consistent UI) 
     * @exports ui/incrementer
     */
    var Incrementer = {
        /**
         * Initialize the plugin.
         * 
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').incrementer({step : 1, min : 0, max : 12 });
         * @public
         * 
         * @constructor
         * @param {Object} [options] - the plugin options
         * @param {Number} [options.step = 1] - the increment step
         * @param {Number} [options.min] - the minimum value
         * @param {Number} [options.max] - the maximum value
         * @returns {jQueryElement} for chaining
         */
        init : function(options){
            var self = Incrementer;

            //get options using default
            options = _.defaults(options || {}, defaults);

            return this.each(function(){
                var $elt = $(this);
                var $ctrl, currentValue;

                if(!$elt.data(dataNs)){

                    //basic type checking
                    if(!$elt.is('input[type="text"]')){
                        $.error('The incrementer plugin applies only on input element of type text');
                    }else{
                        currentValue = parseFloat($elt.val()).toFixed(options.decimal);
                        $elt.data(dataNs, options)                      //add data to the element
                            .addClass(options.incrementerClass)         //add the css class
                            .after(//set up controls
                            "<div class='ctrl " + options.incrementerCtrlClass + "'>\
                                <a href='#' class='inc' title='+' tabindex='-1'></a>\
                                <a href='#' class='dec' title='-' tabindex='-1'></a>\
                              </div>")
                            .on('keydown', function(e){
                            if(e.which === 38){                      //up
                                self._inc($elt);
                                this.select();
                            }else if(e.which === 40){               //down
                                self._dec($elt);
                                this.select();
                            }
                        })
                            .on('keyup', function(){
//                            var val = $elt.val();
//                            if(val.slice(-1) !== '.'){
//                                var currentFloat = parseFloat(val);
//                                if(self._decimalPlaces(currentFloat) > options.decimal){
//                                    val = self._toFixedDown(currentFloat, options.decimal);
//                                }
//                            }
//                            $elt.val(val);
                            $elt.val($elt.val().replace(/[\D]/g, ''));
                        })
                            .on('focus', function(){
                            this.select();
                        })
                            .on('disable', function(){
                            $ctrl.find('.inc,.dec').prop('disabled', true)
                                .addClass('disabled');
                        })
                            .on('enable', function(){
                            $ctrl.find('.inc,.dec').prop('disabled', false)
                                .removeClass('disabled');
                        });

                        //set up the default value if needed
                        if(_.isNaN(currentValue)
                            || (options.min !== null && currentValue < options.min)
                            || (options.max !== null && currentValue > options.max)){
                            $elt.val(options.min || 0);
                        }

                        $ctrl = $elt.next('.' + options.incrementerCtrlClass);

                        $ctrl.find('.inc').click(function(e){
                            e.preventDefault();
                            if(!$(this).prop('disabled')){
                                self._inc($elt);
                            }
                        });
                        $ctrl.find('.dec').click(function(e){
                            e.preventDefault();
                            if(!$(this).prop('disabled')){
                                self._dec($elt);
                            }
                        });

                        /**
                         * The plugin have been created.
                         * @event Incrementer#create.incrementer
                         */
                        $elt.trigger('create.' + ns);
                    }
                }
            });
        },
        _toFixedDown : function(number, precision){
            var m = Math.pow(10, precision||0);
            return Math.floor(number * m) / m;
        },
        _decimalPlaces : function(number){
            var match = ('' + number).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
            if(!match){
                return 0;
            }
            return Math.max(0, (match[1] ? match[1].length : 0) - (match[2] ? +match[2] : 0));
        },
        /**
         * Increment value
         * 
         * @private
         * @param {jQueryElement} $elt - plugin's element 
         * @fires Incrementer#plus.incrementer
         */
        _inc : function($elt){

            var options = $elt.data(dataNs),
                currentFloat = parseFloat($elt.val()),
                stepDecimal = Incrementer._decimalPlaces(options.step),
                current,
                value;

            if(Incrementer._decimalPlaces(currentFloat) > stepDecimal){
                current = Incrementer._toFixedDown(currentFloat, stepDecimal);
            }else{
                current = parseFloat(currentFloat.toFixed(options.decimal));
            }
            var value = current + options.step;
            if(options.max === null || (_.isNumber(options.max) && value <= options.max)){
                $elt.val(value);

                /**
                 * The target has been toggled. 
                 * @event Incrementer#increment.incrementer
                 */
                $elt.trigger('increment.' + ns, [value]).trigger('change');
            }
        },
        /**
         * Decrement value
         * 
         * @private
         * @param {jQueryElement} $elt - plugin's element 
         * @fires Incrementer#minus.incrementer
         */
        _dec : function($elt){

            var options = $elt.data(dataNs),
                currentFloat = parseFloat($elt.val()),
                stepDecimal = Incrementer._decimalPlaces(options.step),
                current,
                value;

            if(Incrementer._decimalPlaces(currentFloat) > stepDecimal){
                value = Math.floor(currentFloat * Math.pow(10, stepDecimal)) / Math.pow(10, stepDecimal);
            }else{
                current = parseFloat(currentFloat.toFixed(options.decimal));
                value = current - options.step;
            }

            if(options.min === null || (_.isNumber(options.min) && value >= options.min)){
                $elt.val(value);

                /**
                 * The target has been toggled. 
                 * @event Incrementer#decrement.incrementer
                 */
                $elt.trigger('decrement.' + ns, [value]).trigger('change');
            }
        },
        /**
         * Destroy completely the plugin.
         * 
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').incrementer('destroy');
         * @public
         */
        destroy : function(){
            this.each(function(){
                var $elt = $(this);
                var options = $elt.data(dataNs);
                $elt.off('keyup keydown')
                    .siblings('.' + options.incrementerCtrlClass).remove();

                /**
                 * The plugin have been destroyed.
                 * @event Incrementer#destroy.incrementer
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
    };

    //Register the incrementer to behave as a jQuery plugin.
    Pluginifier.register(ns, Incrementer);

    /**
     * The only exposed function is used to start listening on data-attr
     * 
     * @public
     * @example define(['ui/incrementer'], function(incrementer){ incrementer($('rootContainer')); });
     * @param {jQueryElement} $container - the root context to listen in
     */
    return function listenDataAttr($container){

        $container.find('[data-increment]').each(function(){
            var $elt = $(this);
            var decimal = Incrementer._decimalPlaces($elt.attr('data-increment'));
            var step = parseFloat($elt.attr('data-increment'));
            var min, max;

            var options = {};
            if(!_.isNaN(step)){
                options.step = step;
            }
            if(!_.isNaN(decimal)){
                options.decimal = decimal;
            }
            if($elt.attr('data-min')){
                min = parseFloat($elt.attr('data-min'));
                if(!_.isNaN(min)){
                    options.min = min;
                }
            }
            if($elt.attr('data-max')){
                max = parseFloat($elt.attr('data-max'));
                if(!_.isNaN(max)){
                    options.max = max;
                }
            }
            $elt.incrementer(options);
        });
    };
});

