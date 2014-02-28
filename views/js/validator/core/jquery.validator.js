define(['jquery', 'lodash', 'core.validator'], function($, _, Validator){


    $.fn.validator = function(options){

        var opts = {};
        var method = '';
        var args = [];
        var ret = undefined;
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
        'elementClass' : 'itemBody'
    };
    
    function isCreated($elt){
        return (typeof $elt.data('validator-options') === 'object');
    }
    
    var methods = {
        validate : function(options, callback){
            validate($(this), options, callback);
        },
        getValidator:function(){
            return $(this).data('validator-object');
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

    
        var rules = [],//rules, *ordered* rules to be executed
            rulesStr,
            validateStr = $elt.data('validate');

        if(validateStr){

            rulesStr = validateStr.split(/;\s+/);

            //get name (and options) for every rules strings:
            _.each(rulesStr, function(ruleStr){

                var ruleName,
                    ruleOptions = {},
                    rightStr = ruleStr.replace(/\s*\$(\w*)/, function($0, name){
                    ruleName = name;
                    return '';
                });

                if(ruleName){
                    rightStr.replace(/\s*\(([^\)]*)\)/, function($0, optionsStr){
                        optionsStr.replace(/(\w*)=([^\s]*)(,)?/g, function($0, optionName, optionValue){
                            if(optionValue.charAt(optionValue.length - 1) === ','){
                                optionValue = optionValue.substring(0, optionValue.length - 1);
                            }
                            ruleOptions[optionName] = optionValue;
                        });
                    });

                    rules.push({
                        name : ruleName,
                        options : ruleOptions
                    });
                }

            });
        }

        return rules;
    };

    var create = function($elt, options){

        $elt.data('validator-options', options);

        var rules = buildRules($elt);
        if(options.rules){
            _.merge(rules, options.rules);
        }

        bindRules($elt, rules);
    };

    var bindRules = function($elt, rules){
        $elt.data('validator-object', new Validator(rules));
    };

    var validate = function($elt, options, callback){
        $elt.each(function(){
            
            var elt=this,
                $el = $(elt),
                value = $el.val();

            $elt.data('validator-object').validate(value, options || {}, function(results){
                $el.trigger('validated', {elt:elt, results:results});
                if(_.isFunction(callback)){
                    callback.call(elt, results);
                }
            });
        });
    };

});