define([
    'jquery',
    'uiBootstrap',
    'helpers',
    'uiForm',
    'generis.actions',
    'controller/main/toolbar',
    'layout/version-warning'
],
    function ($, UiBootstrap, Helpers, UiForm, GenerisActions, toolbar, versionWarning) {

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

            // Playground: @todo rewrite this code decently
            setTimeout(function() {
                // set the focus always to the first text field in the first form
                $('.xhtml_form').first().find('input[type="text"]').first().focus();


            }, 3000);
        }
    };
});