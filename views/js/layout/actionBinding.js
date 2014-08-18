define(['lodash'], function(_){
    return {
        _bindings : {},

        register : function(name, binding){
            this._bindings[name] = binding;
        },

        exec : function(name, $elt, data, done){
            if(_.isFunction(this._bindings[name])){
                this._bindings[name].call($elt, data, done);
            }
        }
    };
});
