define([
'jquery',
'tpl!ui/resourcemgr/layout'],
function($, layout){
    'use strict';

    return function($container, path){

        var $fileBrowser, $fileSelector, $filePreview;

        $container.append(layout());

        $fileBrowser = $('.file-browser', $container);  
        $fileSelector = $('.file-selector', $container); 
        $filePreview  = $('.file-preview', $container); 

        $container.on('selected.resourcemgr', function(e, path){    
            $fileSelector.children('h1').text(path); 
        });

        $('ul a', $fileBrowser).click(function(e){
            e.preventDefault();
        
            var $parent = $(this);
            var path = '/';
            var i = 512;
            do{
                $parent = $parent.parent();
                if($parent.is('li')){
                    path = '/' + $parent.children('a').text() +  path;
                }
                if($parent.hasClass('file-browser')){
                    break;
                } 
            } while(true && i--);
            $container.trigger('selected.resourcemgr', [path]);

        });
    };
});
