/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'lodash', 'i18n', 'core/mimetype', 'core/pluginifier', 'mediaElement'], function($, _, __, mimeType, Pluginifier){
    'use strict';

    var ns = 'previewer';
    var dataNs = 'ui.' + ns;

    //the plugin defaults
    var defaults = {
        containerClass : 'previewer'
    };

    var previewGenerator = {
        placeHolder     : _.template("<p class='nopreview' data-type='${type}'>${desc}</p>"),
        youtubeTemplate : _.template("<video preload='none'><source type='video/youtube' src='${url}'/></video>"),
        videoTemplate   : _.template("<video src='${url}' type='${mime}'></video>"),
        audioTemplate   : _.template("<audio src='${url}' type='${mime}'></audio>"),
        imageTemplate   : _.template("<img src='${url}' alt='${name}' />"),
        pdfTemplate     : _.template("<object data='${url}#toolbar=0' type='application/pdf'><a href='${url} target='_blank'>${name}</a></object>"),
        flashTemplate   : _.template("<object data='${url}' type='application/x-shockwave-flash'><param name='movie' value='${url}'></param></object>"),
        mathmlTemplate  : _.template("<iframe src='${url}'></iframe>"),

        /**
         * Generates the preview tags for a type 
         * @memberOf previewGenerator 
         * @param {String} type - the file type
         * @param {Object} data - the preview data (url, desc, name)
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
         * @example $('selector').previewer({ url : 'test.mp4', type : 'video/mp4' });
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
                } else {
                    $elt.previewer('update', options);
                }
            });
        },

        /**
         * Update the preview
         * @example $('selector').previewer('update', {url: 'foo.mp3', type : 'audio/mp3'});
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
           var type = options.type || mimeType.getFileType({ mime : options.mime, name : options.url});
           var content;
           if(options.url){
           
               if(!options.name){
                    options.name = options.url.substring(options.url.lastIndexOf("/") + 1, options.url.lastIndexOf("."));
                }
                console.log(type, options);
                content = previewGenerator.generate(type, options);
                console.log(content);
           }
           if(!content){
                content = previewGenerator.placeHolder(_.merge({desc : __('No preview available')}, options));
           }
           $content = $(content);
           
           $elt.empty().html($content);
          
           if(options.width){
                $content.attr('width', options.width);
           }
           if(options.height){
                $content.attr('height', options.height);
           }
           if(type === 'audio' || type === 'video'){
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
                url     : $elt.data('preview'),
                type    : $elt.data('preview-type'),
                mime    : $elt.data('preview-mime'),
                width   : $elt.width(),
                height  : $elt.height() 
            });
        });
    };
});

