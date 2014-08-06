define([
    'uiBootstrap',
    'helpers',
    'uiForm',
    'generis.actions',
    'controller/main/toolbar',
    'controller/main/version-warning'
],
    function (UiBootstrap, Helpers, UiForm, GenerisActions, toolbar, versionWarning) {

    return {
        start : function(){
            
            //initialize legacy components
            UiBootstrap.init();
            Helpers.init();
            UiForm.init();
            GenerisActions.init();
            
            //initialize main components
            toolbar.setUp();

            versionWarning.init();
        }
    };
});