define([
    'jquery',
    'uiBootstrap',
    'helpers',
    'uiForm',
    'generis.actions',
    'controller/main/toolbar',
    'layout/version-warning',
    'layout/section-height'
],
    function (
        $,
        UiBootstrap,
        Helpers, UiForm,
        GenerisActions,
        toolbar,
        versionWarning,
        sectionHeight
        ) {

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

            sectionHeight.init();


        }
    };
});

