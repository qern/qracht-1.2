/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	CKEDITOR.scriptLoader.load( CKEDITOR.basePath + 'plugins/image_uploader/plugin.js' );
   	config.extraPlugins =  "youtube";// , MediaEmbed,vimeo
    /*config.removePlugins = 'about, adobeair, ajax, autogrow, bbcode, clipboard, colordialog, devtools, dialog, div, docprops, find, flash, forms, iframe, iframedialog, MediaEmbed pagebreak, pastefromword, pastetext, placeholder, scayt, showblocks, smiley, specialchar, styles, stylesheetparser, table, tableresize, tabletools, templates, uicolor, wsc, xml';*/
    //config.removePlugins = 'about, a11yhelp, adobeair, bbcode, clipboard, colordialog, devtools, docprops, finc, flash, iframe, iframedialog, scayt, showblocks, smiley, stylesheetparser, uicolor, wc, xml';*/
    config.toolbar = 'MyToolbar';
 
    config.toolbar_MyToolbar =
    [
        
        { name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
        { name: 'editing', items : [ 'Find','Replace' ] },
        { name: 'insert', items : [ 'Image', 'Youtube','SpecialChar' ] },
        { name: 'basicstyles', items : [ 'Bold','Italic','Strike','-','RemoveFormat' ] },
        { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote' ] },
        { name: 'links', items : [ 'Link','Unlink','Anchor' ] },
            '/',
        { name: 'styles', items : [ 'Styles','Format' ] },
        //{ name: 'tools', items : [ 'Maximize','-','About' ] }
        //{ name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ] },
        //{ name: 'document', items : [ 'NewPage','Preview' ] },
    ]; 
};
