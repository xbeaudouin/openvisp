YUI.add("gallery-notify",function(g){var b={INIT:"init",STARTED:"started"},c=g.Lang,e="boundingBox",d="contentBox",a="closable",f="default",h="icon";g.namespace("Notify").Message=g.Base.create("notify-message",g.Widget,[g.WidgetChild],{BOUNDING_TEMPLATE:"<li/>",CONTENT_TEMPLATE:"<em/>",CLOSE_TEMPLATE:'<span class="{class}">{label}</span>',_timer:null,initializer:function(i){this.get(e).setStyle("opacity",0);},renderUI:function(){var i=this.get(d),k=this.get(e),j;i.setContent(this.get("message"));if(this.get(a)){j=new g.Button({icon:"eks-circle",callback:g.bind(this.close,this),render:true});k.append(j.get("boundingBox").remove());}},bindUI:function(){this._bindHover();},syncUI:function(){this.timer=new g.Timer({length:this.get("timeout"),repeatCount:1,callback:g.bind(this.close,this)});this.get(e).appear({afterFinish:g.bind(function(){this.timer.start();},this)});},close:function(){if(this.timer){this.timer.stop();this.timer=null;}var i=this.get(e);i.fade({on:{finish:g.bind(function(j){j.preventDefault();i.blindUp({duration:0.2,on:{finish:g.bind(function(k){k.preventDefault();this.destroy();},this)}});},this)}});},_bindHover:function(){var i=this.get(e);i.on("mouseenter",g.bind(function(j){this.timer.pause();},this));i.on("mouseleave",g.bind(function(j){if(this.timer){this.timer.resume();}},this));}},{ATTRS:{closable:{value:true,validator:c.isBoolean},message:{validator:c.isString},timeout:{value:8000},icon:{validator:c.isString,setter:function(i){this.get(e).replaceClass(this.getClassName(h,this.get(h)||f),this.getClassName(h,i||f));return i;},lazyAdd:false}}});g.Notify=g.Base.create("notify",g.Widget,[g.WidgetParent,g.EventTarget],{CONTENT_TEMPLATE:"<ul/>",_childConfig:{},initializer:function(i){this.publish(b.INIT,{broadcast:1});this.publish(b.STARTED,{broadcast:1});this.fire(b.INIT);this._buildChildConfig();},syncUI:function(){this.fire(b.STARTED);},addMessage:function(k,j,i){if(!j){j=f;}this._buildChildConfig(k,j);if(i){return this.add(this._childConfig,i);}if(this.get("prepend")){return this.add(this._childConfig,0);}return this.add(this._childConfig);},addMessages:function(m){var n,k,j;for(n in m){if(c.isArray(m[n])){for(k=0,j=m[n].length;k<j;k++){this.addMessage(m[n][k],n);}}}},_buildChildConfig:function(j,i){this._childConfig={closable:this.get(a),timeout:this.get("timeout"),message:j,icon:i};}},{ATTRS:{closable:{value:true,validator:c.isBoolean},defaultChildType:{value:g.Notify.Message},prepend:{value:false,validator:c.isBoolean},timeout:{value:8000}},EVENTS:b});},"gallery-2011.03.11-23-49",{requires:["base","anim","substitute","widget","widget-parent","widget-child","gallery-timer","event-mouseenter","gallery-effects","gallery-button"]});