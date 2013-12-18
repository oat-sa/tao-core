//loading the main config isn't usefull since we just need jquery
require(['lib/jquery-1.8.0.min'], function() {
    'use strict';

    /**
     * Set focus on the login field.
     */
    function focusFirstField() {
        $('input[name="login"]').focus();
    }
    
    //dom ready
    $(function() {
        focusFirstField();
    });
});
