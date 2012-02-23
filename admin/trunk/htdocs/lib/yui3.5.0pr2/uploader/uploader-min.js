/*
YUI 3.5.0pr2 (build 4560)
Copyright 2011 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/
YUI.add("uploader",function(b){var a=b.config.win;if(a&&a.File&&a.FormData&&a.XMLHttpRequest){b.Uploader=b.UploaderHTML5;}else{b.Uploader=b.UploaderFlash;}},"3.5.0pr2",{requires:["uploader-flash","uploader-html5"]});