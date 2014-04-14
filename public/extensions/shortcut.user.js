// ==UserScript==
// @name           Shortcuts
// @description    Zeigt Welt und Laufzeit im Tab-Titel
// @include        http://*.freewar.de/freewar/internal/*.php*
// @version        1.0
// @grant          none
// ==/UserScript==

var gl_shortcutKey = false,
    gl_shortcutKeyType = 'strg';
function pass_shortcut(event) {   
  if (!event) {
    if (window.event) {
      event = window.event;   
    } else {
      return;
    }        
  }
  var i = 0,
      link = null,
      shiftKeyTransform = {
        1: [49, 33],
        2: [222, 34, 50],
        3: [51, 0, 167], 
        4: [52, 36],
        5: [53, 37],
        6: [55, 38],
        7: [191, 39],
        8: [57, 40],
        9: [48, 41]        
      },
      shortcut = -1,
      transformedKey,
      frame = top.itemFrame || top.frames.itemFrame,
      target = event.target || event.srcElement,
      key = event.keyCode || event.which;
    
  switch (gl_shortcutKeyType) {
    case 'alt' && (event.altKey   === true   || key === 18): 
      // fallthrough
    case 'strg'  && (event.ctrlKey  === true   || key === 17): 
      // fallthrough
    case 'shift' && (event.shiftKey === true   || key === 16):
      if (event.type === 'keydown') {
          gl_shortcutKey = true;
      }
      else {
          gl_shortcutKey = false;
      }
      break;
  }
    
  if (gl_shortcutKey === true) {
    // bei shift wird zweitbelegung übergeben (!"§$% etc)
    if (frame.gl_shortcutKeyType === 'shift') {
      for (transformedKey in shiftKeyTransform) {
        for (i = 0; i < shiftKeyTransform[transformedKey].length; ++i) {                    
          if (shiftKeyTransform[transformedKey][i] === key) {
            shortcut = transformedKey;
            break;
          }
        }
      }
    } else {
      shortcut = key - 48;
    }   

    if (link = getElementById('fastspell' + shortcut, frame)) {
      if (link.getElementsByTagName('a').length > 1) {
        // Shortcut beim schreiben
        if (frame.gl_shortcutKeyType === 'shift' && 
            (
             (target.tagName.toLowerCase() === "input" && target.type.toLowerCase() === "text") ||
             (target.tagName.toLowerCase() === "textarea")
            )                        
            && target.value.length
           ) 
        { 
          return;
        }

        link = link.getElementsByTagName('a')[0];
        frame.location.href = link.href;
        frame.focus();
      }           
    }    
  }
}

/*
 * cross-browser addEventListern from http://snipplr.com/view/12705/addeventlistener-crossbrowser-fix/
 */
function addEvent(obj, type, fn) {
  if (obj.addEventListener) {
		obj.addEventListener(type,fn,false);
		return true;
	} else if (obj.attachEvent) {
		obj['e'+type+fn] = fn;
		obj[type+fn] = function() {obj['e'+type+fn]( window.event );};
		var r = obj.attachEvent('on'+type, obj[type+fn]);
		return r;
	} else {
		obj['on'+type] = fn;
		return true;
	}
}

function getElementById (id, context) {
  if (!context) {
    context = window;
  }   
  if (document.layers) {
    return context.document.layers[id];
  } else if (document.all) {
    return context.document.all[id];
  } else if (document.getElementById) {
    return context.document.getElementById(id);
  }
  return null;
}

addEvent(document, 'keydown', pass_shortcut);
addEvent(document, 'keyup',   pass_shortcut);