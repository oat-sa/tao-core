/**
 * @author Sam <sam@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['lodash', 'i18n'], function(_, __){

    /**
     * Defines the validation callback 
     * @callback IsValidCallback
     * @param {Boolean} isValid - wheter the value is valid or not
     */

    /**
     * The function called by a validator to validate a value
     * @callback Validate
     * @param {String|Boolean|Number} value - the value to validate
     * @param {IsValidCallbacl} callback - called with the validation result
     * @param {Object} [options] - additional options
     */

    /**
     * Validate with a regex pattern
     * @private
     * @param {String|Boolean|Number} value - the value to validate
     * @param {IsValidCallbacl} callback - called with the validation result
     * @param {Object} [options] - additional options
     * @param {String} [options.modifier] - pattern modifier
     * @param {String} [options.pattern] - the pattern itself
     */
    var _validatePattern = function _validatePattern(value, callback, options){
        var regex = new RegExp(options.pattern, options.modifier || ''),
            match = value.match(regex),
            r = (match !== null);

        if(typeof(callback) === 'function'){
            callback.call(null, r);
        }
        return r;
    };

    /**
     * The current validators
     */
    var validators =  {

        numeric : {
            name : 'numeric',
            message : __('must be numeric'),
            options : {},
            validate : function(value, callback){
                var r = _.isNumber(value);
                if(typeof(callback) === 'function'){
                    callback.call(null, r);
                }
            }
        },

        notEmpty : {
            name : 'notEmpty',
            message : __('must not be empty'),
            options : {},
            validate : function(value, callback){
                var r;
                if(_.isNumber(value)){
                    r = true;
                }else{
                    r = !_.isEmpty(value);//works for array/object/string
                }
                if(typeof(callback) === 'function'){
                    callback.call(null, r);
                }
            }
        },

        pattern : {
            name : 'pattern',
            message : __('does not match'),
            options : {pattern : '', modifier : 'igm'},
            validate : _validatePattern
        },

        length : {
            name : 'length',
            message : __('required length'),
            options : {min : 0, max : 0},
            validate : function(value, callback, options){
                var r = false;
                if(value.length >= options.min){
                    if(options.max){
                        r = (value.length <= options.max);
                    }else{
                        r = true;
                    }
                }
                if(typeof(callback) === 'function'){
                    callback.call(null, r);
                }
            }
        },

        /* @todo qti stuffs to be removed from this location */
        qtiIdentifier : {
            name : 'qtiIdentifier',
            message : __('is not a valid IMS QTI identifier'),
            validate : function(value, callback){
                _validatePattern(value, callback, {pattern : '^[_a-z]{1}[a-z0-9-._]{0,31}$', modifier : 'i'});
            }
        }
    };

    /**
     * Register a new validator
     * @param {String} [name] - the validator name
     * @param {Object} validator - the validator
     * @param {String} validator.message - the failure message
     * @param {Function} validator.validate - the validator
     */
    var register = function registerValidator(name, validator){
        name = (typeof name === 'object' && name.name) ? name.name : name;
        validator = (typeof name === 'object') ? name : validator;

        if(!_.isString(name) || _.isEmpty(name)){
            throw new Error('Please name your validator');
        }

        if(!_.isObject(validator) || !_.isString(validator.message) || !_.isFunction(validator.validate)){
            throw new Error('A validator must be an object with a message and a validate method, but given : ' + JSON.stringify(validator));
        }

        //do not override
        if(!validators[name]){
            validators[name] = validator;
        }
    };

    /**
     * Gives access to the validator and enable to register new validators
     * @exports validator/validators 
     */
    return {
        validators: validators,
        register  : register
    };
});

