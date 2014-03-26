//define, dep: ckeditor, dtdhandler, lodash, return ckconfigurator
/**
 * The DTD as defined by CKEDITOR, will on occasion be toggled to the QTI DTD
 */
var originalDtd = _.cloneDeep(CKEDITOR.dtd);

/**
 * Cache original config
 */
var originalConfig = _.cloneDeep(CKEDITOR.config);


/**
 * The configuration generator will not reconfigure a CKEDITOR instance directly but
 * rather expose methods to do so. However, the DTD will need to be set on the global
 * CKEDITOR object and this will be done automatically depending on the argument 'mode'.
 *
 * Options not covered in http://docs.ckeditor.com/#!/api/CKEDITOR.config:
 * options.dtdOverrides         -> @see dtdOverrides which pre-defines them
 * options.toolbar              -> @see toolbar
 * options.positionedPlugins    -> @see ckConfig.positionedPlugins
 *
 * @param mode block | inline | flow | qtiBlock | qtiInline | qtiFlow
 * @param options is based on the CKEDITOR config object with some additional sugar
 * @see http://docs.ckeditor.com/#!/api/CKEDITOR.config
 */
var ckConfigurator = function(mode, options) {

    options = options || {};

    var isQti = mode.indexOf('qti') === 0,
        dtd = _.cloneDeep(originalDtd);


    /**
     * Elements that are allowed as children of other elements, this is only taken in account when in a QTI context
     * For a comprehensive list type console.log(CKEDITOR.dtd).
     * This list can be edited via options.dtdOverrides
     */
    var dtdOverrides = {
        pre: {
            add: [],
            remove: ['img', 'object', 'big', 'small', 'sub', 'sup']
        }
    };


    // This is different from CKEDITOR.config.extraPlugins since it also allows to position the button
    // Valid positioners are indertAfter | insertBefore | replace followed by the button name, e.g. 'Anchor'
    // separator bool, defaults to false
    // don't get confused by the naming - TaoMediaManager is the button name for the plugin taomediamanager
    var positionedPlugins = {
        TaoMediaManager: {
            insertAfter: 'Anchor',
            separator: true
        }
    };


    /**
     * Toolbar presets that you normally never would need to change, they can however be overridden with options.toolbar.
     * The argument 'mode' determines which toolbar to use
     */
    var toolbars = {
        inline: [{
            name: 'clipboard',
            items: ['Undo', 'Redo']
        }, {
            name: 'insert',
            items: ['SpecialChar']
        }, {
            name: 'basicstyles',
            items: ['Bold', 'Italic', 'Subscript', 'Superscript']
        }, {
            name: 'links',
            items: ['Link', 'Unlink', 'Anchor']
        }],

        flow: [{
            name: 'clipboard',
            items: ['Undo', 'Redo']
        }, {
            name: 'insert',
            items: ['SpecialChar']
        }, {
            name: 'basicstyles',
            items: ['Bold', 'Italic', 'Subscript', 'Superscript']
        }, {
            name: 'links',
            items: ['Link', 'Unlink', 'Anchor']
        }],

        block: [{
                name: 'clipboard',
                items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
            }, {
                name: 'insert',
                items: ['Image', 'Table', 'SpecialChar']
            },
            '/', {
                name: 'basicstyles',
                items: ['Bold', 'Italic', 'Subscript', 'Superscript']
            }, {
                name: 'links',
                items: ['Link', 'Unlink', 'Anchor']
            }, {
                name: 'paragraph',
                items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
            }
        ]
    };

    /**
     * defaults for editor configuration
     */
    var ckConfig = {
        disableAutoInline: true,
        //toolbar: toolbars.block,
        autoParagraph: false,
        extraPlugins: '', //taofloatingspace
        floatSpaceDockedOffsetY: 0,
        forcePasteAsPlainText: true,
        skin: 'tao',
        removePlugins: '' //floatingspace
    };



    /**
     * Toggles between original and custom DTD
     */
    var _toggleDtd = function(dtd) {
        CKEDITOR.dtd = dtd;
    };



    /**
     * Manipulates the DTD used by CKEDITOR, see var dtdOverrides for usage
     *
     * @param dtd {object} original CKEDITOR.dtd
     * @param dtdOverrides {object} with instructions for manipulation of the DTD
     * @return dtd {object} the modified dtd
     */
    var _buildDtd = function(dtd, dtdOverrides) {
        var element,
            listCnt,
            child,
            actions = ['remove', 'add'],
            actCnt,
            actLnt = actions.length,
            action;

        for (element in dtdOverrides) {
            if (!dtdOverrides.hasOwnProperty(element)) {
                continue;
            }
            // disallow adding keys to the dtd ckeditor cannot handle
            if (!dtd.hasOwnProperty(element)) {
                continue;
            }

            // note: removing and adding is on purpose done in two steps
            for (actCnt = 0; actCnt < actLnt; actCnt++) {
                action = actions[actCnt];
                listCnt = dtdOverrides[element][action].length;

                // allow 'all' as a shortcut for 'remove all children'
                if (action === 'remove' && dtdOverrides[element][action] === 'all') {
                    dtd[element] = {};
                }

                // doggy style loop over children to add
                while (listCnt--) {
                    child = dtdOverrides[element][action][listCnt];
                    if (action === 'remove') {
                        delete(dtd[element][child]);
                    }
                    // add child element to element as long as it's not entirely unknown to ckeditor
                    else if (action === 'add' && typeof dtd[child] !== 'undefined') {
                        dtd[element][child] = 1;
                    }
                }
            }
        }

        return dtd;
    };


    /**
     * Insert positioned plugins at position specified in options.positionedPlugins
     *
     * @param ckConfig
     * @param positionedPlugins
     */
    var _updatePlugins = function(ckConfig, positionedPlugins) {
        var itCnt,
            tbCnt = ckConfig.toolbar.length,
            itLen,
            method,
            plugin,
            index,
            separator,
            idxItem,
            numToReplace;

        // add positioned plugins to extraPlugins and let CKEDITOR take care of their registration
        ckConfig.extraPlugins = (function(positionedPluginArr, extraPlugins) {
            var i = positionedPluginArr.length,
                extraPluginArr = extraPlugins.split(',');

            while (i--) {
                positionedPluginArr[i] = positionedPluginArr[i].toLowerCase();
            }

            extraPluginArr = _.compact(_.union(extraPluginArr, positionedPluginArr));
            return extraPluginArr.join(',');

        }(_.keys(positionedPlugins), ckConfig.extraPlugins));

        // add positioned plugins to toolbar
        for (plugin in positionedPlugins) {
            method = (function(pluginProps) {
                var i = pluginProps.length;
                while (i--) {
                    if (pluginProps[i].indexOf('insert') === 0 || pluginProps[i] === 'replace') {
                        return pluginProps[i];
                    }
                }

                throw 'Missing key insertBefore | insertAfter | replace in positionedPlugins';

            }(_.keys(positionedPlugins[plugin])));

            // the item to insert before | after
            idxItem = positionedPlugins[plugin][method].toLowerCase();
            separator = positionedPlugins[plugin].separator || false;
            index = -1;

            // each button row
            while (tbCnt--) {
                itLen = ckConfig.toolbar[tbCnt].items.length;

                // each item in row
                for (itCnt = 0; itCnt < itLen; itCnt++) {
                    if (ckConfig.toolbar[tbCnt].items[itCnt].toLowerCase() === idxItem) {
                        index = itCnt;
                        break;
                    }
                }
                if (index > -1) {
                    // ~~ converts bool to number
                    numToReplace = ~~ (method === 'replace');
                    if (method === 'insertAfter') {
                        index++;
                    }
                    if (separator) {
                        ckConfig.toolbar[tbCnt].items.splice(index, numToReplace, '-');
                        index++;
                    }
                    ckConfig.toolbar[tbCnt].items.splice(index, numToReplace, plugin);
                    break;
                }
            }
        }

    };


    // modify DTD to either comply with QTI or XHTML
    if (isQti) {
        mode = mode.slice(3).toLowerCase();
        if (options.dtdOverrides) {
            dtdOverrides = _.assign(dtdOverrides, options.dtdOverrides);
            delete(options.dtdOverrides);
        }
        dtd = _buildDtd(dtd, dtdOverrides);
    }
    _toggleDtd(dtd);

    // if there is a toolbar in the options add it to the set
    if (options.toolbar) {
        toolbars[mode] = options.toolbar;
        delete(options.toolbar);
    }

    // add the toolbar - whether it comes via options or mode to the config
    if (typeof toolbars[mode] !== 'undefined') {
        ckConfig.toolbar = toolbars[mode];
    }


    // modify plugins - this will change the toolbar too
    if (options.positionedPlugins) {
        positionedPlugins = _.assign(positionedPlugins, options.positionedPlugins);
        delete(options.positionedPlugins);
    }
    _updatePlugins(ckConfig, positionedPlugins);

    ckConfig = _.assign({}, _.cloneDeep(originalConfig), ckConfig, options);


    // remember that the DTD at this point is already manipulated @see also originalDtd
    return {
        // the whole shebang
        getConfig: function() {
            return ckConfig;
        },
        // shortcut for the toolbar
        getToolbar: function() {
            return ckConfig.toolbar;
        }
    }

};