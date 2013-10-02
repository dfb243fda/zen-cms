zen = {
	version: 0.1
}

zen.apply = function(o, c, defaults){
    // no "this" reference for friendly out of scope calls
    if(defaults){
        zen.apply(o, defaults);
    }
    if(o && c && typeof c == 'object'){
        for(var p in c){
            o[p] = c[p];
        }
    }
    return o;
};

(function(){
	toString = Object.prototype.toString,
	ua = navigator.userAgent.toLowerCase(),
	check = function(r){
		return r.test(ua);
	},
	DOC = document,
	docMode = DOC.documentMode,
	isStrict = DOC.compatMode == "CSS1Compat",
	isOpera = check(/opera/),
	isChrome = check(/\bchrome\b/),
	isWebKit = check(/webkit/),
	isSafari = !isChrome && check(/safari/),
	isSafari2 = isSafari && check(/applewebkit\/4/), // unique to Safari 2
	isSafari3 = isSafari && check(/version\/3/),
	isSafari4 = isSafari && check(/version\/4/),
	isIE = !isOpera && check(/msie/),
	isIE7 = isIE && (check(/msie 7/) || docMode == 7),
	isIE8 = isIE && (check(/msie 8/) && docMode != 7),
	isIE6 = isIE && !isIE7 && !isIE8,
	isGecko = !isWebKit && check(/gecko/),
	isGecko2 = isGecko && check(/rv:1\.8/),
	isGecko3 = isGecko && check(/rv:1\.9/),
	isBorderBox = isIE && !isStrict,
	isWindows = check(/windows|win32/),
	isMac = check(/macintosh|mac os x/),
	isAir = check(/adobeair/),
	isLinux = check(/linux/),
	isSecure = /^https/i.test(window.location.protocol);
})();

zen.apply(zen, {
	isEmpty : function(v, allowBlank){
		return v === null || v === undefined || ((zen.isArray(v) && !v.length)) || (!allowBlank ? v === '' : false);
	},
	isDefined: function(v) {
		return typeof v !== 'undefined';
	},
	isString : function(v){
		return typeof v === 'string';
	},
	isFunction : function(v){
		return toString.apply(v) === '[object Function]';
	},
	isNumber : function(v){
		return (typeof(v) === 'number' || typeof(v) === 'string') && v !== '' && !isNaN(parseFloat(v));
	},
	isObject : function(v){
		return !!v && Object.prototype.toString.call(v) === '[object Object]';
	},
	isEmptyObject: function (a) {
		for (var b in a) return !1;
		return !0
	},
	isArray : function(v){
		return toString.apply(v) === '[object Array]';
	},
	toArray : function(){
		return isIE ?
			function(a, i, j, res) {
				res = [];
				for(var x = 0, len = a.length; x < len; x++) {
					res.push(a[x]);
				}
				return res.slice(i || 0, j || res.length);
			} :

			function(a, i, j){
				return Array.prototype.slice.call(a, i || 0, j || a.length);
			};
	},
	rand: function (min, max) {
		var argc = arguments.length;
		if (argc === 0) {
			min = 0;
			max = 2147483647;
		} else if (argc === 1) {
			throw new Error('Warning: rand() expects exactly 2 parameters, 1 given');
		}
		return Math.floor(Math.random() * (max - min + 1)) + min;
	},	
	trim: function(str, charlist) {
		i = 0;
		str += '';

		if (!charlist) {        // default list
			whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
		} else {
			// preg_quote custom list
			charlist += '';
			whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
		}

		l = str.length;
		for (i = 0; i < l; i++) {
			if (whitespace.indexOf(str.charAt(i)) === -1) {
				str = str.substring(i);
				break;
			}
		}
		l = str.length;
		for (i = l - 1; i >= 0; i--) {
			if (whitespace.indexOf(str.charAt(i)) === -1) {
				str = str.substring(0, i + 1);
				break;
			}
		}

		return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
	},
	
	strip_tags: function(input, allowed) {
		allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
		var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
			commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
		return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
			return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
		});
	},
	
	array_merge : function() {
		var args = Array.prototype.slice.call(arguments),
			retObj = {},
			k, j = 0,        i = 0,
			retArr = true;

		for (i = 0; i < args.length; i++) {
			if (!(args[i] instanceof Array)) {
				retArr = false;
				break;
			}
		}
		 if (retArr) {
			retArr = [];
			for (i = 0; i < args.length; i++) {
				retArr = retArr.concat(args[i]);
			}
			return retArr;
		}
		var ct = 0;

		for (i = 0, ct = 0; i < args.length; i++) {
			if (args[i] instanceof Array) {
				for (j = 0; j < args[i].length; j++) {
					retObj[ct++] = args[i][j];
				}
			} else {
				for (k in args[i]) {
					if (args[i].hasOwnProperty(k)) {
						if (parseInt(k, 10) + '' === k) {
							retObj[ct++] = args[i][k];
						} else {
							retObj[k] = args[i][k];
						}
					}
				}
			}
		}
		return retObj;
	},

	invoke: function(methodName, params) {
		var arr = methodName.split('.');
		var parent = ((function () {return this;}).call(null)); // get global context
		var funcName = arr.pop();
		
		for(var i = 0; i < arr.length; i++) {
			parent = parent[arr[i]];
		}
		return parent[funcName].apply(parent, params);
	},
	observer : {
		observers : {},
		registerObserver : function(event, callback, args, observer_id) {
			if (!zen.isDefined(args)) {
				args = [];
			}
			if (!zen.isDefined(this.observers[event])) {
				this.observers[event] = [];
			}
			if (!zen.isDefined(observer_id)) {
				observer_id = false;
			}

			this.observers[event].push({
				id: observer_id,
				callback : callback,
				args : args
			});
		},
		fireEvent : function(event) {

			var args = Array.prototype.slice.call(arguments);
			args = args.slice(1);

			if (zen.isDefined(this.observers[event])) {
				$.each(this.observers[event], function(index, value) {
					args = zen.array_merge(value['args'], args);
					value['callback'].apply(window, args);
				});
			}
		},
		clearObservers: function(event, observer_id) {		
			var me = this;
			
			if (zen.isDefined(observer_id)) {
				
				if (event == '') {
					$.each(me.observers, function(i, v) {
						me.observers[i] = $.map(me.observers[i], function(value) {
							if (value['id'] == observer_id) {
								return null;
							}
							return value;
						});
					});
				}
				else {
					if (zen.isDefined(me.observers[event])) {
						me.observers[event] = $.map(me.observers[event], function(value) {
							if (value['id'] == observer_id) {
								return null;
							}
							return value;
						});
					}
				}				
			}
			else {
				me.observers[event] = [];
			}			
		}
	},
	        
    history: {		
        hasPushedStates: false,
        histAPI: false,
        init: function() {
            this.histAPI = !!(window.history && history.pushState);
        },
        add: function(url) {
            this.hasPushedStates = true;
            if (this.histAPI == true) {
                window.history.pushState(null, null, url);	
            }
        },
        go: function(step) {
            window.history.go(step + 1 - window.history.length);
        },
        refresh: function() {
            if (this.histAPI == true) {
                var url = window.location.href;
                
                if (window.location.pathname.indexOf('.') == -1) {
                    url += '.json_html';
                }                
                zen.currentTheme.getAjaxContent(url, false);
            }
            else {
                window.location = window.location;
            }
        }		
    },
    
    includeResources: function(data) {
        
        var includeInlineScript = function() {
            if (zen.isDefined(data['inlineScript'])) {
                $.each(data['inlineScript'], function(i, v) {
                    eval(v['source']);
                });
            }
        };
        
     
        if (zen.isDefined(data['headLink'])) {
            $.each(data['headLink'], function(i, v) {
  //              yepnope.injectCss(v['href']);
                
                //if (conditionalStylesheet) @todo
               $('head').append('<link rel="' + v['rel'] + '" href="' + v['href'] + '" type="' + v['type'] + '" media="' + v['media'] + '" />');
            });
        }      
        
        if (zen.isDefined(data['headScript'])) {
            var scripts = [];
            $.each(data['headScript'], function(i, v) {
                scripts.push(v['attributes']['src']);
            });
            yepnope({
                load: scripts,
                callback: function() {
                    includeInlineScript();
                }
            });
        } else {
            includeInlineScript();
        }
        
        /*
        if (zen.isDefined(data['headScript'])) {
            var cnt = data['headScript'].length;
            
            var tmpCnt = 0;
            $.each(data['headScript'], function(i, v) {
                if ($('head script[src="' + v['attributes']['src'] + '"]').size() == 0) {
                    $.ajax({
                        url: v['attributes']['src'],
                        dataType: "script",
                        cache: true,
                        success: function() {
                            tmpCnt++;
                            if (cnt == tmpCnt) {
                                includeInlineScript();
                            }
                        }
                    });
                } else {
                    tmpCnt++;
                    if (cnt == tmpCnt) {
                        includeInlineScript();
                    }
                }
            });
        } else {
            includeInlineScript();
        }
        */
    }


});
