/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'lodash', 'i18n', 'core/pluginifier', 'mediaElement'], function($, _, __, Pluginifier){
    'use strict';

    var ns = 'previewer';
    var dataNs = 'ui.' + ns;

    //the plugin defaults
    var defaults = {
        containerClass : 'previewer'
    };

   //the previewer will show the resource regarding it's mime/type, willcards are supported for subtypes
   var mimeMapping = [
        { type : 'youtube',   mimes : ['video/youtube'] },
        { type : 'video',   mimes : ['application/ogg', 'video/*'] },
        { type : 'audio',   mimes : ['audio/*'] },
        { type : 'image',   mimes : ['image/*'] },
        { type : 'pdf',     mimes : ['application/pdf'] }, 
        { type : 'flash',   mimes : ['application/x-shockwave-flash'] },
        { type : 'mathml',  mimes : ['application/mathml+xml'] }
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

    var previewGenerator = {
        placeHolder     : _.template("<p class='nopreview' data-type='${type}'>${desc}</p>"),
        youtubeTemplate : _.template("<video preload='none'><source type='video/youtueb' src='${file}'/></video>"),
        videoTemplate   : _.template("<video src='${file}'></video>"),
        audioTemplate   : _.template("<audio src='${file}'></audio>"),
        imageTemplate   : _.template("<img src='${file}' alt='${name}' />"),
        pdfTemplate     : _.template("<object data='${file}#toolbar=0' type='application/pdf'><a href='${file} target='_blank'>${name}</a></object>"),
        flashTemplate   : _.template("<object data='${file}' type='application/x-shockwave-flash'><param name='movie' value='${file}'></param></object>"),
        mathmlTemplate  : _.template("<iframe src='${file}'></iframe>"),

        /**
         * Generates the preview tags for a type 
         * @memberOf previewGenerator 
         * @param {String} type - the file type
         * @param {Object} data - the preview data (file, desc, name)
         * @returns {String} the tags
         */
        generate : function generate(type, data) {
            var tmpl = this[type + 'Template'];
            if(_.isFunction(tmpl)){
                return tmpl(data); 
            }
         }
    };

    /**
     * @exports ui/previewer
     */
    var previewer = {
     
        /**
         * Initialize the plugin.
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').previewer({ file : 'test.mp4', type : 'video/mp4' });
         * @public
         *
         * @constructor
         * @param {Object} [options] - the plugin options
         * @returns {jQueryElement} for chaining
         */
        init : function(options){
            var self = previewer;       


            //get options using default
            options = _.defaults(options || {}, defaults);

            return this.each(function(){
                var $elt = $(this);
                if(!$elt.data(dataNs)){
            
                    if(!$elt.hasClass(options.containerClass)){
                        $elt.addClass(options.containerClass);
                    }

                    $elt.data(dataNs, options);
                    self._update($elt);

                    /**
                     * The plugin has been created.
                     * @event previewer#create.previewer
                     */
                    $elt.trigger('create.' + ns);
                }
            });
        },

        /**
         * Update the preview
         * @example $('selector').previewer('update', {file: 'foo.mp3', type : 'audio/mp3'});
         * @public
         * @param {Object} data - the new options for the preview
         * @returns {jQueryElement} for chaining
         */
        update : function(data){
            return this.each(function(){
                var $elt = $(this);
                var options = $elt.data(dataNs);
                $elt.data(dataNs, _.merge(options, data));
                previewer._update($elt);
            });
        },
      
        /**
         * Update the preview
         * @private
         * @param {jQueryElement} $elt - the current element
         */
        _update : function($elt){
           var $content, mep;
           var options = $elt.data(dataNs);
           var type = getFileType(options.type);
           var content = previewGenerator.placeHolder(_.merge({desc : __('No preview available')}, options));
           if(options.file){
                if(!options.name){
                    options.name = options.file.substring(options.file.lastIndexOf("/") + 1, options.file.lastIndexOf("."));
                }
                content = previewGenerator.generate(type, options);
           }
           $content = $(content);
           
           $elt.empty().html($content);
          
           if(options.width){
                $content.attr('width', options.width);
           }
           if(options.height){
                $content.attr('height', options.height);
           }
           if(type === 'audio' || type === 'video' || type === 'youtube'){
                $content.mediaelementplayer();
           }
            
            /**
             * The plugin has been created.
             * @event previewer#update.previewer
             */
            $elt.trigger('update.' + ns);
        },
 
        /**
         * Destroy completely the plugin.
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').previewer('destroy');
         * @public
         */
        destroy : function(){
            this.each(function(){
                var $elt = $(this);
                var options = $elt.data(dataNs);

                /**
                 * The plugin has been destroyed.
                 * @event previewer#destroy.previewer
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
    };

    //Register the incrementer to behave as a jQuery plugin.
    Pluginifier.register(ns, previewer);

    /**
     * The only exposed function is used to start listening on data-attr
     *
     * @public
     * @example define(['ui/previewer'], function(previewer){ previewer($('rootContainer')); });
     * @param {jQueryElement} $container - the root context to listen in
     */
    return function listenDataAttr($container){

        $container.find('[data-preview]').each(function(){
            var $elt = $(this);
            $elt.previewer({
                file    : $elt.data('preview'),
                type    : $elt.data('preview-type'),
                width   : $elt.width(),
                height  : $elt.height() 
            });
        });
    };
});

