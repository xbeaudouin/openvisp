/*
YUI 3.5.0pr2 (build 4560)
Copyright 2011 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/
YUI.add('uploader', function(Y) {

/**
 * Provides UI for selecting multiple files and functionality for 
 * uploading multiple files to the server with support for either
 * html5 or Flash transport mechanisms, automatic queue management,
 * upload progress monitoring, and error events.
 * @module uploader
 * @main uploader
 * @since 3.5.0
 */
	
 var Win = Y.config.win;

 if (Win && Win.File && Win.FormData && Win.XMLHttpRequest) {
 	Y.Uploader = Y.UploaderHTML5;
 }

 else {
 	Y.Uploader = Y.UploaderFlash;
 }


}, '3.5.0pr2' ,{requires:['uploader-flash', 'uploader-html5']});
