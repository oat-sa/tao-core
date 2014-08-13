define(['module', 'jquery', 'ui/feedback'], function(module, $, feedback){
    'use strict';

    /**
     * Set focus on the login field.
     */
     document.getElementById('login').focus();

    for(var type in module.config()) {
        if(!module.config()[type]) {
            continue;
        }
        feedback()[type](module.config()[type]);
    }
});