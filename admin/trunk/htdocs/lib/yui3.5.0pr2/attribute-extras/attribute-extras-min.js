/*
YUI 3.5.0pr2 (build 4560)
Copyright 2011 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/
YUI.add("attribute-extras",function(f){var a="broadcast",d="published",e="initValue",c={readOnly:1,writeOnce:1,getter:1,broadcast:1};function b(){}b.prototype={modifyAttr:function(h,g){var i=this,k,j;if(i.attrAdded(h)){if(i._isLazyAttr(h)){i._addLazyAttr(h);}j=i._state;for(k in g){if(c[k]&&g.hasOwnProperty(k)){j.add(h,k,g[k]);if(k===a){j.remove(h,d);}}}}},removeAttr:function(g){this._state.removeAll(g);},reset:function(g){var h=this;if(g){if(h._isLazyAttr(g)){h._addLazyAttr(g);}h.set(g,h._state.get(g,e));}else{f.each(h._state.data,function(i,j){h.reset(j);});}return h;},_getAttrCfg:function(g){var h;if(g){h=this._state.getAll(g)||{};}else{h={};f.each(this._state.data,function(i,j){h[g]=this._state.getAll(g);});}return h;}};f.AttributeExtras=b;},"3.5.0pr2");