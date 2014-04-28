define([
'jquery',
'lodash',
'tpl!ui/resourcemgr/layout'],
function($, _, layout){
    'use strict';

    var ns = 'resourcemgr';

    return function($container, path){
        var $filePreview  = $('.file-preview', $container); 

        $('.select', $filePreview).on('click', function(e){
            e.preventDefault();
            console.log('select button clicked');
            $container.trigger('select.' + ns, [['/test/123/rammstein.ogg']]);
        });
    };
});
