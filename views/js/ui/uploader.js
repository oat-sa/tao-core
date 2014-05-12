/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery', 
    'lodash', 
    'i18n', 
    'core/pluginifier', 
    'context',
    'ui/filesender',
    'filereader',
    'jqueryui'
], function($, _, __, Pluginifier, context){
    'use strict';

    var ns = 'uploader';
    var dataNs = 'ui.' + ns;

    //the plugin defaults
    var defaults = {
        containerClass      : 'file-upload',
        browseBtnClass      : 'btn-browse',
        browseBtnIcon       : 'upload',
        browseBtnLabel      : __('Browse...'),
        upload              : true,
        uploadBtnClass      : 'btn-upload',
        uploadBtnIcon       : 'upload',
        uploadBtnLabel      : __('Upload'),
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
                    options.$browseBtn   = $('.' + options.browseBtnClass, $elt);
                    options.$fileName    = $('.' + options.fileNameClass, $elt);
                    options.$dropZone    = options.dropZone || $elt.parent().find('.' + options.dropZoneClass);
                    options.$progressBar = options.progressBar || $elt.parent().find('.' + options.progressBarClass);
    
                    if(options.upload){
                        options.$form = options.$form || $elt.parents('form');
                        options.$uploadBtn   = $('.' + options.uploadBtnClass, $elt);
                    }                   
 
                    $elt.data(dataNs, options);
           
                    self._reset($elt);

                    var inputHandler = function (e) {
                        var file = e.target.files[0];
                        
                        // Are you really sure something was selected
                        // by the user... huh? :)
                        if (typeof(file) !== 'undefined') {
                            $elt.trigger('file.' + ns, [file]);
                        }
                   };

                    var dragOverHandler = function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        options.$dropZone.addClass(options.dragOverClass);
                    };
                    
                    if (tests.filereader) {
                        // Yep ! :D
                        options.$input.on('change', inputHandler);
                    }
                    else {
                        // Nope... :/
                        options.$input.fileReader({
                            id: 'fileReaderSWFObject',
                            filereader: context.taobase_www + 'js/lib/polyfill/filereader.swf',
                            callback: function() {
                                options.$input.on('change', inputHandler);
                            }
                        });
                    }

                    if(options.$dropZone.length){
                        if(tests.dnd){
                            options.$dropZone
                                .on('dragover', dragOverHandler)
                                .on('dragend', dragOverHandler)
                                .on('drop', function(e){
                                    dragOverHandler(e); 
                                    
                                var files =  e.target.filesi || e.originalEvent.files || e.originalEvent.dataTransfer.files;
                                if(files && files.length > 0){
                                    $elt.trigger('file.' + ns, [files[0]]);
                                }
                            
                            });
                        } else {
                            options.$dropZone.hide();
                        }
                    }
                    
                    // IE Specific hack. It prevents the browseBtn to slightly
                    // move on click. Special thanks to Dieter Rabber, OAT S.A.
                    options.$input.on('mousedown', function(e){
                        e.preventDefault();
                        $(this).blur();
                        return false;
                    });


                    //what to do with the file
                    $elt.on('file.' + ns, function(e, file){
                
                        options.$fileName
                            .text(file.name)
                            .removeClass('placeholder');

                        if(options.upload){
                            self._upload($elt, file);
                        }
                        if(options.read){
                            self._read($elt, file);
                        }
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
            
            options.$fileName
                .text(options.fileNamePlaceholder)
                .addClass('placeholder');    

            if(options.browseBtnIcon){        
                options.$browseBtn.html('<span class="icon-' + options.browseBtnIcon +'"></span>' + options.browseBtnLabel);
            } else {
                options.$browseBtn.text(options.browseBtnLabel);
            }
            if(options.upload){
                console.log('reset upload', options.$uploadBtn);
                options.$uploadBtn.prop('disabled', true);
 
                if(options.uploadBtnIcon){        
                    options.$uploadBtn.html('<span class="icon-' + options.uploadBtnIcon +'"></span>' + options.uploadBtnLabel);
                } else {
                    options.$uploadBtn.text(options.uploadBtnLabel);
                }
            }

            /**
             * The plugin has been created.
             * @event uploader#reset.uploader
             */
            $elt.trigger('reset.' + ns);
        },

        _upload : function($elt, file){
            var options = $elt.data(dataNs);
            var uploaded = false;
            var fakeProgress = function(value){
                setTimeout(function(){
                    if(uploaded === false){
                        options.$progressBar.progressbar({
                            value: value
                        });
                        fakeProgress(value += 1);
                    }
                }, 10);
            };
 
            if(options.uploadUrl){

                //ne real way to know the progress
                if(options.$progressBar.length){
                    fakeProgress(0);
                }
                options.$form.sendfile({
                    url : options.uploadUrl, 
                    loaded : function(result){
                        uploaded = true;
                        options.$progressBar.progressbar({value: 100});
                        $elt.trigger('upload.'+ns, [file, result]); 
                    }
                });
            } 
        },

        _read : function($elt, file){
            var options = $elt.data(dataNs);
            var filename;
            var filesize;
            var filetype;
        
            if(options && file){
            
                // Show information about the processed file to the candidate.
                filename = file.name;
                filesize = file.size;
                filetype = file.type;
                
                // Let's read the file to get its base64 encoded content.
                var reader = new FileReader();

                reader.onload = function (e) {
                    options.$progressBar.progressbar({
                        value: 100
                    });
                    $elt.trigger('readend.'+ns, [file, e.target.result]);                    
                };
                
                reader.onloadstart = function (e) {
                    options.$progressBar.progressbar({
                        value: 0
                    });
                    $elt.trigger('readstart.'+ns, [file]); 
                };
               
                if(options.$progressBar.length){
                    reader.onprogress = function (e) {
                        var percentProgress = Math.ceil(Math.round(e.loaded) / Math.round(e.total) * 100);
                        options.$progressBar.progressbar({
                            value: percentProgress
                        });
                    };
                }
                reader.readAsDataURL(file);
            }
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

                options.$dropZone
                    .off('dragover')
                    .off('dragend')
                    .off('drop');
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

