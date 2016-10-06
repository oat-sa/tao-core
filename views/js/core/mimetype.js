/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'lodash', 'json!core/mimetype/categories.json', 'json!core/mimetype/extensions.json'], function($, _, categories, extensions){
    'use strict';

    /**
     * Helps you to retrieve file type and categories based on a file mime type
     * @exports core/mimetype
     */
    var mimetypeHelper = {

        /**
         * Gets the MIME type of a resource.
         *
         * @param {String} url - The URL of the resource to get type of
         * @param {Function} [callback] - An optional function called when the response is received.
         *                                This callback must accept 2 arguments:
         *                                the first is the potential error if the request failed,
         *                                the second is the MIME type if the request succeed.
         * @returns {mimetype}
         */
        getResourceType : function getResourceType(url, callback) {
            $.ajax({
                type: "HEAD",
                async: true,
                url: url,
                success: function onSuccess(message, text, jqXHR) {
                    var mime = jqXHR.getResponseHeader('Content-Type');
                    if (callback) {
                        callback(null, mime);
                    }
                },

                error: function onError(jqXHR) {
                    var error = jqXHR.status || 404;
                    if (callback) {
                        callback(error);
                    }
                }
            });
            return this;
        },

        /**
         * Get the type from a mimeType regarding the mimeMapping above
         * @param {Object} file - the file
         * @param {String} [file.mime] - the mime type
         * @param {String} [file.name] - the file name
         * @returns {String} the type
         */
        getFileType : function getFileType(file){
            var type;
            var mime = file.mime;
            var extMatch, ext;

            if(mime){
                //lookup for exact mime
                type = _.findKey(categories, { mimes : [mime]});

                //then check  with star
                if(!type){
                    type = _.findKey(categories, { mimes : [mime.replace(/\/.*$/, '/*')]});
                }
            }

            //try by extension
            if(!type){
                ext = getFileExtension(file.name);
                if(ext){
                    type = _.findKey(categories, { extensions : [ext]});
                }
            }

            return type;
        },

        /**
         * Get the category of a type
         * @param {String} type
         * @returns {String} category
         */
        getCategory : function getCategory(type){
            if(categories[type]){
                return categories[type].category;
            }
        },
        /**
         * Get mime type from a File object
         * It first based the detection on the standard type File.type property
         * If the returned type is empty or in a generic application/octet-stream, it will use its extension.
         * If the extension is unknown, the property File.type is returned anyway.
         * 
         * @param {File} file
         * @returns {String} the mime type
         */
        getMimeType : function getMimeType(file){
            var ext,
                type = file.type,
                category = mimetypeHelper.getFileType({
                    name : file.name,
                    mime : type
                });

            if(type && !type.match(/invalid/) && category !== 'generic'){
                return type;
            }else{
                ext = getFileExtension(file.name);
                if(ext && extensions[ext]){
                    return extensions[ext];
                }
            }
            return type;
        }

    };

    /**
     * Get the file extension from the file name
     *
     * @param {String} fileName
     * @returns {String}
     */
    function getFileExtension(fileName){
        var extMatch  = fileName.match(/\.([0-9a-z]+)(?:[\?#]|$)/i);
        if(extMatch && extMatch.length > 1){
            return extMatch[1];
        }
    }

    return mimetypeHelper;
});
