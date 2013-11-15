var callbackMeWhenReady = {};
var helpers;
var uiBootstrap;
var eventMgr;
var uiForm;
var generisActions;

require([taobase_www + 'js/config/config'], function(){
    'use strict';
    
    require(['jquery', 'class', 'uiBootstrap', 'helpers', 'EventMgr', 'uiForm', 'generis.actions', 'jqueryUI', 'i18n'], 
    function ($, Class, UiBootstrap, Helpers, EventMgr, UiForm, GenerisActions) {

            $(function(){
                    helpers = new Helpers();
                    uiBootstrap = new UiBootstrap();
                    eventMgr = new EventMgr();
                    uiForm = new UiForm();
                    generisActions = new GenerisActions();
                    for (var e in callbackMeWhenReady) {
                            callbackMeWhenReady[e]();
                    }
            });
    });

});