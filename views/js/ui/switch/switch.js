define([
    'jquery',
    'ui/component',
    'tpl!ui/switch/switch',
    'css!taoCss/component/switch.css'
], function($, component, switchTpl){
    'use strict';

    var defaultConfig = {
        //default configuration
    };

    return function switchFactory(){
        var api = {
            //component methods
        };
        return component(api, defaultConfig)
                .setTemplate(switchTpl)
                .on('render', function(){
                    var $component = this.getElement();
                    $component.on('click', 'span', function(e){
                        e.preventDefault();
                        $('span', $component).toggleClass('active');
                    });
                });
    };
});
