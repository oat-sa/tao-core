define(['lodash', 'async', 'validator/core/Report', 'validator/core/validators'], function(_, async, Report, validators){

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

    var _applyRules = function(value, rule, options, callback){
        options = _.merge(_.cloneDeep(rule.options), options);
        rule.validate(value, options, callback);
    };

    var Validator = function(rules, options){
        this.options = _.merge(_.cloneDeep(_defaultOptions), options);
        this.rules = [];
        this.addRules(rules);
    };

    Validator.prototype.validate = function(value, options, callback){

        var callStack = [];

        options = _.merge(this.options, options || {});

        _.each(this.rules, function(rule){
            callStack.push(function(cb){
                _applyRules(value, rule, options, function(success){
                    if(success){
                        //continue;
                        cb(null, new Report('success', {message : rule.message}));
                    }else{
                        var report = new Report('failure', {message : rule.message});
                        if(options.stopOnFirst && !report.isError()){
                            cb(true, report);
                        }else{
                            cb(null, report);
                        }
                    }
                });
            });
        });
        
        async.series(callStack, function(err, results){
            if(_.isFunction(callback)){
                callback(results);
            }
        });

        return this;
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