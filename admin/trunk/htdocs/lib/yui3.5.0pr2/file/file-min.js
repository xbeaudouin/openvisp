/*
YUI 3.5.0pr2 (build 4560)
Copyright 2011 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/
YUI.add("file",function(e){var b=e.Lang,d=e.bind,a=e.config.win;var c=function(g){var f=null;if(e.File.isValidFile(g)){f=g;}else{if(e.File.isValidFile(g.file)){f=g.file;}else{f=false;}}c.superclass.constructor.apply(this,arguments);if(f&&e.File.canUpload()){if(!this.get("file")){this._set("file",f);}if(!this.get("html5")){this._set("html5",true);}if(!this.get("name")){this._set("name",f.name||f.fileName);}if(this.get("size")!=(f.size||f.fileSize)){this._set("size",f.size||f.fileSize);}if(!this.get("type")){this._set("type",f.type);}if(f.hasOwnProperty("lastModifiedDate")&&!this.get("dateModified")){this._set("dateModified",f.lastModifiedDate);}}else{if(this.get("uploader")){}}};e.extend(c,e.Base,{initializer:function(f){if(!this.get("id")){this._set("id",e.guid("file"));}},_swfEventHandler:function(f){if(f.id===this.get("id")){console.log("FE:::"+f.id+":::"+this.get("id")+":::"+f.type);console.log(f);switch(f.type){case"uploadstart":this.fire("uploadstart",{uploader:this.get("uploader")});break;case"uploadprogress":this.fire("uploadprogress",{originEvent:f,bytesLoaded:f.bytesLoaded,bytesTotal:f.bytesTotal,percentLoaded:Math.min(100,Math.round(10000*f.bytesLoaded/f.bytesTotal)/100)});this._set("bytesUploaded",f.bytesLoaded);break;case"uploadcomplete":this.fire("uploadfinished",{originEvent:f});break;case"uploadcompletedata":this.fire("uploadcomplete",{originEvent:f,data:f.data});break;case"uploadcancel":this.fire("uploadcancel",{originEvent:f});break;case"uploaderror":this.fire("uploaderror",{originEvent:f});}}},_uploadEventHandler:function(g){switch(g.type){case"progress":this.fire("uploadprogress",{originEvent:g,bytesLoaded:g.loaded,bytesTotal:this.get("size"),percentLoaded:Math.min(100,Math.round(10000*g.loaded/this.get("size"))/100)});this._set("bytesUploaded",g.loaded);break;case"load":this.fire("uploadcomplete",{originEvent:g,data:g.target.responseText});var h=this.get("xhr").upload,i=this.get("xhr"),f=this.get("boundEventHandler");h.removeEventListener("progress",f);h.removeEventListener("error",f);h.removeEventListener("abort",f);i.removeEventListener("load",f);i.removeEventListener("readystatechange",f);this._set("xhr",null);break;case"error":this.fire("uploaderror",{originEvent:g,status:i.status,statusText:i.statusText});break;case"abort":this.fire("uploadcancel",{originEvent:g});break;case"readystatechange":this.fire("readystatechange",{readyState:g.target.readyState,originEvent:g});break;}},startUpload:function(g,o,m){console.log("Starting upload of file "+this.get("id"));if(this.get("html5")){console.log("We are using html5 upload method");this._set("bytesUploaded",0);this._set("xhr",new XMLHttpRequest());this._set("boundEventHandler",d(this._uploadEventHandler,this));var f=new FormData(),l=m||"Filedata",p=this.get("xhr"),i=this.get("xhr").upload,n=this.get("boundEventHandler");e.each(o,function(r,q){f.append(q,r);});f.append(l,this.get("file"));i.addEventListener("progress",n,false);i.addEventListener("error",n,false);i.addEventListener("abort",n,false);p.addEventListener("load",n,false);p.addEventListener("readystatechange",n,false);p.open("POST",g,true);p.send(f);this.fire("uploadstart",{xhr:p});}else{if(this.get("uploader")){console.log("Using Flash upload method");var k=this.get("uploader"),l=m||"Filedata",h=this.get("id"),j=o||null;console.log("The uploader instance is ");console.log(k);console.log(h);this._set("bytesUploaded",0);k.on("uploadstart",this._swfEventHandler,this);k.on("uploadprogress",this._swfEventHandler,this);k.on("uploadcomplete",this._swfEventHandler,this);k.on("uploadcompletedata",this._swfEventHandler,this);k.on("uploaderror",this._swfEventHandler,this);console.log("Calling upload on the file...");k.callSWF("upload",[h,g,j,l]);}}},cancelUpload:function(){if(this.get("html5")){xhr.abort();}else{if(this.get("uploader")){this.get("uploader").callSWF("cancel",[this.get("id")]);}}},},{NAME:"file",ATTRS:{html5:{readOnly:true,value:false},id:{writeOnce:"initOnly",value:null},size:{writeOnce:"initOnly",value:0},name:{writeOnce:"initOnly",value:null},dateCreated:{writeOnce:"initOnly",value:null},dateModified:{writeOnce:"initOnly",value:null},bytesUploaded:{readOnly:true,value:0},type:{writeOnce:"initOnly",value:null},file:{writeOnce:"initOnly",value:null},uploader:{writeOnce:"initOnly",value:null},xhr:{readOnly:true,value:null},boundEventHandler:{readOnly:true,value:null}},isValidFile:function(f){return(a&&a.File&&f instanceof File);},canUpload:function(){return(a&&a.FormData&&a.XMLHttpRequest);},FOO:"BAR"});e.File=c;},"3.5.0pr2",{requires:["base"]});