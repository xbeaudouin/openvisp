/*
YUI 3.5.0pr2 (build 4560)
Copyright 2011 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/
YUI.add("dial",function(g){var f=false;if(g.UA.ie&&g.UA.ie<9){f=true;}var e=g.Lang,d=g.Widget,b=g.Node;function a(h){a.superclass.constructor.apply(this,arguments);}a.NAME="dial";a.ATTRS={min:{value:-220},max:{value:220},diameter:{value:100},handleDiameter:{value:0.2},markerDiameter:{value:0.1},centerButtonDiameter:{value:0.5},value:{value:0,validator:function(h){return this._validateValue(h);}},minorStep:{value:1},majorStep:{value:10},stepsPerRevolution:{value:100},decimalPlaces:{value:0},strings:{valueFn:function(){return g.Intl.get("dial");}},handleDistance:{value:0.75}};function c(h){return g.ClassNameManager.getClassName(a.NAME,h);}a.CSS_CLASSES={label:c("label"),labelString:c("label-string"),valueString:c("value-string"),northMark:c("north-mark"),ring:c("ring"),ringVml:c("ring-vml"),marker:c("marker"),markerVml:c("marker-vml"),markerMaxMin:c("marker-max-min"),centerButton:c("center-button"),centerButtonVml:c("center-button-vml"),resetString:c("reset-string"),handle:c("handle"),handleVml:c("handle-vml"),hidden:c("hidden"),dragging:g.ClassNameManager.getClassName("dd-dragging")};a.LABEL_TEMPLATE='<div class="'+a.CSS_CLASSES.label+'"><span id="" class="'+a.CSS_CLASSES.labelString+'">{label}</span><span class="'+a.CSS_CLASSES.valueString+'"></span></div>';if(f===false){a.RING_TEMPLATE='<div class="'+a.CSS_CLASSES.ring+'"><div class="'+a.CSS_CLASSES.northMark+'"></div></div>';a.MARKER_TEMPLATE='<div class="'+a.CSS_CLASSES.marker+" "+a.CSS_CLASSES.hidden+'"></div>';a.CENTER_BUTTON_TEMPLATE='<div class="'+a.CSS_CLASSES.centerButton+'"><div class="'+a.CSS_CLASSES.resetString+" "+a.CSS_CLASSES.hidden+'">{resetStr}</div></div>';a.HANDLE_TEMPLATE='<div class="'+a.CSS_CLASSES.handle+'" aria-labelledby="" aria-valuetext="" aria-valuemax="" aria-valuemin="" aria-valuenow="" role="slider"  tabindex="0" title="{tooltipHandle}">';}else{a.RING_TEMPLATE='<div class="'+a.CSS_CLASSES.ring+" "+a.CSS_CLASSES.ringVml+'">'+'<div class="'+a.CSS_CLASSES.northMark+'"></div>'+'<v:oval strokecolor="#ceccc0" strokeweight="1px"><v:fill type=gradient color="#8B8A7F" color2="#EDEDEB" angle="45"/></v:oval>'+"</div>"+"";a.MARKER_TEMPLATE='<div class="'+a.CSS_CLASSES.markerVml+" "+a.CSS_CLASSES.hidden+'">'+'<v:oval stroked="false">'+'<v:fill opacity="20%" color="#000"/>'+"</v:oval>"+"</div>"+"";a.CENTER_BUTTON_TEMPLATE='<div class="'+a.CSS_CLASSES.centerButton+" "+a.CSS_CLASSES.centerButtonVml+'">'+'<v:oval strokecolor="#ceccc0" strokeweight="1px">'+'<v:fill type=gradient color="#C7C5B9" color2="#fefcf6" colors="35% #d9d7cb, 65% #fefcf6" angle="45"/>'+'<v:shadow on="True" color="#000" opacity="10%" offset="2px, 2px"/>'+"</v:oval>"+'<div class="'+a.CSS_CLASSES.resetString+" "+a.CSS_CLASSES.hidden+'">{resetStr}</div>'+"</div>"+"";a.HANDLE_TEMPLATE='<div class="'+a.CSS_CLASSES.handleVml+'" aria-labelledby="" aria-valuetext="" aria-valuemax="" aria-valuemin="" aria-valuenow="" role="slider"  tabindex="0" title="{tooltipHandle}">'+'<v:oval stroked="false">'+'<v:fill opacity="20%" color="#6C3A3A"/>'+"</v:oval>"+"</div>"+"";}g.extend(a,d,{renderUI:function(){this._renderLabel();this._renderRing();this._renderMarker();this._renderCenterButton();this._renderHandle();this.contentBox=this.get("contentBox");this._originalValue=this.get("value");this._minValue=this.get("min");this._maxValue=this.get("max");this._stepsPerRevolution=this.get("stepsPerRevolution");this._minTimesWrapped=(Math.floor(this._minValue/this._stepsPerRevolution-1));this._maxTimesWrapped=(Math.floor(this._maxValue/this._stepsPerRevolution+1));this._timesWrapped=0;this._angle=this._getAngleFromValue(this.get("value"));this._prevAng=this._angle;this._setTimesWrappedFromValue(this._originalValue);this._handleNode.set("aria-valuemin",this._minValue);this._handleNode.set("aria-valuemax",this._maxValue);},_setBorderRadius:function(){this._ringNode.setStyles({"WebkitBorderRadius":this._ringNodeRadius+"px","MozBorderRadius":this._ringNodeRadius+"px","borderRadius":this._ringNodeRadius+"px"});this._handleNode.setStyles({"WebkitBorderRadius":this._handleNodeRadius+"px","MozBorderRadius":this._handleNodeRadius+"px","borderRadius":this._handleNodeRadius+"px"});this._markerNode.setStyles({"WebkitBorderRadius":this._markerNodeRadius+"px","MozBorderRadius":this._markerNodeRadius+"px","borderRadius":this._markerNodeRadius+"px"});this._centerButtonNode.setStyles({"WebkitBorderRadius":this._centerButtonNodeRadius+"px","MozBorderRadius":this._centerButtonNodeRadius+"px","borderRadius":this._centerButtonNodeRadius+"px"});},_handleCenterButtonEnter:function(){this._resetString.removeClass(a.CSS_CLASSES.hidden);},_handleCenterButtonLeave:function(){this._resetString.addClass(a.CSS_CLASSES.hidden);},bindUI:function(){this.after("valueChange",this._afterValueChange);var h=this.get("boundingBox"),j=(!g.UA.opera)?"down:":"press:",i=j+"38,40,33,34,35,36",l=j+"37,39",k=j+"37+meta,39+meta";g.on("key",g.bind(this._onDirectionKey,this),h,i);g.on("key",g.bind(this._onLeftRightKey,this),h,l);h.on("key",this._onLeftRightKeyMeta,k,this);g.on("mouseenter",g.bind(this._handleCenterButtonEnter,this),this._centerButtonNode);g.on("mouseleave",g.bind(this._handleCenterButtonLeave,this),this._centerButtonNode);g.on("gesturemovestart",g.bind(this._resetDial,this),this._centerButtonNode);g.on("gesturemoveend",g.bind(this._handleCenterButtonMouseup,this),this._centerButtonNode);g.on("gesturemovestart",g.bind(this._handleHandleMousedown,this),this._handleNode);g.on("gesturemovestart",g.bind(this._handleMousedown,this),this._ringNode);g.on("gesturemoveend",g.bind(this._handleRingMouseup,this),this._ringNode);this._dd1=new g.DD.Drag({node:this._handleNode,on:{"drag:drag":g.bind(this._handleDrag,this),"drag:start":g.bind(this._handleDragStart,this),"drag:end":g.bind(this._handleDragEnd,this)}});g.bind(this._dd1.addHandle(this._ringNode),this);},_setTimesWrappedFromValue:function(h){if(h%this._stepsPerRevolution===0){this._timesWrapped=(h/this._stepsPerRevolution);}else{this._timesWrapped=Math.floor(h/this._stepsPerRevolution);
}},_getAngleFromHandleCenter:function(j,i){var h=Math.atan((this._dialCenterY-i)/(this._dialCenterX-j))*(180/Math.PI);h=((this._dialCenterX-j)<0)?h+90:h+90+180;return h;},_calculateDialCenter:function(){this._dialCenterX=this._ringNode.get("offsetWidth")/2;this._dialCenterY=this._ringNode.get("offsetHeight")/2;},_handleRingMouseup:function(){this._handleNode.focus();},_handleCenterButtonMouseup:function(){this._handleNode.focus();},_handleHandleMousedown:function(){this._handleNode.focus();},_handleDrag:function(j){var l,k,h,i;l=(parseInt(this._handleNode.getStyle("left"),10)+this._handleNodeRadius);k=(parseInt(this._handleNode.getStyle("top"),10)+this._handleNodeRadius);h=this._getAngleFromHandleCenter(l,k);if((this._prevAng>270)&&(h<90)){if(this._timesWrapped<this._maxTimesWrapped){this._timesWrapped=(this._timesWrapped+1);}}else{if((this._prevAng<90)&&(h>270)){if(this._timesWrapped>this._minTimesWrapped){this._timesWrapped=(this._timesWrapped-1);}}}i=this._getValueFromAngle(h);if(i>(this._maxValue+this._stepsPerRevolution)){this._timesWrapped--;}else{if(i<(this._minValue-this._stepsPerRevolution)){this._timesWrapped++;}}this._prevAng=h;this._handleValuesBeyondMinMax(j,i);},_handleMousedown:function(m){var k=this._getAngleFromValue(this._minValue),j=this._getAngleFromValue(this._maxValue),l,i,o,n,h;if(g.UA.ios){o=(m.clientX-this._ringNode.getX());n=(m.clientY-this._ringNode.getY());}else{o=(m.clientX+g.one("document").get("scrollLeft")-this._ringNode.getX());n=(m.clientY+g.one("document").get("scrollTop")-this._ringNode.getY());}h=this._getAngleFromHandleCenter(o,n);if(this._maxValue-this._minValue>this._stepsPerRevolution){if(Math.abs(this._prevAng-h)>180){if((this._timesWrapped>this._minTimesWrapped)&&(this._timesWrapped<this._maxTimesWrapped)){this._timesWrapped=((this._prevAng-h)>0)?(this._timesWrapped+1):(this._timesWrapped-1);}}else{if((this._timesWrapped===this._minTimesWrapped)&&(h-this._prevAng<180)){this._timesWrapped++;}}}else{if(this._maxValue-this._minValue===this._stepsPerRevolution){if(h<k){this._timesWrapped=1;}else{this._timesWrapped=0;}}else{if(k>j){if((this._prevAng>=k)&&(h<=(k+j)/2)){this._timesWrapped++;}else{if((this._prevAng<=j)&&(h>(k+j)/2)){this._timesWrapped--;}}}else{if((h<k)||(h>j)){i=(((k+j)/2)+180)%360;if(i>180){l=((j<h)&&(h<i))?this.get("max"):this.get("min");}else{l=((k>h)&&(h>i))?this.get("min"):this.get("max");}this._prevAng=this._getAngleFromValue(l);this.set("value",l);this._setTimesWrappedFromValue(l);return;}}}}l=this._getValueFromAngle(h);this._prevAng=h;this._handleValuesBeyondMinMax(m,l);},_handleValuesBeyondMinMax:function(i,h){if((h>=this._minValue)&&(h<=this._maxValue)){this.set("value",h);if(i.currentTarget===this._ringNode){this._dd1._handleMouseDownEvent(i);}}else{if(h>this._maxValue){this.set("value",this._maxValue);if(i.type==="gesturemovestart"){this._prevAng=this._getAngleFromValue(this._maxValue);}}else{if(h<this._minValue){this.set("value",this._minValue);if(i.type==="gesturemovestart"){this._prevAng=this._getAngleFromValue(this._minValue);}}}}},_handleDragStart:function(h){this._markerNode.removeClass(a.CSS_CLASSES.hidden);},_handleDragEnd:function(){var h=this._handleNode;h.transition({duration:0.08,easing:"ease-in",left:this._setNodeToFixedRadius(this._handleNode,true)[0]+"px",top:this._setNodeToFixedRadius(this._handleNode,true)[1]+"px"},g.bind(function(){var i=this.get("value");if((i>this._minValue)&&(i<this._maxValue)){this._markerNode.addClass(a.CSS_CLASSES.hidden);}else{this._setTimesWrappedFromValue(i);this._prevAng=this._getAngleFromValue(i);}},this));},_setNodeToFixedRadius:function(k,n){var i=(this._angle-90),h=(Math.PI/180),j=Math.round(Math.sin(i*h)*this._handleDistance),m=Math.round(Math.cos(i*h)*this._handleDistance),l=k.get("offsetWidth");j=j-(l*0.5);m=m-(l*0.5);if(n){return[(this._ringNodeRadius+m),(this._ringNodeRadius+j)];}else{k.setStyle("left",(this._ringNodeRadius+m)+"px");k.setStyle("top",(this._ringNodeRadius+j)+"px");}},syncUI:function(){this._setSizes();this._calculateDialCenter();this._setBorderRadius();this._uiSetValue(this.get("value"));this._markerNode.addClass(a.CSS_CLASSES.hidden);this._resetString.addClass(a.CSS_CLASSES.hidden);},_setSizes:function(){var k=this.get("diameter"),j,l,i,h=function(n,p,m){var o="px";n.getElementsByTagName("oval").setStyle("width",(p*m)+o);n.getElementsByTagName("oval").setStyle("height",(p*m)+o);n.setStyle("width",(p*m)+o);n.setStyle("height",(p*m)+o);};h(this._ringNode,k,1);h(this._handleNode,k,this.get("handleDiameter"));h(this._markerNode,k,this.get("markerDiameter"));h(this._centerButtonNode,k,this.get("centerButtonDiameter"));this._ringNodeRadius=this._ringNode.get("offsetWidth")*0.5;this._handleNodeRadius=this._handleNode.get("offsetWidth")*0.5;this._markerNodeRadius=this._markerNode.get("offsetWidth")*0.5;this._centerButtonNodeRadius=this._centerButtonNode.get("offsetWidth")*0.5;this._handleDistance=this._ringNodeRadius*this.get("handleDistance");j=(this._ringNodeRadius-this._centerButtonNodeRadius);this._centerButtonNode.setStyle("left",j+"px");this._centerButtonNode.setStyle("top",j+"px");l=(this._centerButtonNodeRadius-(this._resetString.get("offsetWidth")*0.5));i=(this._centerButtonNodeRadius-(this._resetString.get("offsetHeight")*0.5));this._resetString.setStyles({"left":l+"px","top":i+"px"});},_renderLabel:function(){var h=this.get("contentBox"),i=h.one("."+a.CSS_CLASSES.label);if(!i){i=b.create(g.substitute(a.LABEL_TEMPLATE,this.get("strings")));h.append(i);}this._labelNode=i;this._valueStringNode=this._labelNode.one("."+a.CSS_CLASSES.valueString);},_renderRing:function(){var h=this.get("contentBox"),i=h.one("."+a.CSS_CLASSES.ring);if(!i){i=h.appendChild(a.RING_TEMPLATE);i.setStyles({width:this.get("diameter")+"px",height:this.get("diameter")+"px"});}this._ringNode=i;},_renderMarker:function(){var i=this.get("contentBox"),h=i.one("."+a.CSS_CLASSES.marker);if(!h){h=i.one("."+a.CSS_CLASSES.ring).appendChild(a.MARKER_TEMPLATE);}this._markerNode=h;},_renderCenterButton:function(){var h=this.get("contentBox"),i=h.one("."+a.CSS_CLASSES.centerButton);
if(!i){i=b.create(g.substitute(a.CENTER_BUTTON_TEMPLATE,this.get("strings")));h.one("."+a.CSS_CLASSES.ring).append(i);}this._centerButtonNode=i;this._resetString=this._centerButtonNode.one("."+a.CSS_CLASSES.resetString);},_renderHandle:function(){var j=a.CSS_CLASSES.label+g.guid(),h=this.get("contentBox"),i=h.one("."+a.CSS_CLASSES.handle);if(!i){i=b.create(g.substitute(a.HANDLE_TEMPLATE,this.get("strings")));i.setAttribute("aria-labelledby",j);this._labelNode.one("."+a.CSS_CLASSES.labelString).setAttribute("id",j);h.one("."+a.CSS_CLASSES.ring).append(i);}this._handleNode=i;},_setLabelString:function(h){this.get("contentBox").one("."+a.CSS_CLASSES.labelString).setContent(h);},_setResetString:function(h){this.get("contentBox").one("."+a.CSS_CLASSES.resetString).setContent(h);},_setTooltipString:function(h){this._handleNode.set("title",h);},_onDirectionKey:function(h){h.preventDefault();switch(h.charCode){case 38:this._incrMinor();break;case 40:this._decrMinor();break;case 36:this._setToMin();break;case 35:this._setToMax();break;case 33:this._incrMajor();break;case 34:this._decrMajor();break;}},_onLeftRightKey:function(h){h.preventDefault();switch(h.charCode){case 37:this._decrMinor();break;case 39:this._incrMinor();break;}},_onLeftRightKeyMeta:function(h){h.preventDefault();switch(h.charCode){case 37:this._setToMin();break;case 39:this._setToMax();break;}},_incrMinor:function(){var h=(this.get("value")+this.get("minorStep"));h=Math.min(h,this.get("max"));this.set("value",h.toFixed(this.get("decimalPlaces"))-0);},_decrMinor:function(){var h=(this.get("value")-this.get("minorStep"));h=Math.max(h,this.get("min"));this.set("value",h.toFixed(this.get("decimalPlaces"))-0);},_incrMajor:function(){var h=(this.get("value")+this.get("majorStep"));h=Math.min(h,this.get("max"));this.set("value",h.toFixed(this.get("decimalPlaces"))-0);},_decrMajor:function(){var h=(this.get("value")-this.get("majorStep"));h=Math.max(h,this.get("min"));this.set("value",h.toFixed(this.get("decimalPlaces"))-0);},_setToMax:function(){this.set("value",this.get("max"));},_setToMin:function(){this.set("value",this.get("min"));},_resetDial:function(h){if(h){h.stopPropagation();}this.set("value",this._originalValue);this._resetString.addClass(a.CSS_CLASSES.hidden);this._handleNode.focus();},_getAngleFromValue:function(h){var j=h%this._stepsPerRevolution,i=j/this._stepsPerRevolution*360;return(i<0)?(i+360):i;},_getValueFromAngle:function(i){if(i<0){i=(360+i);}else{if(i===0){i=360;}}var h=(i/360)*this._stepsPerRevolution;h=(h+(this._timesWrapped*this._stepsPerRevolution));return h.toFixed(this.get("decimalPlaces"))-0;},_afterValueChange:function(h){this._uiSetValue(h.newVal);},_valueToDecimalPlaces:function(h){},_uiSetValue:function(h){this._angle=this._getAngleFromValue(h);if(this._handleNode.hasClass(a.CSS_CLASSES.dragging)===false){this._setTimesWrappedFromValue(h);this._setNodeToFixedRadius(this._handleNode,false);this._prevAng=this._getAngleFromValue(this.get("value"));}this._valueStringNode.setContent(h.toFixed(this.get("decimalPlaces")));this._handleNode.set("aria-valuenow",h);this._handleNode.set("aria-valuetext",h);this._setNodeToFixedRadius(this._markerNode,false);if((h===this._maxValue)||(h===this._minValue)){this._markerNode.addClass(a.CSS_CLASSES.markerMaxMin);if(f===true){this._markerNode.getElementsByTagName("fill").set("color","#AB3232");}this._markerNode.removeClass(a.CSS_CLASSES.hidden);}else{if(f===true){this._markerNode.getElementsByTagName("fill").set("color","#000");}this._markerNode.removeClass(a.CSS_CLASSES.markerMaxMin);if(this._handleNode.hasClass(a.CSS_CLASSES.dragging)===false){this._markerNode.addClass(a.CSS_CLASSES.hidden);}}},_validateValue:function(j){var i=this.get("min"),h=this.get("max");return(e.isNumber(j)&&j>=i&&j<=h);}});g.Dial=a;},"3.5.0pr2",{requires:["widget","dd-drag","substitute","event-mouseenter","event-move","event-key","transition","intl"],lang:["en","es"],skinnable:true});