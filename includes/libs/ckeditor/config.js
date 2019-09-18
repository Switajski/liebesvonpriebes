/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here.
    // For complete reference see:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    config.toolbar =
    [
        { name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo', '-', 'Find','Replace','-','ShowBlocks','SelectAll','-','Scayt' ] },
        { name: 'tools', items : [ 'Source', '-','Maximize','-','About' ] },
        '/',
        { name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','Iframe','CreateDiv','-','Link','Unlink','Anchor' ] },
        { name: 'styles', items : [ 'FontSize','Format' ] },
        '/',
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','-','TextColor','BGColor','RemoveFormat','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote' ] },
    ];
    
    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

    // Simplify the dialog windows.
    config.removeDialogTabs = 'image:advanced;';
    
    // This setting is used when instantiating CKEDITOR.editor.filter. 
    // true – will disable the filter (data will not be filtered, all features will be activated).
    // see http://docs.ckeditor.com/#!/api/CKEDITOR.config-cfg-allowedContent
    config.allowedContent = true;
    
    config.language = 'de';
    // config.uiColor = '#AADC6E';
    config.htmlEncodeOutput = false;
    config.basicEntities = false;
    config.enterMode = CKEDITOR.ENTER_P;
    config.entities = false;
    config.entities_latin = false;
    config.entities_greek = false;
    config.ignoreEmptyParagraph = false;
    config.fillEmptyBlocks = false;
    config.autoParagraph = false;
    
    /*
    config.codemirror = {
        mode: 'smartymixed'
    };
    */
};