/*
YUI 3.5.0pr2 (build 4560)
Copyright 2011 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/
YUI.add("arraylist-filter",function(a){a.mix(a.ArrayList.prototype,{filter:function(c){var b=[];a.Array.each(this._items,function(e,d){e=this.item(d);if(c(e)){b.push(e);}},this);return new this.constructor(b);}});},"3.5.0pr2",{requires:["arraylist"]});