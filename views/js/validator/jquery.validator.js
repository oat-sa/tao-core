define(['jquery', 'lodash', 'core.validator'], function($, _, Validator){


    $.fn.validator = function(options){

        var opts = {},
            method = '',
            args = [],
            ret = undefined;

        if(typeof options === 'object'){
            opts = $.extend({}, $.fn.validator.defaults, options);
        }else if(options === undefined){
            opts = $.extend({}, $.fn.validator.defaults);//use default
        }else if(typeof options === 'string'){
            if(typeof methods[options] === 'function'){
                method = options;
                args = Array.prototype.slice.call(arguments, 1);
            }
        }

        this.each(function(){
            var $this = $(this);
            if(!isCreated($this)){
                create($this, opts);
            }
            if(method){
                if(isCreated($this)){
                    ret = methods[method].apply($(this), args);
                }else{
                    $.error('call of method of validator when it is not initialized');
                }
            }
        });

        if(ret === undefined){
            return this;
        }else{
            return ret;
        }
    };

    $.fn.validator.defaults = {
        'validator' : {
            lazy : false
        },
        'validated' : function(results){
            //manage results here, this = DOM element, modify visual only necessary
        }
    };

    function isCreated($elt){
        return (typeof $elt.data('validator-config') === 'object');
    }

    var methods = {
        destroy : function(){
            destroy($(this));
        },
        validate : function(arg1, arg2){
            var callback, options = {};

            //prepare args:
            if(_.isFunction(arg1)){
                callback = arg1;
            }else if(_.isObject(arg1)){
                _.merge(options, arg1);//treat it like the options array:
                if(_.isFunction(arg2)){
                    callback = arg2;
                }
            }

            //event the callback is optional, since we may set an event listener instead
            validate($(this), callback, options);
        },
        getValidator : function(){
            return $(this).data('validator-instance');
        }
    };


    /**
     * rule must have been set in the following string format:
     * $validatorName1; $validatorName2(optionName1=optionValue1, optionName2=optionValue2)
     * 
     * example:
     * $notEmpty; $pattern(pattern=[A-Z][a-z]{3,}, modifier=i); 
     * 
     * @param {type} $elt
     * @returns {object}
     */
    var buildRules = function($elt){

        var rulesStr = $elt.data('validate'),
            rules = rulesStr ? tokenize(rulesStr) : {};

        return rules;
    };

    var tokenize = function(inputStr){

        var ret = [], //return object
            tokens;

        var tokens = inputStr.split(/;\s+/);

        //get name (and options) for every rules strings:
        _.each(tokens, function(token){

            var key,
                options = {},
                rightStr = token.replace(/\s*\$(\w*)/, function($0, k){
                key = k;
                return '';
            });

            if(key){
                rightStr.replace(/\s*\(([^\)]*)\)/, function($0, optionsStr){
                    optionsStr.replace(/(\w*)=([^\s]*)(,)?/g, function($0, optionName, optionValue){
                        if(optionValue.charAt(optionValue.length - 1) === ','){
                            optionValue = optionValue.substring(0, optionValue.length - 1);
                        }
                        options[optionName] = optionValue;
                    });
                });

                ret.push({
                    name : key,
                    options : options
                });
            }

        });

        return ret;
    };

    var buildOptions = function($elt){

        var optionsStr = $elt.data('validate-option'),
            optionsArray = optionsStr ? tokenize(optionsStr) : {},
            availableCoreValidatorOptions = _.keys(Validator.getDefaultOptions()),
            options = _.clone($.fn.validator.defaults);

        //separate core.validator options from jquery.validator options
        _.each(optionsArray, function(optionArray){
            if(_.indexOf(availableCoreValidatorOptions, optionArray.name) >= 0){
                options.validator[optionArray.name] = optionArray.options;
            }else{
                options[optionArray.name] = optionArray.options;
            }
        });

        return options;
    };

    var create = function($elt, config){

        $elt.data('validator-config', $.extend({}, $.fn.validator.defaults, config));

        var rules = buildRules($elt);
        if(config.rules){
            _.merge(rules, config.rules);
        }

        var options = buildOptions($elt);
        if(config.options){
            _.merge(options, config.options);
        }

        createValidator($elt, rules, options);
    };

    var destroy = function($elts){
        $elts.removeData('validator-instance validator-config');
        $elts.off('.validator');
    };

    var createValidator = function($elt, rules, options){
        $elt.data('validator-instance', new Validator(rules, options.validator || {}));
        if(options.event){
            bindEvents($elt, options.event);
        }
    };

    var bindEvents = function($elt, options){

        var config = $elt.data('validator-config');

        if(_.isFunction(config.validated)){

            switch(options.type){
                case 'keyup':
                case 'keydown':

                    $elt.on(options.type, function(){
                        var v = $elt.val();
                        if(options.length){
                            if(v && v.length > options.length){
                                validate($elt, config.validated, {});
                            }
                        }else{
                            validate($elt, config.validated, {});
                        }
                    });
                    break;

                case 'change':
                case 'blur':

                    $elt.on(options.type, function(){
                        validate($elt, config.validated, {});
                    });
                    break;

                default:
                    $.error('unknown event type to be bound to validation : ' + options.type);
            }

        }

    };

    var validate = function($elt, callback, options){

        var value = $elt.val();
        $elt.data('validator-instance').validate(value, options || {}, function(results){

            //always trigger an event "validated" with associated results:
            $elt.trigger('validated', {elt : $elt[0], results : results});

            //call the callback function is given:
            if(_.isFunction(callback)){
                callback.call($elt[0], results);
            }
        });
    };

});