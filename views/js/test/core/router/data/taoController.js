//mock controller module
define([], function(){
    'use strict';
    return {
        start : function start(){
            window.controllerStarted = window.controllerStarted || {};
            window.controllerStarted.tao = true;
        }
    };
});
