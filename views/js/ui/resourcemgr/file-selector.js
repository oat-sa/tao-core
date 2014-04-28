define([
'jquery',
'lodash',
'tpl!ui/resourcemgr/layout'],
function($, _, layout){
    'use strict';

    var ns = 'resourcemgr';

    function shortenPath(path){
        var tokens = path.replace(/\/$/, '').split('/');
        var size = tokens.length - 1;
        return _.map(tokens, function(token, index){
            return (token && index < size) ? token[0] : token;
        }).join('/');
    }

    function isTextLarger($element, text){
        var $dummy = $element
                        .clone()
                        .detach()
                        .css({
                            position: 'absolute',
                            visibility: 'hidden',
                            'text-overflow' : 'clip',
                            width: 'auto'
                        })
                        .text(text)
                        .insertAfter($element);
        var textSize = $dummy.width();
        $dummy.remove();

        return textSize > $element.width();
    }

    return function($container, path){

        var $fileSelector = $('.file-selector', $container); 

        //update current folder
        var $pathTitle = $fileSelector.children('h1');
        $container.on('folderselect.' + ns , function(e, path){    
            //update title
            $pathTitle.text(isTextLarger($pathTitle, path) ? shortenPath(path) : path); 

            //update content here
        });

        var $files = $('.files > li', $fileSelector);
        $files.click(function(e){
            e.preventDefault();
            var $selected = $(this);                
            $files.removeClass('active');
            $selected.addClass('active');
            
            $container.trigger('fileselect.' + ns, [$selected.data('file')]); 
        });
    };
});
