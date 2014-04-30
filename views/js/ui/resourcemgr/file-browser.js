define(['jquery', 'lodash'], function($, _) {
    'use strict';

    var ns = 'resourcemgr';



    return function(options){


        var $container = options.$target;
        var $fileBrowser = $('.file-browser', $container);  
        var $folderContainer = $('.folders', $fileBrowser);

        var fileTree = {};
        showContent(fileTree, '/', function(content){
            console.log('1 content', content);        
            console.log('1 fileTree', fileTree);

            showContent(fileTree, '/images', function(content){
                console.log('2 content', content);        
                console.log('2 fileTree', fileTree);

                  
            });
              
        });
/*
, function(data){

            //file browser
            var $folders = $('.folders li', $fileBrowser);
            $folders.on('click', 'a', function(e){
                e.preventDefault();
               
                //TODO move active on a elements 
                var $selected = $(this);                
                $folders.removeClass('active');
                $selected.parent('li').addClass('active');
        
                //get full path 
                //var $parent = $selected;
                //var path = '/';
                //var i = 512;
                //do{
                    //$parent = $parent.parent();
                    //if($parent.is('li')){
                        //path = '/' + $parent.children('a').text() +  path;
                    //}
                    //if($parent.hasClass('file-browser')){
                        //break;
                    //} 
                //} while(true && i--);

                $container.trigger('folderselect.' + ns , [data]);
            });

            $container.trigger('folderselect.' + ns , [data]);
        });
*/

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

        function getContent(path, subtree, recurse){
            var parameters = {};
            parameters[options.pathParam] = path;
            $.getJSON(options.browseUrl, _.merge(parameters, options.params)).done(function(data){
                if(!subtree){
                    subtree = data;
                }
                if(data.children && _.isArray(data.children)){
                    if(recurse){
                        subtree.children = data.children;
                    }
                    _.forEach(data.children, function(child, index){
                        if(child.path){
                            getContent(child.path, subtree.children[index], true);
                        } 
                    });
                }
            });
        }



        //updateFolders(data, $folderContainer);
        function updateFolders(data, $parent){
           if(data.children && _.isArray(data.children)){
                if(!$parent.hasClass('folders')){
                    $parent = $('<ul><ul>').appendTo($parent);     
                }           
                _.forEach(data.children, function(child){
                    updateFolders(child, $parent);
                });
           } 
           if(data.path && data.path !== '/'){
                $parent.append('<li><a href="#">' + data.path.replace(/\//, '') + '</a></li>');
           }
        }
    };
});
