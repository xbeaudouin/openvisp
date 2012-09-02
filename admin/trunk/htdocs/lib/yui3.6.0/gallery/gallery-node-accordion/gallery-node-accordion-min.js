YUI.add("gallery-node-accordion",function(b){var J=b.ClassNameManager.getClassName,A={},G={fast:0.1,slow:0.6,normal:0.4},m="accordion",d="item",C="scrollHeight",I="scrollWidth",e="width",y="height",L="px",q="host",M="orientation",c="fade",z="multiple",o="persistent",N="speed",E="anim",x="items",w="triggerSelector",v="itemSelector",l="itemBodySelector",K=J(m),B=J(m,"hidden"),i=J(m,d),p=J(m,d,"active"),a=J(m,d,"sliding"),F=J(m,d,"hd"),D=J(m,d,"bd"),t=J(m,d,"bd","sliding"),n=J(m,d,"ft"),r=J(m,d,"trigger"),f=".",s=">.",g=" .",k=", ",u=s+i,H=f+D,j=u+f+r+k+u+s+f+r+k+u+s+F+g+r+k+u+s+n+g+r;function h(P,O){return P.get("region")[O];}b.namespace("Plugin").NodeAccordion=b.Base.create("NodeAccordion",b.Plugin.Base,[],{_root:null,_eventHandler:null,initializer:function(P){var O=this;if((O._root=O.get(q))){O.get(x).each(function(Q){if(Q.hasClass(p)){O.expandItem(Q);}else{O.collapseItem(Q);}});O._eventHandler=O._root.delegate("click",function(Q){O.toggleItem(Q.currentTarget);Q.target.blur();Q.halt();},O.get(w));O._root.replaceClass(B,K);}},destructor:function(){var O=this;if(O._eventHandler){O._eventHandler.detach();}O._eventHandler=null;},_slidingBegin:function(O,P,Q){O.addClass(a);P.addClass(t);if(Q){O.addClass(p);}},_slidingEnd:function(O,P,Q){O.removeClass(a);P.removeClass(t);if(Q){O.removeClass(p);}},_getItemBody:function(O){var Q,P=this.get(l);if(b.Lang.isNumber(O)){O=this.get(x).item(O);}Q=O.one(P);if(!Q){O=O.next();Q=((O&&O.test(P))?O:null);}return Q;},_getItem:function(P){if(b.Lang.isNumber(P)){P=this.get(x).item(P);}var O=function(Q){return Q.hasClass(i);};if(P&&!P.hasClass(i)){return P.ancestor(O);}return P;},_animate:function(S,P,Q){var R=A[S],O=this;if((R)&&(R.get("running"))){R.stop();}if(b.Lang.isFunction(O.get(E))){P.easing=O.get(E);}R=new b.Anim(P);R.on("end",Q,O);R.run();A[S]=R;return R;},_openItem:function(Z){var Y=this,T,P,W,S,R,V=Y.get(x),Q=Y.get(M),U={duration:Y.get(N),to:{scroll:[]}},X,O;if(Z&&V.size()&&!Z.hasClass(p)&&(T=Y._getItemBody(Z))&&(P=b.stamp(T))){if(!Y.get(z)){X=Y._root.one(s+p);}U.to[Q]=(Q==e?T.get(I):T.get(C));U.node=T;Y._slidingBegin(Z,T,true);W=function(){Y._slidingEnd(Z,T);};if(!Y.get(E)){S=h(T,Q);if(X&&(O=Y._getItemBody(X))){S=h(O,Q);Y._slidingBegin(X,O);}for(R=1;R<=U.to[Q];R++){if(X&&O){O.setStyle(Q,(S-R)+L);}T.setStyle(Q,R+L);}if(X&&O){Y._slidingEnd(X,O,true);}W();}else{U.to.scroll=[0,0];if(Y.get(c)){U.to.opacity=1;}if(b.Lang.isObject(X)){Y._closeItem(X);}Y._animate(P,U,W);}}},_closeItem:function(X){var W=this,S,O,V,R,Q,U=W.get(x),P=W.get(M),T={duration:W.get(N),to:{scroll:[]}};if(X&&U.size()&&(S=W._getItemBody(X))&&(O=b.stamp(S))){T.to[P]=(P==y?W.get("minHeight"):W.get("minWidth"));T.node=S;W._slidingBegin(X,S);V=function(){W._slidingEnd(X,S,true);};if(!W.get(E)){R=h(S,P);for(Q=R;Q>=T.to[P];Q--){S.setStyle(P,Q+L);}V();}else{T.to.scroll=(P==e?[S.get(I),0]:[0,S.get(C)]);if(W.get(c)){T.to.opacity=0;}W._animate(O,T,V);}}},expandAllItems:function(){var O=this;if(O.get(z)){O.get(x).each(function(P){O.expandItem(P);});}return O;},collapseAllItems:function(){var O=this;if(O.get(z)||!O.get(o)){O.get(x).each(function(P){O.collapseItem(P);});}return O;},expandItem:function(Q){var O=this,P=O._getItem(Q);if(P){O._openItem(P);}return O;},collapseItem:function(Q){var O=this,P=O._getItem(Q);if(P&&P.hasClass(p)&&(O.get(z)||!O.get(o))){O._closeItem(P);}return O;},toggleItem:function(Q){var O=this,P=O._getItem(Q);if(P){if(P.hasClass(p)&&(O.get(z)||!O.get(o))){O._closeItem(P);}else{O._openItem(P);}}return O;}},{NS:m,ATTRS:{activeItems:{readOnly:true,getter:function(O){return this._root.all(s+p);}},items:{readOnly:true,getter:function(O){return this._root.all(this.get(v));}},orientation:{value:y,writeOnce:true},fade:{value:false},anim:{value:false,validator:function(O){return !b.Lang.isUndefined(b.Anim);}},multiple:{value:true},persistent:{value:false},speed:{value:0.4,validator:function(O){return(b.Lang.isNumber(O)||(b.Lang.isString(O)&&G.hasOwnProperty(O)));},setter:function(O){return(G.hasOwnProperty(O)?G[O]:O);}},triggerSelector:{initOnly:true,value:j},itemSelector:{initOnly:true,value:u},itemBodySelector:{initOnly:true,value:H},minHeight:{value:0},minWidth:{value:0}}});},"gallery-2011.03.02-20-58",{skinnable:true,optional:["anim"],requires:["node-base","node-style","plugin","base","node-event-delegate","classnamemanager"]});