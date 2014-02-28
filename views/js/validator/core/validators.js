define(['lodash', 'i18n'], function(_, __){

    var _validatePattern = function(value, callback, options){
        var regex = new RegExp(options.pattern, options.modifier || ''),
            match = value.match(regex),
            r = (match !== null);

        if(typeof(callback) === 'function'){
            callback.call(null, r);
        }
        return r;
    };

    return {
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
            options : {min : 0, max:0},
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
        qtiIdentifier : {
            name : 'qtiIdentifier',
            message : __('is not a valid IMS QTI identifier'),
            validate : function(value, callback){
                _validatePattern(value, callback, {pattern : '^[_a-z]{1}[a-z0-9-._]{0,31}$', modifier : 'i'});
            }
        }
    };
});

