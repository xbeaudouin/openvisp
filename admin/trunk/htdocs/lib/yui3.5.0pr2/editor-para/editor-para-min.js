/*
YUI 3.5.0pr2 (build 4560)
Copyright 2011 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/
YUI.add("editor-para",function(a){var d=function(){d.superclass.constructor.apply(this,arguments);},k="host",f="body",c="nodeChange",j="parentNode",b=f+" > p",h="p",g="<br>",i="firstChild",e="li";a.extend(d,a.Base,{_fixFirstPara:function(){var p=this.get(k),r=p.getInstance(),q,s,l=r.config.doc.body,o=l.innerHTML,m=((o.length)?true:false);if(o===g){o="";m=false;}l.innerHTML="<"+h+">"+o+r.EditorSelection.CURSOR+"</"+h+">";s=r.one(b);q=new r.EditorSelection();q.selectNode(s,true,m);},_onNodeChange:function(R){var F=this.get(k),q=F.getInstance(),x,D,C,T,O,H=q.EditorSelection.DEFAULT_BLOCK_TAG,z,o,s,P,v,l,G,M,u,N,V,S,J,B,A,Q=":last-child";switch(R.changedType){case"enter-up":var m=((this._lastPara)?this._lastPara:R.changedNode),U=m.one("br.yui-cursor");if(this._lastPara){delete this._lastPara;}if(U){if(U.previous()||U.next()){if(U.ancestor(h)){U.remove();}}}if(!m.test(H)){var E=m.ancestor(H);if(E){m=E;E=null;}}if(m.test(H)){var I=m.previous(),L,w,y=false;if(I){L=I.one(Q);while(!y){if(L){w=L.one(Q);if(w){L=w;}else{y=true;}}else{y=true;}}if(L){F.copyStyles(L,m);}}}break;case"enter":if(a.UA.ie){if(R.changedNode.test("br")){R.changedNode.remove();}else{if(R.changedNode.test("p, span")){var U=R.changedNode.one("br.yui-cursor");if(U){U.remove();}}}}if(a.UA.webkit){if(R.changedEvent.shiftKey){F.execCommand("insertbr");R.changedEvent.preventDefault();}}if(R.changedNode.test("li")&&!a.UA.ie){x=q.EditorSelection.getText(R.changedNode);if(x===""){C=R.changedNode.ancestor("ol,ul");var K=C.getAttribute("dir");if(K!==""){K=' dir = "'+K+'"';}C=R.changedNode.ancestor(q.EditorSelection.BLOCKS);T=q.Node.create("<p"+K+">"+q.EditorSelection.CURSOR+"</p>");C.insert(T,"after");R.changedNode.remove();R.changedEvent.halt();O=new q.EditorSelection();O.selectNode(T,true,false);}}if(a.UA.gecko&&F.get("defaultblock")!=="p"){C=R.changedNode;if(!C.test(e)&&!C.ancestor(e)){if(!C.test(H)){C=C.ancestor(H);}T=q.Node.create("<"+H+"></"+H+">");C.insert(T,"after");O=new q.EditorSelection();if(O.anchorOffset){z=O.anchorNode.get("textContent");D=q.one(q.config.doc.createTextNode(z.substr(0,O.anchorOffset)));o=q.one(q.config.doc.createTextNode(z.substr(O.anchorOffset)));P=O.anchorNode;P.setContent("");l=P.cloneNode();l.append(o);G=false;u=P;while(!G){u=u.get(j);if(u&&!u.test(H)){M=u.cloneNode();M.set("innerHTML","");M.append(l);s=u.get("childNodes");var r=false;s.each(function(n){if(r){M.append(n);}if(n===P){r=true;}});P=u;l=M;}else{G=true;}}o=l;O.anchorNode.append(D);if(o){T.append(o);}}if(T.get(i)){T=T.get(i);}T.prepend(q.EditorSelection.CURSOR);O.focusCursor(true,true);x=q.EditorSelection.getText(T);if(x!==""){q.EditorSelection.cleanCursor();}R.changedEvent.preventDefault();}}break;case"keyup":if(a.UA.gecko){if(q.config.doc&&q.config.doc.body&&q.config.doc.body.innerHTML.length<20){if(!q.one(b)){this._fixFirstPara();}}}break;case"backspace-up":case"backspace-down":case"delete-up":if(!a.UA.ie){N=q.all(b);S=q.one(f);if(N.item(0)){S=N.item(0);}V=S.one("br");if(V){V.removeAttribute("id");V.removeAttribute("class");}D=q.EditorSelection.getText(S);D=D.replace(/ /g,"").replace(/\n/g,"");B=S.all("img");if(D.length===0&&!B.size()){if(!S.test(h)){this._fixFirstPara();}J=null;if(R.changedNode&&R.changedNode.test(h)){J=R.changedNode;}if(!J&&F._lastPara&&F._lastPara.inDoc()){J=F._lastPara;}if(J&&!J.test(h)){J=J.ancestor(h);}if(J){if(!J.previous()&&J.get(j)&&J.get(j).test(f)){R.changedEvent.frameEvent.halt();R.preventDefault();}}}if(a.UA.webkit){if(R.changedNode){R.preventDefault();S=R.changedNode;if(S.test("li")&&(!S.previous()&&!S.next())){x=S.get("innerHTML").replace(g,"");if(x===""){if(S.get(j)){S.get(j).replace(q.Node.create(g));R.changedEvent.frameEvent.halt();q.EditorSelection.filterBlocks();}}}}}}if(a.UA.gecko){T=R.changedNode;A=q.config.doc.createTextNode(" ");T.appendChild(A);T.removeChild(A);}break;}if(a.UA.gecko){if(R.changedNode&&!R.changedNode.test(H)){J=R.changedNode.ancestor(H);if(J){this._lastPara=J;}}}},_afterEditorReady:function(){var m=this.get(k),n=m.getInstance(),l;if(n){n.EditorSelection.filterBlocks();l=n.EditorSelection.DEFAULT_BLOCK_TAG;b=f+" > "+l;h=l;}},_afterContentChange:function(){var l=this.get(k),m=l.getInstance();if(m&&m.EditorSelection){m.EditorSelection.filterBlocks();}},_afterPaste:function(){var l=this.get(k),n=l.getInstance(),m=new n.EditorSelection();a.later(50,l,function(){n.EditorSelection.filterBlocks();});},initializer:function(){var l=this.get(k);if(l.editorBR){a.error("Can not plug EditorPara and EditorBR at the same time.");return;}l.on(c,a.bind(this._onNodeChange,this));l.after("ready",a.bind(this._afterEditorReady,this));l.after("contentChange",a.bind(this._afterContentChange,this));if(a.Env.webkit){l.after("dom:paste",a.bind(this._afterPaste,this));}}},{NAME:"editorPara",NS:"editorPara",ATTRS:{host:{value:false}}});a.namespace("Plugin");a.Plugin.EditorPara=d;},"3.5.0pr2",{requires:["editor-base"],skinnable:false});