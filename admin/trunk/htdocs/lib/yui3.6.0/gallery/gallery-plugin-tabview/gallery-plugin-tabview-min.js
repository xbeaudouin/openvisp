YUI.add("gallery-plugin-tabview",function(b){var a="active";b.Plugin.TabView=b.Base.create("tabview",b.Plugin.Base,[],{history:null,initializer:function(){this.history=new b.HistoryHash();this.renderUI();this.bindUI();this.syncUI();},renderUI:function(){var f=this.get("host"),e=this.get("tabSelector"),c=this.get("panelSelector"),d=0;f.addClass("tabView");f.all(e).each(function(i){var g=i.one("a"),j=null,h="";if(!g||g.getAttribute("href").indexOf("#")<0){d++;return;}j=g.getAttribute("name")||g.getAttribute("id");if(j===""||j.indexOf("yui_")===0){h=i.get("id");if(h===""||h.indexOf("yui_")===0){j=d;}else{j=h;}}g.setAttribute("href",g.getAttribute("href").replace(/#(.)*/,"#tab="+j));d++;});f.all(c).each(function(h){if(h.one("*:first-child").hasClass("liner")){return;}var j=b.Node.create('<div class="liner" />'),m=h.get("children").remove(),k,g;for(k=0,g=m.size();k<g;k++){j.append(m.item(k));}h.append(j);});},bindUI:function(){var c=this.get("host");c.delegate("click",function(f){var d=f.currentTarget;f.preventDefault();this.history.addValue("tab",c.all(d.get("tagName")).indexOf(d));},this.get("tabSelector"),this);this.history.on("change",function(d){if(d.changed.tab){this._updateActiveTab(c.all(this.get("tabSelector")).item(parseInt(d.changed.tab.newVal,10)||0));}},this);},syncUI:function(){var f=this.get("host"),d=this.get("tabSelector"),c=0,e=null,g=null;if(window.location.hash){e=window.location.hash.replace("#","");if(e.indexOf("=")>0){c=e.substring(e.indexOf("=")+1);g=f.all(d).item(c);}else{g=f.one("#"+e+", "+d+"[name="+e+"], "+d+" *[name="+e+"]");if(g){if(g.get("tagName")!==d.toUpperCase()){g=g.ancestor(d);}c=f.all(d).indexOf(g);}}}this.history.addValue("tab",c);this._updateActiveTab(g);},_updateActiveTab:function(c){var d=this.get("host");if(c.hasClass(a)){return;}d.all("."+a).removeClass(a);c.addClass(a);c.next(this.get("panelSelector")).addClass(a);}},{NS:"tabview",ATTRS:{tabSelector:{value:"dt"},panelSelector:{value:"dd"}}});},"gallery-2010.11.17-21-32",{requires:["plugin","base-build","node","event","history"]});