/*
YUI 3.5.0pr2 (build 4560)
Copyright 2011 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/
YUI.add("pjax-plugin",function(a){a.Plugin.Pjax=a.Base.create("pjaxPlugin",a.Pjax,[a.Plugin.Base],{initializer:function(b){this.set("container",b.host);}},{NS:"pjax"});},"3.5.0pr2",{requires:["node-pluginhost","pjax","plugin"]});