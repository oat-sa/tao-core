define([
'jquery',
'lodash',
'tpl!ui/resourcemgr/fileSelect'],
function($, _, fileSelectTpl){
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

    return function($container, path){

        var $fileSelector = $('.file-selector', $container); 
        var $fileContainer = $('.files', $fileSelector);

        //update current folder
        var $pathTitle = $fileSelector.find('h1 > .title');
        $container.on('folderselect.' + ns , function(e, fullPath, data){    

            console.log(arguments);

            //update title
            $pathTitle.text(isTextLarger($pathTitle, fullPath) ? shortenPath(fullPath) : fullPath); 

            //update content here
            if(_.isArray(data)){
                var files = _.filter(data, function(item){
                    return !!item.name;
                }).map(function(file){
                    file.type = getFileType(file.mime);
                    return file; 
                });
                updateFiles(files); 
            }
        });

        var $files = $('.files > li', $fileSelector);
        $files.click(function(e){
            e.preventDefault();
            var $selected = $(this);                
            $files.removeClass('active');
            $selected.addClass('active');
            
            $container.trigger('fileselect.' + ns, [$selected.data('file')]); 
        });

        function updateFiles(files){
            $fileContainer.empty().append(fileSelectTpl({
                files : files    
            })); 
        }
    };
});
