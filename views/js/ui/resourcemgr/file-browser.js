define(['jquery', 'lodash'], function($, _) {
    'use strict';

    var ns = 'resourcemgr';

    return function(options, root){


        var $container = options.$target;
        var liveSelector = '#' + $container.attr('id') + ' .file-browser'; 
        var $fileBrowser = $('.file-browser', $container);  
        var $folderContainer = $('.folders', $fileBrowser);
        var fileTree = {};
        
        root = root || '/';

        $folderContainer.append('<li><a class="root-folder" href="#">' + root + '</a></li>');

        showContent(fileTree, root, function(content){
             var $innerList = $('<ul></ul>').insertAfter($('.root-folder', $folderContainer));     
             updateFolders(content, $innerList); 
             $container.trigger('folderselect.' + ns , [root, content.children]);
        });

        console.log( liveSelector + ' .folders  a');
        
        $(document).on('click', liveSelector + ' .folders  a', function(e){
            e.preventDefault();
            var $selected = $(this); 
            var $folders = $('.folders li', $fileBrowser);
            var fullPath = $selected.data('path');
            var subTree = getByPath(fileTree, fullPath);

            $folders.removeClass('active');
            $selected.parent('li').addClass('active');
                        
 
            showContent(subTree, fullPath, function(content){
                 var $innerList = $selected.siblings('ul');
                 if(!$innerList.length && content.children && _.find(content.children, 'path')){
                    $innerList = $('<ul></ul>').insertAfter($selected);     
                    updateFolders(content, $innerList);
                    $selected.addClass('opened');
                 } else if($innerList.length){
                    if($innerList.css('display') === 'none'){
                        $innerList.show();
                        $selected.addClass('opened');
                    } else {
                        $innerList.hide();
                        $selected.removeClass('opened');
                    } 
                 }
                 $container.trigger('folderselect.' + ns , [fullPath, content.children]);
            });
        });
    
        function getFullPathFromList($parent){
            var i = 512;
            var fullPath = '';
            do{
                $parent = $parent.parent();
                if($parent.is('li')){
                    fullPath = $parent.children('a').text() +   fullPath;
                }
                if($parent.hasClass('file-browser')){
                    break;
                } 
            } while(true && i--);
            if(fullPath.length > 1){
                fullPath = fullPath.replace(/\/$/, '');
            }
            return fullPath;
        }

        function showContent(tree, path, cb){
            var content = getByPath(tree, path);
            if(!content || (!content.children && !content.empty)){
                loadContent(path).done(function(data){
                    if(!tree.path){
                        tree = _.merge(tree, data);
                    } else if (data.children) {
                        setToPath(tree, path, data.children);
                    } else {
                        tree.empty = true;
                    }
                    cb(data);
                });
            } else {
                cb(content);
            }
        } 

        function getByPath(tree, path){
            var match;
            if(tree){
                if(tree.path === path){
                    match = tree;
                } else if(tree.children){
                   _.forEach(tree.children, function(child){
                        match = getByPath(child, path);
                        if(match){
                            return false;
                        }
                   });
                }
            }
            return match;
        }

        function setToPath(tree, path, data){
            var done = false;
            if(tree){
                if(tree.path === path){
                    tree.children = data;
                } else if(tree.children){
                   _.forEach(tree.children, function(child){
                        done = setToPath(child, path, data);
                        if(done){
                            return false;
                        }
                    });
                }
            }
            return done;
        }

        function loadContent(path){
            var parameters = {};
            parameters[options.pathParam] = path;
            return $.getJSON(options.browseUrl, _.merge(parameters, options.params));
        }

        //updateFolders(data, $folderContainer);
        function updateFolders(data, $parent, recurse){
           var $item;
           if(recurse && data.path){
                $item = $('<li><a data-path="' + data.path + '" href="#">' + data.path.split('/').pop() + '</a></li>').appendTo($parent);
           }
           if(data.children && _.isArray(data.children)){
                 //if(!$parent.hasClass('folders')){
                    //$parent = $('<ul><ul>').insertAfter($('a', $item));     
                 //}           
                _.forEach(data.children, function(child){
                    updateFolders(child, $parent, true);
                });
           } 
        }
    };
});
