define([
    'jquery',
    'jquery.cookie'
],
    function($){
    return {
        init : function(){
            var versionWarning = $('.version-warning'),
                closer = versionWarning.find('.close-trigger');

            closer.on('click', function() {
                $.cookie('versionWarning', true, { expires: 1000, path: '/' });
                versionWarning.slideUp('slow');
            });

            if($.cookie('versionWarning')) {
                setTimeout(function() {
                    closer.trigger('click');
                }, 2000);
            }
        }
    };
});


