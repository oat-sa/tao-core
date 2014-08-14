define(['module', 'jquery', 'ui/feedback'], function(module, $, feedback){
    'use strict';


    for(var type in module.config()) {
        if(!module.config()[type]) {
            continue;
        }
        feedback()[type](module.config()[type]);
    }
});