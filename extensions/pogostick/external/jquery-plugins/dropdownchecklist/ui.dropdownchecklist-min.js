(function(a){a.widget("ui.dropdownchecklist",{_appendDropContainer:function(){var c=a("<div/>");c.addClass("ui-dropdownchecklist-dropcontainer-wrapper");c.css({position:"absolute",left:"-3300",top:"-3300px",width:"3000px",height:"3000px"});var b=a("<div/>");b.addClass("ui-dropdownchecklist-dropcontainer").css("overflow-y","auto");c.append(b);c.insertAfter(this.sourceSelect);c.drop=false;return c},_isDropDownKeyShortcut:function(b){return b.altKey&&(a.ui.keyCode.DOWN==(b.keyCode||b.which))},_isDroDownCloseKey:function(b){return a.ui.keyCode.ESCAPE==(b.keyCode||b.which)},_handleKeyboard:function(c){var b=this;if(b._isDropDownKeyShortcut(c)){c.stopPropagation();b._toggleDropContainer();b.dropWrapper.find("input:first").focus()}else{if(b.dropWrapper.drop&&b._isDroDownCloseKey(c)){b._toggleDropContainer()}}},_appendControl:function(){var b=this,c=this.options,d=this.sourceSelect;var g=a("<span/>");g.addClass("ui-dropdownchecklist-wrapper");g.css({display:"inline-block",cursor:"default"});var f=a("<span/>");f.addClass("ui-dropdownchecklist");f.css({display:"inline-block"});f.attr("tabIndex",0);f.keyup(function(h){b._handleKeyboard(h)});g.append(f);var e=a("<span/>");e.addClass("ui-dropdownchecklist-text");e.css({display:"inline-block",overflow:"hidden"});f.append(e);g.hover(function(){if(!b.disabled){f.toggleClass("ui-dropdownchecklist-hover")}},function(){if(!b.disabled){f.toggleClass("ui-dropdownchecklist-hover")}});g.click(function(h){if(!b.disabled){h.stopPropagation();b._toggleDropContainer()}});g.insertAfter(d);return g},_createDropItem:function(g,i,k,j,d){var m=this;var l=a("<div/>");l.addClass("ui-dropdownchecklist-item");l.css({whiteSpace:"nowrap"});var c=j?' checked="checked"':"";var n=(m.sourceSelect.attr("id")||"ddcl");var b=n+g;var f;if(m.initialMultiple){f=a('<input type="checkbox" id="'+b+'"'+c+"/>")}else{f=a('<input type="radio" id="'+b+'" name="'+n+'"'+c+"/>")}f=f.attr("index",g).val(i);l.append(f);var h=a("<label for="+b+"/>");h.addClass("ui-dropdownchecklist-text").css({cursor:"default",width:"100%"}).text(k);if(d){l.addClass("ui-dropdownchecklist-indent")}l.append(h);l.hover(function(){l.addClass("ui-dropdownchecklist-item-hover")},function(){l.removeClass("ui-dropdownchecklist-item-hover")});f.click(function(o){o.stopPropagation();m._syncSelected(a(this));m.sourceSelect.trigger("change")});var e=function(p){p.stopPropagation();var o=f.attr("checked");f.attr("checked",!o);m._syncSelected(f);m.sourceSelect.trigger("change")};h.click(function(o){o.stopPropagation()});l.click(e);l.keyup(function(o){m._handleKeyboard(o)});return l},_createGroupItem:function(e){var b=this;var d=a("<div />");d.addClass("ui-dropdownchecklist-group");d.css({whiteSpace:"nowrap"});var c=a("<span/>");c.addClass("ui-dropdownchecklist-text").css({cursor:"default",width:"100%"}).text(e);d.append(c);return d},_appendItems:function(){var d=this,h=this.sourceSelect,f=this.controlWrapper,g=this.dropWrapper;var b=g.find(".ui-dropdownchecklist-dropcontainer");b.css({"float":"left"});h.children().each(function(i){var j=a(this);if(j.is("option")){d._appendOption(j,b,i,false)}else{var l=j.attr("label");var k=d._createGroupItem(l);b.append(k);d._appendOptions(j,b,i,true)}});var c=b.outerWidth();var e=b.outerHeight();b.css({"float":""});return{width:c,height:e}},_appendOptions:function(f,c,e,b){var d=this;f.children("option").each(function(g){var h=a(this);var i=(e+"."+g);d._appendOption(h,c,i,b)})},_appendOption:function(e,b,f,c){var j=this;var h=e.text();var g=e.val();var d=e.attr("selected");var i=j._createDropItem(f,g,h,d,c);b.append(i)},_syncSelected:function(h){var i=this,k=this.options,c=this.sourceSelect,g=this.controlWrapper,d=this.dropWrapper;var f=d.find("input");if(k.firstItemChecksAll){if(h.attr("index")==0){f.attr("checked",h.attr("checked"))}else{var e;e=true;f.each(function(l){if(l>0){var m=a(this).attr("checked");if(!m){e=false}}});var j=f.filter(":first");j.attr("checked",false);if(e){j.attr("checked",true)}}}var b=c.get(0).options;f.each(function(l){a(b[l]).attr("selected",a(this).attr("checked"))});i._updateControlText()},_updateControlText:function(){var i=this,b=this.sourceSelect,j=this.options,f=this.controlWrapper,d=this.dropWrapper;var k=b.find("option:first");var g=null!=k&&k.attr("selected");var c=b.find("option");var h=i._formatText(c,j.firstItemChecksAll,g);var e=f.find(".ui-dropdownchecklist-text");e.text(h);e.attr("title",h)},_formatText:function(c,d,b){var e;if(d&&b){e=c.filter(":first").text()}else{e="";c.each(function(){if(a(this).attr("selected")){e+=a(this).text()+", "}});if(e.length>0){e=e.substring(0,e.length-2)}}return e},_toggleDropContainer:function(){var c=this,f=this.dropWrapper,e=this.controlWrapper;var d=function(){var g=a.ui.dropdownchecklist.drop;if(null!=g){g.dropWrapper.css({top:"-3300px",left:"-3300px"});g.controlWrapper.find(".ui-dropdownchecklist").toggleClass("ui-dropdownchecklist-active");g.dropWrapper.find("input").attr("tabIndex",-1);g.dropWrapper.drop=false;a.ui.dropdownchecklist.drop=null;a(document).unbind("click",d);c.sourceSelect.trigger("blur")}};var b=function(g){if(null!=a.ui.dropdownchecklist.drop){d()}g.dropWrapper.css({top:g.controlWrapper.offset().top+g.controlWrapper.outerHeight()+"px",left:g.controlWrapper.offset().left+"px"});var i=e.parents().map(function(){var j=a(this).css("z-index");return isNaN(j)?0:j}).get();var h=Math.max.apply(Math,i);if(h>0){g.dropWrapper.css({zIndex:(h+1)})}g.controlWrapper.find(".ui-dropdownchecklist").toggleClass("ui-dropdownchecklist-active");g.dropWrapper.find("input").attr("tabIndex",0);g.dropWrapper.drop=true;a.ui.dropdownchecklist.drop=g;a(document).bind("click",d);c.sourceSelect.trigger("focus")};if(f.drop){d(c)}else{b(c)}},_setSize:function(b){var j=this.options,e=this.dropWrapper,h=this.controlWrapper;var g;if(j.width){g=parseInt(j.width)}else{g=b.width;var c=j.minWidth;if(g<c){g=c}}h.find(".ui-dropdownchecklist-text").css({width:g+"px"});var i=h.outerWidth();var f=j.maxDropHeight?parseInt(j.maxDropHeight):b.height;var d=b.width<i?i:b.width;a(e).css({width:d+"px",height:f+"px"});e.find(".ui-dropdownchecklist-dropcontainer").css({height:f+"px"})},_init:function(){var c=this,d=this.options;var g=c.element;c.initialDisplay=g.css("display");g.css("display","none");c.initialMultiple=g.attr("multiple");g.attr("multiple","multiple");c.sourceSelect=g;var f=c._appendDropContainer();c.dropWrapper=f;var b=c._appendItems();var e=c._appendControl();c.controlWrapper=e;c._updateControlText(e,f,g);c._setSize(b);if(d.bgiframe&&typeof c.dropWrapper.bgiframe=="function"){c.dropWrapper.bgiframe()}},enable:function(){this.controlWrapper.find(".ui-dropdownchecklist").removeClass("ui-dropdownchecklist-disabled");this.disabled=false},disable:function(){this.controlWrapper.find(".ui-dropdownchecklist").addClass("ui-dropdownchecklist-disabled");this.disabled=true},destroy:function(){a.widget.prototype.destroy.apply(this,arguments);this.sourceSelect.css("display",this.initialDisplay);this.sourceSelect.attr("multiple",this.initialMultiple);this.controlWrapper.unbind().remove();this.dropWrapper.remove()}});a.extend(a.ui.dropdownchecklist,{defaults:{width:null,maxDropHeight:null,firstItemChecksAll:false,minWidth:50,bgiframe:false}})})(jQuery);