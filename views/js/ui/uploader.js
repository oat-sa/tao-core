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
        browseBtnIcon       : false,
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
         * @param {Boolean} [options.upload =  true] - if we upload the file once selected
         * @param {Boolean} [options.read =  true] - if we can read the file once selected1
         * @param {String} [options.containerClass = file-upload] - the class of the upload container
         * @param {jQueryElement} [options.browseBtn] - the browse button element
         * @param {String} [options.browseBtnClass = btn-browse] - the class to identify the browse button
         * @param {String|Boolean} [options.browseBtnIcon  = false] - the icon used by the browse button
         * @param {String} [options.browseBtnLabel = Browse] - the brows button label
         * @param {jQueryElement} [options.uploadBtn] - the upload button element
         * @param {String} [options.uploadBtnClass = btn-upload] - the class to identify the upload button
         * @param {String|Boolean} [options.uploadBtnIcon  = upload] - the icon used by the upload button
         * @param {String} [options.uploadBtnLabel = Browse] - the brows button label
        uploadBtnLabel      : __('Upload'),
        fileNameClass       : 'file-name',
        fileNamePlaceholder : __('No file selected'),
        dropZoneClass       : 'file-drop',
        progressBarClass    : 'progressbar',
        dragOverClass       : 'drag-hover'
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
                        options.$uploadBtn = options.uploadBtn || $elt.parent().find('.' + options.uploadBtnClass, $elt);
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
                    var dragOutHandler = function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        options.$dropZone.removeClass(options.dragOverClass);
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
                                .on('dragend', dragOutHandler)
                                .on('drop', function(e){
                                    dragOutHandler(e); 
                                    
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
                           options.$uploadBtn
                                .off('click')
                                .on('click', function(e){
                                    e.preventDefault();
                                
                                    self._upload($elt, file);
                                }).removeProp('disabled');
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

       /**
        * Reset the component
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').uploader('reset');
        * @param {jQueryElement} $elt - plugin's element 
        * @fires uploader#reset.uploader
        */
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

       /**
        * Upload the selected file
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').uploader('upload', file);
        * @param {jQueryElement} $elt - plugin's element 
        * @param {Object} [file] - the file object
        * @fires uploader#upload.uploader
        */
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

                        /**
                         * A file is uploaded
                         * @event uploader#upload.uploader
                         * @param {Object} file - the uploaded file
                         * @param {Object} result - the upload response
                         */
                        $elt.trigger('upload.'+ns, [file, result]); 
                    }
                });
            } 
        },

       /**
        * Read the selected file
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').uploader('upload', file);
        * @param {jQueryElement} $elt - plugin's element 
        * @param {Object} [file] - the file object
        * @fires uploader#readstart.uploader
        * @fires uploader#readend.uploader
        */
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

                    /**
                     * The reading fininshed
                     * @event uploader#upload.uploader
                     * @param {Object} file - the uploaded file
                     * @param {Object} result - the content
                     */
                    $elt.trigger('readend.'+ns, [file, e.target.result]);                    
                };
                
                reader.onloadstart = function (e) {
                    options.$progressBar.progressbar({
                        value: 0
                    });

                    /**
                     * The reading starts
                     * @event uploader#upload.uploader
                     * @param {Object} file - the uploaded file
                     */
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

                uploader._reset($elt);

                options.$input.off('change')
                              .off('mousedown');

                options.$dropZone
                    .off('dragover')
                    .off('dragend')
                    .off('drop');

                if(options.upload){
                    options.$uploadBtn.off('click');
                }
                /**
                 * The plugin has been destroyed.
                 * @event uploader#destroy.uploader
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
    };

    //Register the incrementer to behave as a jQuery plugin.
    Pluginifier.register(ns, uploader, ['reset', 'upload', 'read']);

});

