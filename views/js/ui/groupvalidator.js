define(['jquery', 'lodash', 'ui/validator'], function($, _){

    var defaults = {
        errorClass : 'error',
        errorMessageClass: 'validate-error',
        defaultEvent : ['change', 'blur']
    };

    /**
     * Register a plugin to validate a group of elements
     * 
     * @example $('form').groupValidator();
     * @exports validator/jquery.groupvalidator
     * 
     * @param {Object} options - the plugin options
     * @param {string} [options.errorClass = 'error'] - the class added to the element itself if the validation fails
     * @param {string} [options.errorMessageClass = 'validate-error'] - the class added to the inserted node that contains the failure message itself
     * @param {string|Array} [options.defaultEvent = ['change', 'blur']] - the default event that triggers the validation
     * @fires validated.group
     * @returns {jQueryElement} for chaining
     */
    $.fn.groupValidator = function(options){
    
        options = _.defaults(options || {}, defaults);
        
        return this.each(function(){
            var $container = $(this);
            var states = [];
            var events = _.map(options.defaultEvent, function(event){
                return {type : event};
            });
            
            $('[data-validate]', $container).validator({
                event: events,
                validated : function(valid, results){
                    var $elt = $(this);
                    var message, rule;
            
                    //update global state
                    states[$(this).attr('name')] = valid;

                    //removes previous error messages
                    $elt.siblings('.' + options.errorMessageClass).remove();

                    if(valid === false){
                        rule = _.where(results, {type: 'failure'})[0];
                        $elt.addClass(options.errorClass);
                        if(rule && rule.data.message){
                            $elt.after("<span class='" + options.errorMessageClass + "'>" + rule.data.message + "</span>"); 
                        }
                    } else {
                        $elt.removeClass(options.errorClass);
                    }
    
                    /**
                     * Gives the validation state of the entire group. 
                     * Fired at each validation
                     * @event validated.group
                     * @param {boolean} isValid - wheter the group is valid 
                     */
                    $container.trigger('validated.group', [_(states).values().contains(false) === false]);
                 }
           });
        });
    };
});
