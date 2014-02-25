/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
  // Define changes to default configuration here. For example:
  // config.language = 'fr';
  // config.uiColor = '#AADC6E';

  config.skin = 'tao';
  config.toolbar = 'Block';
  //config.toolbar = 'Inline';
  config.toolbar_Inline = [
    { name: 'clipboard', groups: [ 'undo' ], items: [ 'Undo', 'Redo' ] },
    { name: 'insert', items: [ 'SpecialChar' ] },
    { name: 'basicstyles', groups: [ 'basicstyles' ], items: [ 'Bold', 'Italic', 'Subscript', 'Superscript' ] },
    { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] }
  ];
  config.toolbar_Block = [
    { name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
    { name: 'insert', items: [ 'Image', 'Table', 'SpecialChar' ] },
    '/',
    { name: 'basicstyles', groups: [ 'basicstyles' ], items: [ 'Bold', 'Italic', 'Subscript', 'Superscript' ] },
    { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
    { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] }

    //  { name: 'styles', items: [ 'Styles' ] }, // not for now, might be nice at some point to enter custom feedback and such
  ];
};


CKEDITOR.disableAutoInline = true;
