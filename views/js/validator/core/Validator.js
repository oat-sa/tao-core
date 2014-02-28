define(['lodash', 'validator/core/Report', 'validator/core/validators'], function(_, Report, validators){

    var _rules = validators;

    var _buildRule = function(rule){
        var ret = null;
        if(_.isString(rule) && _rules[rule]){
            ret = _rules[rule];
        }else if(_.isObject(rule) && rule.name){
            if(_rules[rule.name]){
                ret = _.merge(_.cloneDeep(_rules[rule.name]), rule);
            }else if(rule.message && _.isFunction(rule.validate)){
                ret = rule;
            }
        }
        return ret;
    };

    var _defaultOptions = {};

    var _applyRules = function(value, rule, options){
        var r = null;
        options = _.merge(_.cloneDeep(rule.options), options);
        if(rule.validate(value, options) === false){//async should return "null"
            r = new Report('failure', {message : rule.message});
        }
        return r;
    };

    var Validator = function(rules, options){
        this.options = _.merge(_.cloneDeep(_defaultOptions), options);
        this.rules = [];
        this.addRules(rules);
    };

    Validator.prototype.validate = function(value, options, callback){

        var _this = this, results = [];

        options = _.merge(this.options, options || {});

        _.each(this.rules, function(rule){
            var report = _applyRules(value, rule);
            if(report !== null){
                results.push(report);
                if(options.stopOnFirst && !report.isError()){
                    return false;
                }
            }
        });

        if(_.isFunction(callback)){
            callback(results);
        }

        return results;
    };

    Validator.prototype.addRule = function(rule){
        if(_.isString(rule) && _rules[rule]){
            this.rules.push(_rules[rule]);
        }else if(rule = _buildRule(rule)){
            this.rules.push(rule);
        }
        return this;
    };

    Validator.prototype.addRules = function(rules){
        var _this = this;
        _.each(rules, function(rule){
            _this.addRule(rule);
        });
        return this;
    };

    return Validator;
});