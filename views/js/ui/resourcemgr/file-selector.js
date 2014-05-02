define([
    'jquery',
    'lodash',
    'i18n',
    'tpl!ui/resourcemgr/fileSelect',
    'ui/uploader' 
], function($, _, __, fileSelectTpl, uploader){
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
   
    //the previewer will show the resource regarding it's mime/type, willcards are supported for subtypes
   var mimeMapping = [
        { type : 'youtube',   mimes : ['video/youtube'] },
        { type : 'video',   mimes : ['application/ogg', 'video/*'] },
        { type : 'audio',   mimes : ['audio/*'] },
        { type : 'image',   mimes : ['image/*'] },
        { type : 'pdf',     mimes : ['application/pdf'] }, 
        { type : 'flash',   mimes : ['application/x-shockwave-flash'] },
        { type : 'mathml',  mimes : ['application/mathml+xml'] },
        { type : 'xml'  ,   mimes : ['application/xml'] },
        { type : 'html',    mimes : ['text/html'] },
        { type : 'text',     mimes : ['text/*'] }
    ]; 

    /**
     * Get the type from a mimeType regarding the mimeMapping above
     * @private
     * @param {String} mime - type/mime
     * @returns {String} type
     */
    var getFileType = function getType(mime){
        var fileType;
        var result = _.where(mimeMapping, { mimes : [mime]});
        if(result.length === 0){
             result = _.where(mimeMapping, { mimes : [mime.replace(/\/.*$/, '/*')]});
        }
        if(result.length > 0){
            return result[0].type;
        }
    };

    return function(options, root){

        var $container = options.$target;
        var $fileSelector = $('.file-selector', $container); 
        var $fileContainer = $('.files', $fileSelector);
        var $uploadContainer = $('.uploader', $fileSelector);
        var liveSelector = '#' + $container.attr('id') + ' .file-selector'; 

        //update current folder
        var $pathTitle = $fileSelector.find('h1 > .title');
        $container.on('folderselect.' + ns , function(e, fullPath, data){    
            
            //update title
            $pathTitle.text(isTextLarger($pathTitle, fullPath) ? shortenPath(fullPath) : fullPath); 

            //update content here
            if(_.isArray(data)){
                var files = _.filter(data, function(item){
                    return !!item.name;
                }).map(function(file){
                    file.type = getFileType(file.mime);
                    file.path = (fullPath + '/' + file.name).replace('//', '/');
                    file.downloadUrl = options.downloadUrl + '?' +  $.param(options.params) + '&' + options.pathParam + '=' + file.path;
                    return file; 
                });
            
                updateFiles(fullPath, files); 
            }
        });

        //listen for file activation
        $(document).on('click', liveSelector + ' .files li', function(e){
            
            var $selected = $(this); 
            var $files = $('.files > li', $fileSelector);
            var selection = {};

            if(!$.contains($selected.find('.actions')[0], e.target)){
                e.preventDefault();
            }

            if($selected.hasClass('active')){   
                $files.removeClass('active');
            } else {
                $files.removeClass('active');
                $selected.addClass('active');
                selection = $selected.data();
            }
             
            $container.trigger('fileselect.' + ns, [selection]); 
        });

        //select a file
        $(document).on('click', liveSelector + ' .files li a.select', function(e){
            e.preventDefault();
            $container.trigger('select.' + ns, [[$(this).parents('li').data('file')]]);
        });

        //delete a file
        $fileContainer.on('delete.deleter', function(e, $target){
            var file;
            if(e.namespace === 'deleter' && $target.length){
                file = $target.data('file');
                $(this).one('deleted', function(){

                    //TODO connect DELETE HERE
                    //console.log(file + ' deleted');
                });
            }
        });
       

        //TODO move upload where the path is correct 
        var $uploader =  $('.file-upload', $fileSelector);
        $uploader.on('upload.uploader', function(e, file, result){
            //console.log('UPLOAD', arguments);
        });
        $('.file-upload', $fileSelector).uploader({
            upload : true,
            uploadUrl : options.uploadUrl + '?' +  $.param(options.params) + '&' + options.pathParam + '=/' 
        });

        //siwtch to upload mode
        $('.upload-switcher a', $fileSelector).click(function(e){
            e.preventDefault();

            if($fileContainer.css('display') === 'none'){
                $uploadContainer.hide();
                $fileContainer.show();
                $(this).html('<span class="icon-add"></span>' + __('Upload'));
            } else {
                $fileContainer.hide();
                $uploadContainer.show();
                $(this).html('<span class="icon-undo"></span>' + __('Files'));
            }
        }); 


        function updateFiles(path, files){
            $fileContainer.empty().append(fileSelectTpl({
                files : files
            })); 
        }
    };
});
