/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery', 
    'lodash', 
    'i18n', 
    'core/pluginifier', 
    'context',
    'filereader'
], function($, _, __, Pluginifier, context){
    'use strict';

    var ns = 'uploader';
    var dataNs = 'ui.' + ns;

    //the plugin defaults
    var defaults = {
        containerClass      : 'file-upload',
        buttonClass         : 'btn-info',
        buttonIcon          : 'upload',
        buttonLabel         : __('Browse...'),
        fileNameClass       : 'file-name',
        fileNamePlaceholder : __('No file selected'),
        dropZoneClass       : 'file-drop',
        progressBarClass    : 'progressbar',
        dragOverClass       : 'drag-hover'      
    };

    var tests = {
        filereader: typeof FileReader !== 'undefined',
        dnd : 'draggable' in document.createElement('span')
    };

    console.log(tests);

    /**
     * @exports ui/uploader
     */
    var uploader = {
     
        /**
         * Initialize the plugin.
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').uploader({});
         * @public
         *
         * @constructor
         * @param {Object} [options] - the plugin options
         * @returns {jQueryElement} for chaining
         */
        init : function(options){
            var self = uploader;       

            //get options using default
            options = _.defaults(options || {}, defaults);

            return this.each(function(){
                var $elt = $(this);
                if(!$elt.data(dataNs)){
                   
                    //retrieve elements 
                    options.$input       = $('input[type=file]', $elt);
                    options.$button      = $('.' + options.buttonClass, $elt);
                    options.$fileName    = $('.' + options.fileNameClass, $elt);

                    options.$dropZone    = options.dropZone || $elt.parent().find('.' + options.dropZoneClass);
                    options.$progressBar = options.progressBar || $elt.parent().find('.' + options.progressBarClass);
                    
                    $elt.data(dataNs, options);
           
                    self._reset($elt);
         
                    var changeListener = function (e) {
                        var file = e.target.files[0];
                        
                        // Are you really sure something was selected
                        // by the user... huh? :)
                        if (typeof(file) !== 'undefined') {
                            console.log(file);
                            $elt.trigger('file.' + ns, [file]);
                        }
                    };
                    
                    if (tests.filereader) {
                        // Yep ! :D
                        options.$input.on('change', changeListener);
                    }
                    else {
                        // Nope... :/
                        options.$input.fileReader({
                            id: 'fileReaderSWFObject',
                            filereader: context.taobase_www + 'js/lib/polyfill/filereader.swf',
                            callback: function() {
                                options.$input.on('change', changeListener);
                            }
                        });
                    }

                    if(options.$dropZone.length){
                        if(tests.dnd){
                            console.log('DND is ok');
                            options.$dropZone.on('dragover', function (event) {
                                event.preventDefault();
                                options.$dropZone.addClass(options.dragOverClass);
                            }).on('dragend',  function(event) { 
                                event.preventDefault();
                                options.$dropZone.removeClass(options.dragOverClass);
                            }).on('drop', function(event){
                                event.preventDefault();
                                options.$dropZone.removeClass(options.dragOverClass);
                                
                                console.log('drop files', event.originalEvent.dataTransfer);
                            
                            });
                        } else {
                            options.$dropZone.hide();
                        }
                    }
                    
                    // IE Specific hack. It prevents the button to slightly
                    // move on click. Special thanks to Dieter Rabber, OAT S.A.
                    options.$input.on('mousedown', function(e){
                        e.preventDefault();
                        $(this).blur();
                        return false;
                    });
 
                    /**
                     * The plugin has been created.
                     * @event uploader#create.uploader
                     */
                    $elt.trigger('create.' + ns);
                }
            });
        },

        reset : function(){
            return this.each(function(){
                uploader._reset($(this));
            });
        },

        _reset : function($elt){
            var options = $elt.data(dataNs);
            
            options.$fileName.text(options.fileNamePlaceholder);
            if(options.buttonIcon){        
                options.$button.html('<span class="icon-' + options.buttonIcon +'"></span>' + options.buttonLabel);
            } else {
                options.$button.text(options.buttonLabel);
            }
        },

        _read : function($elt, file){
            var options = $elt.data(dataNs);

            // Show information about the processed file to the candidate.
            var filename = file.name;
            var filesize = file.size;
            var filetype = file.type;
            
            options.$fileName.text(filename);
            
            // Let's read the file to get its base64 encoded content.
            var reader = new FileReader();

            // Update file processing progress.
            
            reader.onload = function (e) {
                
                $container.find('.progressbar').progressbar({
                    value: 100
                });
                
                var base64Data = e.target.result;
                var commaPosition = base64Data.indexOf(',');
                
                // Store the base64 encoded data for later use.
                base64Raw = base64Data.substring(commaPosition + 1);
                filetype = filetype;
                _response = { "base" : { "file" : { "data" : base64Raw, "mime" : filetype, "name" : filename } } }; 
            }
            
            reader.onloadstart = function (e) {
                Helper.removeInstructions(interaction);
                $container.find('.progressbar').progressbar({
                    value: 0
                });
            };
            
            reader.onprogress = function (e) {
                var percentProgress = Math.ceil(Math.round(e.loaded) / Math.round(e.total) * 100);
                $container.find('.progressbar').progressbar({
                    value: percentProgress
                });
            }
            
            reader.readAsDataURL(file);
        },

        /**
         * Destroy completely the plugin.
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').uploader('destroy');
         * @public
         */
        destroy : function(){
            this.each(function(){
                var $elt = $(this);
                var options = $elt.data(dataNs);

                options.$input.off('change')
                              .off('mousedown');

                /**
                 * The plugin has been destroyed.
                 * @event uploader#destroy.uploader
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
    };

    //Register the incrementer to behave as a jQuery plugin.
    Pluginifier.register(ns, uploader);

    /**
     * The only exposed function is used to start listening on data-attr
     *
     * @public
     * @example define(['ui/uploader'], function(uploader){ uploader($('rootContainer')); });
     * @param {jQueryElement} $container - the root context to listen in
     */
    return function listenDataAttr($container){

        $container.find('').each(function(){
            var $elt = $(this);
            $elt.uploader({
            });
        });
    };
});

