define(['jquery', 'lodash', 'layout/logout-event'], function($, _, logoutEvent) {
    'use strict';

    /**
     * The FileSender widget enables you to post a file
     * to the server asynchronously.
     *
     * @exports filesender
     */
    var FileSender = {

        /**
         * The default options
         */
        _opts: {
            frame: '__postFrame_',
            loaded: function(data) {}
        },

        /**
         * Initialize the file sending
         *  @param {Object} options - the sending options
         *  @param {String} [options.url] - the url where the form will send the file, if not set we get the form.action attr
         *  @param {String} [options.frame] - a name for the frame create in background
         *  @param {String} [options.fileParamName] - the name of the element of request payload which will contain file.
         *  @param {String} [options.fileNameParamName] - the name of the element of request payload which will contain file name.
         *  @param {FileLoadedCallback} [options.loaded] - executed once received the server response
         */
        _init: function(options) {

            var self = FileSender,
                opts = _.defaults(options, self._opts),
                xhr2 = typeof XMLHttpRequest !== 'undefined' && new XMLHttpRequest().upload && typeof FormData !== 'undefined',
                $form = this,
                fileParamName = options.fileParamName || 'content',
                fileNameParamName = options.fileNameParamName || 'contentName',
                $file, xhr, fd;

            if (!$form.attr('action') && (!opts.url || opts.url.trim().length === 0)) {
                throw new Error('An url is required in the options or at least an action ');
            }
            $file = $form.find("input[type='file']");
            if ($file.length === 0) {
                throw new Error('This plugin is used to post files, your form should include an input element of type file.');
            }
            //for is not really nessasery when using XHR so moving to fallback section
            if (!$form || !$form.is('form')) {
                throw new Error('This plugin can only be called on a FORM element');
            }

            if (xhr2) {
                //send using xhr2
                xhr = new XMLHttpRequest();

                //post the full form that contains the file
                fd = new FormData(this[0]);

                if (options.file && options.file instanceof File) {
                    fd.append(fileParamName, options.file);
                    fd.append(fileNameParamName, encodeURIComponent(options.file.name));
                }

                xhr.open("POST", opts.url, true);
                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            var result = $.parseJSON(xhr.responseText);
                            if (result.error) {
                                if (typeof opts.failed === 'function') {
                                    opts.failed(result.error);
                                }
                            } else if (typeof opts.loaded === 'function') {
                                opts.loaded(result);
                            }
                        } else {

                            if(xhr.status === 403) {
                                logoutEvent();
                            }

                            if (typeof opts.failed === 'function') {
                                opts.failed();
                            }
                        }
                    }
                };

                // Initiate a multipart/form-data upload
                xhr.send(fd);
            }
        }
    };

    /**
     * Reference the plugin to the jQuery context
     * to be able to call as $('#aForm').sendfile({'url' : '/api/postfile'});
     *  @param {Object} options - the sending options
     *  @param {String} options.url - the url where the form will send the file
     *  @param {String} [options.frame] - a name for the frame create in background
     *  @param {FileLoadedCallback} [options.loaded] - executed once received the server response
     */
    $.fn.sendfile = function(options) {
        return FileSender._init.call(this, options);
    };

    /**
     * Callback function to receive the server response of posted file
     * @callback FileLoadedCallback
     * @param {Object} data - the evaluated JSON response sent by the server
     */
});
