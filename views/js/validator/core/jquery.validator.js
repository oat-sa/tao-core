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
        'elementClass' : 'itemBody'
    };
    
    function isCreated($elt){
        return (typeof $elt.data('validator-config') === 'object');
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

        var rulesStr = $elt.data('validate'),
            rules = rulesStr ? tokenize(rulesStr) : {};
    
        return rules;
    };
    
    var tokenize = function(inputStr){
        
        var ret = [],//return object
            tokens;
        
        var tokens = inputStr.split(/;\s+/);
        
        //get name (and options) for every rules strings:
        _.each(tokens, function(token){

            var key,
                options = {},
                rightStr = token.replace(/\s*\$(\w*)/, function($0, k) {
                    key = k;
                    return '';
                });

            if (key) {
                rightStr.replace(/\s*\(([^\)]*)\)/, function($0, optionsStr) {
                    optionsStr.replace(/(\w*)=([^\s]*)(,)?/g, function($0, optionName, optionValue) {
                        if (optionValue.charAt(optionValue.length - 1) === ',') {
                            optionValue = optionValue.substring(0, optionValue.length - 1);
                        }
                        options[optionName] = optionValue;
                    });
                });

                ret.push({
                    name: key,
                    options: options
                });
            }

        });
        
        return ret;
    };
    
    var buildOptions = function($elt){
        var optionsStr = $elt.data('validate-option'),
            options = optionsStr ? tokenize(optionsStr) : {};
        
        console.log(options);
        //separate core.validator options from jquery.validator options
        
        return options;
    };
    
    var create = function($elt, config){

        $elt.data('validator-config', config);

        var rules = buildRules($elt);
        if(config.rules){
            _.merge(rules, config.rules);
        }
        
        var options = buildOptions($elt);
        if (config.options) {
            _.merge(options, config.options);
        }
        
        createValidator($elt, rules, options);
    };

    var createValidator = function($elt, rules, options){
        $elt.data('validator-object', new Validator(rules, options));
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