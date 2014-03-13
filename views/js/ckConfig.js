define(['ckeditor'], function (ckeditor) {

    config.skin = 'tao';
    config.toolbar = 'Block';
    //config.toolbar = 'Inline';

    var toolbars = {
        inline: [
            { name: 'clipboard', groups: [ 'undo' ], items: [ 'Undo', 'Redo' ] },
            { name: 'insert', items: [ 'SpecialChar' ] },
            { name: 'basicstyles', groups: [ 'basicstyles' ], items: [ 'Bold', 'Italic', 'Subscript', 'Superscript' ] },
            { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] }
        ],

        flow: [
            { name: 'clipboard', groups: [ 'undo' ], items: [ 'Undo', 'Redo' ] },
            { name: 'insert', items: [ 'SpecialChar' ] },
            { name: 'basicstyles', groups: [ 'basicstyles' ], items: [ 'Bold', 'Italic', 'Subscript', 'Superscript' ] },
            { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] }
        ],

        block: [
            { name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
            { name: 'insert', items: [ 'Image', 'Table', 'SpecialChar' ] },
            '/',
            { name: 'basicstyles', groups: [ 'basicstyles' ], items: [ 'Bold', 'Italic', 'Subscript', 'Superscript' ] },
            { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] }
        ]};

    var qtiDeviationList = {
        pre: {
            add: [],
            remove: ['img', 'object', 'big', 'small', 'sub', 'sup']
        }
    };


    ckeditor.config.toolbar = 'result of switch'


    var editDtd = function (dtd, deviationList) {
        var element,
            listCnt,
            child,
            actions = ['remove', 'add'],
            actCnt,
            actLnt = actions.length,
            action;


        for (element in deviationList) {
            if (!deviationList.hasOwnProperty(element)) {
                continue;
            }
            // disallow adding keys to the dtd ckeditor cannot handle
            if (!dtd.hasOwnProperty(element)) {
                continue;
            }

            for (actCnt = 0; actCnt < actLnt; actCnt++) {
                action = actions[actCnt];
                listCnt = deviationList[element][action].length;

                // allow 'all' as a shortcut to remove all children
                if (action === 'remove' && deviationList[element][action] === 'all') {
                    dtd[element] = {};
                }

                // doggy style loop over children to add
                while (listCnt--) {
                    child = deviationList[element][action][listCnt];
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


    var ckConfigurator = function (seletor, context) {
        switch (context) {
            case 'flow':
            //use a + b

            default:
            // use whatever
        }

        seletor.ckeditor()


    }

    return ckConfigurator;
});
