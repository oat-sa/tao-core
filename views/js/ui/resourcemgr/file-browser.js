define(['jquery', 'lodash'], function($, _) {
    'use strict';

    var ns = 'resourcemgr';

    return function($container, path){

        var $fileBrowser = $('.file-browser', $container);  

        //file browser
        var $folders = $('.folders li', $fileBrowser);

    console.log($folders);
        $folders.on('click', 'a', function(e){
            e.preventDefault();
           
            //TODO move active on a elements 
            var $selected = $(this);                
            $folders.removeClass('active');
            $selected.parent('li').addClass('active');
    
            //get full path 
            var $parent = $selected;
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

            $container.trigger('folderselect.' + ns , [path]);
        });
    };
});
