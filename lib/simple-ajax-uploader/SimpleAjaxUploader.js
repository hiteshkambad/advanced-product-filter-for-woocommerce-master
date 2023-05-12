;
(function (global, factory) {
    if (typeof define === 'function' && define.amd) {
        define(function () {
            return factory(global);
        });

    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory(global);

    } else {
        global.ss = factory(global);
    }

}(typeof window !== 'undefined' ? window : this, function (window) {

    var ss = window.ss || {},
            
            rLWhitespace = /^\s+/,
            rTWhitespace = /\s+$/,
            
            uidReplace = /[xy]/g,
            
            rPath = /.*(\/|\\)/,
            
            rExt = /.*[.]/,
            
            rHasClass = /[\t\r\n]/g,
            
            isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0,

            isIE7to9 = (navigator.userAgent.indexOf('MSIE') !== -1 &&
                    navigator.userAgent.indexOf('MSIE 1') === -1),
            isIE7 = (navigator.userAgent.indexOf('MSIE 7') !== -1),

            input = document.createElement('input'),
            XhrOk;

    input.type = 'file';

    XhrOk = ('multiple' in input &&
            typeof File !== 'undefined' &&
            typeof (new XMLHttpRequest()).upload !== 'undefined');


    ss.obj2string = function (obj, prefix) {
        "use strict";

        var str = [];

        for (var prop in obj) {
            if (obj.hasOwnProperty(prop)) {
                var k = prefix ? prefix + '[' + prop + ']' : prop, v = obj[prop];
                str.push(typeof v === 'object' ?
                        ss.obj2string(v, k) :
                        encodeURIComponent(k) + '=' + encodeURIComponent(v));
            }
        }

        return str.join('&');
    };

    ss.extendObj = function (first, second) {
        "use strict";

        for (var prop in second) {
            if (second.hasOwnProperty(prop)) {
                first[prop] = second[prop];
            }
        }
    };

    ss.addEvent = function (elem, type, fn) {
        "use strict";

        if (elem.addEventListener) {
            elem.addEventListener(type, fn, false);

        } else {
            elem.attachEvent('on' + type, fn);
        }
        return function () {
            ss.removeEvent(elem, type, fn);
        };
    };

    ss.removeEvent = document.removeEventListener ?
            function (elem, type, fn) {
                if (elem.removeEventListener) {
                    elem.removeEventListener(type, fn, false);
                }
            } :
            function (elem, type, fn) {
                var name = 'on' + type;

                if (typeof elem[ name ] === 'undefined') {
                    elem[ name ] = null;
                }

                elem.detachEvent(name, fn);
            };

    ss.newXHR = function () {
        "use strict";

        if (typeof XMLHttpRequest !== 'undefined') {
            return new window.XMLHttpRequest();

        } else if (window.ActiveXObject) {
            try {
                return new window.ActiveXObject('Microsoft.XMLHTTP');
            } catch (err) {
                return false;
            }
        }
    };

    ss.encodeUTF8 = function (str) {
        "use strict";
        return unescape(encodeURIComponent(str));
    };

    ss.getIFrame = function () {
        "use strict";

        var id = ss.getUID(),
                iframe;

        
        if (isIE7) {
            iframe = document.createElement('<iframe src="javascript:false;" name="' + id + '">');

        } else {
            iframe = document.createElement('iframe');
            iframe.src = 'javascript:false;';
            iframe.name = id;
        }

        iframe.style.display = 'none';
        iframe.id = id;
        return iframe;
    };

    ss.getForm = function (opts) {
        "use strict";

        var form = document.createElement('form');

        form.encoding = 'multipart/form-data';
        form.enctype = 'multipart/form-data';
        form.style.display = 'none';

        for (var prop in opts) {
            if (opts.hasOwnProperty(prop)) {
                form[prop] = opts[prop];
            }
        }

        return form;
    };

    ss.getHidden = function (name, value) {
        "use strict";

        var input = document.createElement('input');

        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    };

    ss.parseJSON = function (data) {
        "use strict";
        

        if (!data) {
            return false;
        }

        data = ss.trim(data + '');

        if (window.JSON && window.JSON.parse) {
            try {
                return window.JSON.parse(data + '');
            } catch (err) {
                return false;
            }
        }

        var rvalidtokens = /(,)|(\[|{)|(}|])|"(?:[^"\\\r\n]|\\["\\\/bfnrt]|\\u[\da-fA-F]{4})*"\s*:?|true|false|null|-?(?!0\d)\d+(?:\.\d+|)(?:[eE][+-]?\d+|)/g,
                depth = null,
                requireNonComma;

        
        return data && !ss.trim(data.replace(rvalidtokens, function (token, comma, open, close) {

        
            if (requireNonComma && comma) {
                depth = 0;
            }

        
            if (depth === 0) {
                return token;
            }

          
            requireNonComma = open || comma;

          
            depth += !close - !open;

          
            return '';
        })) ?
                (Function('return ' + data))() :
                false;
    };

    ss.getBox = function (elem) {
        "use strict";

        var box,
                docElem,
                top = 0,
                left = 0;

        if (elem.getBoundingClientRect) {
            box = elem.getBoundingClientRect();
            docElem = document.documentElement;
            top = box.top + (window.pageYOffset || docElem.scrollTop) - (docElem.clientTop || 0);
            left = box.left + (window.pageXOffset || docElem.scrollLeft) - (docElem.clientLeft || 0);

        } else {
            do {
                left += elem.offsetLeft;
                top += elem.offsetTop;
            } while ((elem = elem.offsetParent));
        }

        return {
            top: Math.round(top),
            left: Math.round(left)
        };
    };

    
    ss.addStyles = function (elem, styles) {
        "use strict";

        for (var name in styles) {
            if (styles.hasOwnProperty(name)) {
                elem.style[name] = styles[name];
            }
        }
    };

    
    ss.copyLayout = function (from, to) {
        "use strict";

        var box = ss.getBox(from);

        ss.addStyles(to, {
            position: 'absolute',
            display: 'block',
            left: box.left + 'px',
            top: box.top + 'px',
            width: from.offsetWidth + 'px',
            height: from.offsetHeight + 'px'
        });
    };

    
    ss.getUID = function () {
        "use strict";

        
        return 'axxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(uidReplace, function (c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    };

    
    var trim = "".trim;

    ss.trim = trim && !trim.call("\uFEFF\xA0") ?
            function (text) {
                return text === null ?
                        "" :
                        trim.call(text);
            } :
            function (text) {
                return text === null ?
                        "" :
                        text.toString().replace(rLWhitespace, '').replace(rTWhitespace, '');
            };

    var arr = [];

    ss.indexOf = arr.indexOf ?
            function (array, elem) {
                return array.indexOf(elem);
            } :
            function (array, elem) {
                for (var i = 0, len = array.length; i < len; i++) {
                    if (array[i] === elem) {
                        return i;
                    }
                }
                return -1;
            };

    
    ss.arrayDelete = function (array, elem) {
        var index = ss.indexOf(array, elem);

        if (index > -1) {
            array.splice(index, 1);
        }
    };

    ss.getFilename = function (path) {
        "use strict";
        return path.replace(rPath, '');
    };

    ss.getExt = function (file) {
        "use strict";
        return (-1 !== file.indexOf('.')) ? file.replace(rExt, '') : '';
    };

    ss.isVisible = function (elem) {
        "use strict";

        if (!elem) {
            return false;
        }

        if (elem.nodeType !== 1 || elem == document.body) {
            elem = null;
            return true;
        }

        if (elem.parentNode &&
                (elem.offsetWidth > 0 ||
                        elem.offsetHeight > 0 ||
                        ss.getStyle(elem, 'display').toLowerCase() != 'none'))
        {
            return ss.isVisible(elem.parentNode);
        }

        elem = null;
        return false;
    };

    ss.getStyle = function (elem, style) {
        "use strict";

        if (window.getComputedStyle) {
            var cs = elem.ownerDocument.defaultView.getComputedStyle(elem, null);
            return cs.getPropertyValue(style);

        } else if (elem.currentStyle && elem.currentStyle[ style ]) {
            return elem.currentStyle[ style ];
        }
    };

    ss.getFormObj = function (form) {
        "use strict";

        var elems = form.elements,
                ignore = ['button', 'submit', 'image', 'reset'],
                inputs = {},
                obj;

        for (var i = 0, len = elems.length; i < len; i++) {
            obj = {};

            if (elems[ i ].name && !elems[ i ].disabled && ss.indexOf(ignore, elems[ i ].type) === -1) {
                if ((elems[ i ].type == 'checkbox' || elems[ i ].type == 'radio') &&
                        !elems[ i ].checked)
                {
                    continue;
                }

                obj[ elems[ i ].name ] = ss.val(elems[ i ]);
                ss.extendObj(inputs, obj);
            }
        }

        return inputs;
    };

    ss.val = function (elem) {
        "use strict";

        if (!elem) {
            return;
        }

        if (elem.nodeName.toUpperCase() == 'SELECT') {
            var options = elem.options,
                    index = elem.selectedIndex,
                    one = elem.type === 'select-one' || index < 0,
                    values = one ? null : [],
                    value;

            for (var i = 0, len = options.length; i < len; i++) {
                if ((options[ i ].selected || i === index) && !options[ i ].disabled) {
                    value = !options[ i ].value ? options[ i ].text : options[ i ].value;

                    if (one) {
                        return value;
                    }

                    values.push(value);
                }
            }

            return values;

        } else {
            return elem.value;
        }
    };

    ss.hasClass = function (elem, name) {
        "use strict";

        if (!elem || !name) {
            return false;
        }

        return (' ' + elem.className + ' ').replace(rHasClass, ' ').indexOf(' ' + name + ' ') >= 0;
    };

    ss.addClass = function (elem, name) {
        "use strict";

        if (!elem || !name) {
            return false;
        }

        if (!ss.hasClass(elem, name)) {
            elem.className += ' ' + name;
        }
    };

    ss.removeClass = (function () {
        "use strict";

        var c = {}; 

        return function (e, name) {
            if (!e || !name) {
                return false;
            }

            if (!c[name]) {
                c[name] = new RegExp('(?:^|\\s)' + name + '(?!\\S)');
            }

            e.className = e.className.replace(c[name], '');
        };
    })();

    ss.purge = function (d) {
        "use strict";

        var a = d.attributes, i, l, n;

        if (a) {
            for (i = a.length - 1; i >= 0; i -= 1) {
                n = a[i].name;

                if (typeof d[n] === 'function') {
                    d[n] = null;
                }
            }
        }

        a = d.childNodes;

        if (a) {
            l = a.length;
            for (i = 0; i < l; i += 1) {
                ss.purge(d.childNodes[i]);
            }
        }
    };

    ss.remove = function (elem) {
        "use strict";

        if (elem && elem.parentNode) {
            ss.purge(elem);
            elem.parentNode.removeChild(elem);
        }
        elem = null;
    };

    
    ss.verifyElem = function (elem) {
        "use strict";


        if (typeof jQuery !== 'undefined' && elem instanceof jQuery) {
            elem = elem[0];

        } else if (typeof elem === 'string') {
            if (elem.charAt(0) == '#') {
                elem = elem.substr(1);
            }
            elem = document.getElementById(elem);
        }

        if (!elem || elem.nodeType !== 1) {
            return false;
        }

        if (elem.nodeName.toUpperCase() == 'A') {
            elem.style.cursor = 'pointer';

            ss.addEvent(elem, 'click', function (e) {
                if (e && e.preventDefault) {
                    e.preventDefault();

                } else if (window.event) {
                    window.event.returnValue = false;
                }
            });
        }

        return elem;
    };

    ss._options = {};

    ss.uploadSetup = function (options) {
        "use strict";
        ss.extendObj(ss._options, options);
    };

    ss.SimpleUpload = function (options) {
        "use strict";

        this._opts = {
            button: '',
            url: '',
            dropzone: '',
            dragClass: '',
            form: '',
            overrideSubmit: true,
            cors: false,
            withCredentials: false,
            progressUrl: false,
            sessionProgressUrl: false,
            nginxProgressUrl: false,
            multiple: false,
            multipleSelect: false, 
            maxUploads: 3,
            queue: true,
            checkProgressInterval: 500,
            keyParamName: 'APC_UPLOAD_PROGRESS',
            sessionProgressName: 'PHP_SESSION_UPLOAD_PROGRESS',
            nginxProgressHeader: 'X-Progress-ID',
            customProgressHeaders: {},
            corsInputName: 'XHR_CORS_TARGETORIGIN',
            allowedExtensions: [],
            accept: '',
            maxSize: false,
            name: '',
            data: {},
            noParams: true,
            autoSubmit: true,
            multipart: true,
            method: 'POST',
            responseType: '',
            debug: false,
            hoverClass: '',
            focusClass: '',
            disabledClass: '',
            customHeaders: {},
            encodeHeaders: true,
            autoCalibrate: true,
            onBlankSubmit: function () {},
            onAbort: function (filename, uploadBtn, size) {},
            onChange: function (filename, extension, uploadBtn, size, file) {},
            onSubmit: function (filename, extension, uploadBtn, size) {},
            onProgress: function (pct) {},
            onUpdateFileSize: function (filesize) {},
            onComplete: function (filename, response, uploadBtn, size) {},
            onDone: function (filename, status, textStatus, response, uploadBtn, size) {},
            onAllDone: function () {},
            onExtError: function (filename, extension) {},
            onSizeError: function (filename, fileSize, uploadBtn) {},
            onError: function (filename, type, status, statusText, response, uploadBtn, size) {},
            startXHR: function (filename, fileSize, uploadBtn) {},
            endXHR: function (filename, fileSize, uploadBtn) {},
            startNonXHR: function (filename, uploadBtn) {},
            endNonXHR: function (filename, uploadBtn) {}
        };

        ss.extendObj(this._opts, ss._options);
        ss.extendObj(this._opts, options);

        
        this._queue = [];

        this._active = 0;
        this._disabled = false; 
        this._maxFails = 10; 
        this._progKeys = {};
        this._sizeFlags = {};
        this._btns = [];

        this.addButton(this._opts.button);

        delete this._opts.button;
        this._opts.button = options = null;

        if (this._opts.multiple === false) {
            this._opts.maxUploads = 1;
        }

        if (this._opts.dropzone !== '') {
            this.addDZ(this._opts.dropzone);
        }

        if (this._opts.dropzone === '' && this._btns.length < 1) {
            throw new Error("Invalid upload button. Make sure the element you're passing exists.");
        }

        if (this._opts.form !== '') {
            this.setForm(this._opts.form);
        }

        this._createInput();
        this._manDisabled = false;
        this.enable(true);
    };

    ss.SimpleUpload.prototype = {

        destroy: function () {
            "use strict";

            
            var i = this._btns.length;

            
            while (i--) {
            
                if (this._btns[i].off) {
                    this._btns[i].off();
                }

            
                ss.removeClass(this._btns[i], this._opts.hoverClass);
                ss.removeClass(this._btns[i], this._opts.focusClass);
                ss.removeClass(this._btns[i], this._opts.disabledClass);

            
                this._btns[i].disabled = false;
            }

            this._killInput();

            
            this._destroy = true;
        },

        log: function (str) {
            "use strict";

            if (this._opts && this._opts.debug && window.console && window.console.log) {
                window.console.log('[Uploader] ' + str);
            }
        },

        setData: function (data) {
            "use strict";
            this._opts.data = data;
        },

        setOptions: function (options) {
            "use strict";
            ss.extendObj(this._opts, options);
        },

        addButton: function (button) {
            var btn;

            
            if (button instanceof Array) {

                for (var i = 0, len = button.length; i < len; i++) {
                    btn = ss.verifyElem(button[i]);

                    if (btn !== false) {
                        this._btns.push(this.rerouteClicks(btn));

                    } else {
                        this.log('Button with array index ' + i + ' is invalid');
                    }
                }

                
            } else {
                btn = ss.verifyElem(button);

                if (btn !== false) {
                    this._btns.push(this.rerouteClicks(btn));
                }
            }
        },

        addDZ: function (dropzone) {
            if (!XhrOk) {
                return;
            }

            dropzone = ss.verifyElem(dropzone);

            if (!dropzone) {
                this.log('Invalid or nonexistent element passed for drop zone');
            } else {
                this.addDropZone(dropzone);
            }
        },

        setProgressBar: function (elem) {
            "use strict";
            this._progBar = ss.verifyElem(elem);
        },

        setPctBox: function (elem) {
            "use strict";
            this._pctBox = ss.verifyElem(elem);
        },

        setFileSizeBox: function (elem) {
            "use strict";
            this._sizeBox = ss.verifyElem(elem);
        },

        setProgressContainer: function (elem) {
            "use strict";
            this._progBox = ss.verifyElem(elem);
        },

        setAbortBtn: function (elem, remove) {
            "use strict";

            this._abortBtn = ss.verifyElem(elem);
            this._removeAbort = false;

            if (remove) {
                this._removeAbort = true;
            }
        },

        setForm: function (form) {
            "use strict";

            this._form = ss.verifyElem(form);

            if (!this._form || this._form.nodeName.toUpperCase() != 'FORM') {
                this.log('Invalid or nonexistent element passed for form');

            } else {
                var self = this;
                this._opts.autoSubmit = false;

                if (this._opts.overrideSubmit) {
                    ss.addEvent(this._form, 'submit', function (e) {
                        if (e.preventDefault) {
                            e.preventDefault();

                        } else if (window.event) {
                            window.event.returnValue = false;
                        }

                        if (self._validateForm()) {
                            self.submit();
                        }
                    });

                    this._form.submit = function () {
                        if (self._validateForm()) {
                            self.submit();
                        }
                    };
                }
            }
        },

        getQueueSize: function () {
            "use strict";
            return this._queue.length;
        },

        removeCurrent: function (id) {
            "use strict";

            if (id) {
                var i = this._queue.length;

                while (i--) {
                    if (this._queue[i].id === id) {
                        this._queue.splice(i, 1);
                        break;
                    }
                }

            } else {
                this._queue.splice(0, 1);
            }

            this._cycleQueue();
        },

        clearQueue: function () {
            "use strict";
            this._queue.length = 0;
        },

        disable: function (_self) {
            "use strict";

            var i = this._btns.length,
                    nodeName;

            
            this._manDisabled = !_self || this._manDisabled === true ? true : false;
            this._disabled = true;

            while (i--) {
                nodeName = this._btns[i].nodeName.toUpperCase();

                if (nodeName == 'INPUT' || nodeName == 'BUTTON') {
                    this._btns[i].disabled = true;
                }

                if (this._opts.disabledClass !== '') {
                    ss.addClass(this._btns[i], this._opts.disabledClass);
                }
            }

            
            if (this._input && this._input.parentNode) {
                this._input.parentNode.style.visibility = 'hidden';
            }
        },

        enable: function (_self) {
            "use strict";

            
            if (!_self) {
                this._manDisabled = false;
            }

            
            if (this._manDisabled === true) {
                return;
            }

            var i = this._btns.length;

            this._disabled = false;

            while (i--) {
                ss.removeClass(this._btns[i], this._opts.disabledClass);
                this._btns[i].disabled = false;
            }
        },

        updatePosition: function (btn) {
            "use strict";

            btn = !btn ? this._btns[0] : btn;

            if (btn && this._input && this._input.parentNode) {
                ss.copyLayout(btn, this._input.parentNode);
            }

            btn = null;
        },

        rerouteClicks: function (elem) {
            "use strict";

            var self = this,
                    detachOver,
                    detachClick;

            detachOver = ss.addEvent(elem, 'mouseover', function () {
                if (self._disabled) {
                    return;
                }

                if (!self._input) {
                    self._createInput();
                }

                self._overBtn = elem;
                ss.copyLayout(elem, self._input.parentNode);
                self._input.parentNode.style.visibility = 'visible';
            });

            
            detachClick = ss.addEvent(elem, 'click', function (e) {
                if (e && e.preventDefault) {
                    e.preventDefault();
                }

                if (self._disabled) {
                    return;
                }

                if (!self._input) {
                    self._createInput();
                }

                self._overBtn = elem;

                if (!isIE7to9) {
                    self._input.click();
                }
            });

            elem.off = function () {
                detachOver();
                detachClick();
            };

            if (self._opts.autoCalibrate && !ss.isVisible(elem)) {
                self.log('Upload button not visible');

                var interval = function () {
                    if (ss.isVisible(elem)) {
                        self.log('Upload button now visible');

                        window.setTimeout(function () {
                            self.updatePosition(elem);

                            if (self._btns.length === 1) {
                                self._input.parentNode.style.visibility = 'hidden';
                            }
                        }, 200);

                    } else {
                        window.setTimeout(interval, 500);
                    }
                };

                window.setTimeout(interval, 500);
            }

            return elem;
        },

        submit: function (_, auto) {
            "use strict";

            if (!auto && this._queue.length < 1) {
                this._opts.onBlankSubmit.call(this);
                return;
            }

            if (this._disabled ||
                    this._active >= this._opts.maxUploads ||
                    this._queue.length < 1)
            {
                return;
            }

            if (!this._checkFile(this._queue[0])) {
                return;
            }

            
            if (false === this._opts.onSubmit.call(this, this._queue[0].name, this._queue[0].ext, this._queue[0].btn, this._queue[0].size)) {
                this.removeCurrent(this._queue[0].id);
                return;
            }

            
            this._active++;

            
            if (this._opts.multiple === false ||
                    this._opts.queue === false && this._active >= this._opts.maxUploads)
            {
                this.disable(true);
            }

            this._initUpload(this._queue[0]);
        }

    };

    ss.IframeUpload = {

        _detachEvents: {},

        _detach: function (id) {
            if (this._detachEvents[ id ]) {
                this._detachEvents[ id ]();
                delete this._detachEvents[ id ];
            }
        },

        _getHost: function (uri) {
            var a = document.createElement('a');

            a.href = uri;

            if (a.hostname) {
                return a.hostname.toLowerCase();
            }
            return uri;
        },

        _addFiles: function (file) {
            var filename = ss.getFilename(file.value),
                    ext = ss.getExt(filename);

            if (false === this._opts.onChange.call(this, filename, ext, this._overBtn, undefined, file)) {
                return false;
            }

            this._queue.push({
                id: ss.getUID(),
                file: file,
                name: filename,
                ext: ext,
                btn: this._overBtn,
                size: null
            });

            return true;
        },

        _uploadIframe: function (fileObj, progBox, sizeBox, progBar, pctBox, abortBtn, removeAbort) {
            "use strict";

            var self = this,
                    opts = this._opts,
                    key = ss.getUID(),
                    iframe = ss.getIFrame(),
                    form,
                    url,
                    msgLoaded = false,
                    iframeLoaded = false,
                    cancel;

            if (opts.noParams === true) {
                url = opts.url;

            } else {
                url = !opts.nginxProgressUrl ?
                        opts.url :
                        url + ((url.indexOf('?') > -1) ? '&' : '?') +
                        encodeURIComponent(opts.nginxProgressHeader) + '=' + encodeURIComponent(key);
            }

            form = ss.getForm({
                action: url,
                target: iframe.name,
                method: opts.method
            });

            opts.onProgress.call(this, 0);

            if (pctBox) {
                pctBox.innerHTML = '0%';
            }

            if (progBar) {
                progBar.style.width = '0%';
            }

            
            if (opts.cors) {
                var msgId = ss.getUID();

                self._detachEvents[ msgId ] = ss.addEvent(window, 'message', function (event) {
            
                    if (self._getHost(event.origin) != self._getHost(opts.url)) {
                        self.log('Non-matching origin: ' + event.origin);
                        return;
                    }

                    msgLoaded = true;
                    self._detach(msgId);
                    opts.endNonXHR.call(self, fileObj.name, fileObj.btn);
                    self._finish(fileObj, '', '', event.data, sizeBox, progBox, pctBox, abortBtn, removeAbort);
                });
            }

            self._detachEvents[ iframe.id ] = ss.addEvent(iframe, 'load', function () {
                self._detach(iframe.id);

                if (opts.sessionProgressUrl) {
                    form.appendChild(ss.getHidden(opts.sessionProgressName, key));
                }

                
                else if (opts.progressUrl) {
                    form.appendChild(ss.getHidden(opts.keyParamName, key));
                }

                if (self._form) {
                    ss.extendObj(opts.data, ss.getFormObj(self._form));
                }

                
                for (var prop in opts.data) {
                    if (opts.data.hasOwnProperty(prop)) {
                        form.appendChild(ss.getHidden(prop, opts.data[prop]));
                    }
                }


                if (opts.cors) {
                    form.appendChild(ss.getHidden(opts.corsInputName, window.location.href));
                }

                form.appendChild(fileObj.file);

                self._detachEvents[ fileObj.id ] = ss.addEvent(iframe, 'load', function () {
                    if (!iframe || !iframe.parentNode || iframeLoaded) {
                        return;
                    }

                    self._detach(fileObj.id);
                    iframeLoaded = true;

                    delete self._progKeys[ key ];
                    delete self._sizeFlags[ key ];

                    if (abortBtn) {
                        ss.removeEvent(abortBtn, 'click', cancel);
                    }


                    if (opts.cors) {
                        window.setTimeout(function () {
                            ss.remove(iframe);

                            if (!msgLoaded) {
                                self._errorFinish(fileObj, '', '', false, 'error', progBox, sizeBox, pctBox, abortBtn, removeAbort);
                            }

                            opts = key = iframe = sizeBox = progBox = pctBox = abortBtn = removeAbort = null;
                        }, 600);
                    }
                    else {
                        try {
                            var doc = iframe.contentDocument ? iframe.contentDocument : iframe.contentWindow.document,
                                    response = doc.body.innerHTML;

                            ss.remove(iframe);
                            iframe = null;

                            opts.endNonXHR.call(self, fileObj.name, fileObj.btn);

                            
                            self._finish(fileObj, '', '', response, sizeBox, progBox, pctBox, abortBtn, removeAbort);

                        } catch (e) {
                            self._errorFinish(fileObj, '', e.message, false, 'error', progBox, sizeBox, pctBox, abortBtn, removeAbort);
                        }

                        opts = key = sizeBox = progBox = pctBox = null;
                    }
                });

                if (abortBtn) {
                    cancel = function () {
                        ss.removeEvent(abortBtn, 'click', cancel);

                        delete self._progKeys[key];
                        delete self._sizeFlags[key];

                        if (iframe) {
                            iframeLoaded = true;
                            self._detach(fileObj.id);

                            try {
                                if (iframe.contentWindow.document.execCommand) {
                                    iframe.contentWindow.document.execCommand('Stop');
                                }
                            } catch (err) {
                            }

                            try {
                                iframe.src = 'javascript'.concat(':false;');
                            } catch (err) {
                            }

                            window.setTimeout(function () {
                                ss.remove(iframe);
                                iframe = null;
                            }, 1);
                        }

                        self.log('Upload aborted');
                        opts.onAbort.call(self, fileObj.name, fileObj.btn, fileObj.size);
                        self._last(sizeBox, progBox, pctBox, abortBtn, removeAbort, fileObj, 'abort');
                    };

                    ss.addEvent(abortBtn, 'click', cancel);
                }

                self.log('Commencing upload using iframe');
                form.submit();


                window.setTimeout(function () {
                    ss.remove(form);
                    form = null;
                    self.removeCurrent(fileObj.id);
                }, 1);

                if (self._hasProgUrl) {

                    self._progKeys[key] = 1;

                    window.setTimeout(function () {
                        self._getProg(key, progBar, sizeBox, pctBox, 1);
                        progBar = sizeBox = pctBox = null;
                    }, 600);
                }

            });

            document.body.appendChild(form);
            document.body.appendChild(iframe);
        },

        _getProg: function (key, progBar, sizeBox, pctBox, counter) {
            "use strict";

            var self = this,
                    opts = this._opts,
                    time = new Date().getTime(),
                    xhr,
                    url,
                    callback;

            if (!key || !opts) {
                return;
            }

            
            if (opts.nginxProgressUrl) {
                url = opts.nginxProgressUrl + '?' +
                        encodeURIComponent(opts.nginxProgressHeader) + '=' + encodeURIComponent(key) +
                        '&_=' + time;
            } else if (opts.sessionProgressUrl) {
                url = opts.sessionProgressUrl;
            }

            
            else if (opts.progressUrl) {
                url = opts.progressUrl +
                        '?progresskey=' + encodeURIComponent(key) +
                        '&_=' + time;
            }

            callback = function () {
                var response,
                        size,
                        pct,
                        status,
                        statusText;

                try {
                    
                    if (callback && (opts.cors || xhr.readyState === 4)) {
                        callback = undefined;
                        xhr.onreadystatechange = function () {};

                        try {
                            statusText = xhr.statusText;
                            status = xhr.status;
                        } catch (e) {
                            statusText = '';
                            status = '';
                        }

                        
                        if (opts.cors || (status >= 200 && status < 300)) {
                            response = ss.parseJSON(xhr.responseText);

                            if (response === false) {
                                self.log('Error parsing progress response (expecting JSON)');
                                return;
                            }

                            
                            if (opts.nginxProgressUrl) {

                                if (response.state == 'uploading') {
                                    size = parseInt(response.size, 10);
                                    if (size > 0) {
                                        pct = Math.round((parseInt(response.received, 10) / size) * 100);
                                        size = Math.round(size / 1024);
                                    }

                                } else if (response.state == 'done') {
                                    pct = 100;

                                } else if (response.state == 'error') {
                                    self.log('Error requesting upload progress: ' + response.status);
                                    return;
                                }
                            }

                            
                            else if (opts.sessionProgressUrl || opts.progressUrl) {
                                if (response.success === true) {
                                    size = parseInt(response.size, 10);
                                    pct = parseInt(response.pct, 10);
                                }
                            }

                            
                            if (pct) {
                                if (pctBox) {
                                    pctBox.innerHTML = pct + '%';
                                }

                                if (progBar) {
                                    progBar.style.width = pct + '%';
                                }

                                opts.onProgress.call(self, pct);
                            }

                            if (size && !self._sizeFlags[key]) {
                                if (sizeBox) {
                                    sizeBox.innerHTML = size + 'K';
                                }

                                self._sizeFlags[key] = 1;
                                opts.onUpdateFileSize.call(self, size);
                            }

                            
                            if (!pct &&
                                    !size &&
                                    counter >= self._maxFails)
                            {
                                counter++;
                                self.log('Failed progress request limit reached. Count: ' + counter);
                                return;
                            }

                            
                            if (pct < 100 && self._progKeys[key]) {
                                window.setTimeout(function () {
                                    self._getProg(key, progBar, sizeBox, pctBox, counter);

                                    key = progBar = sizeBox = pctBox = counter = null;
                                }, opts.checkProgressInterval);
                            }

                            
                        } else {
                            delete self._progKeys[key];
                            self.log('Error requesting upload progress: ' + status + ' ' + statusText);
                        }

                        xhr = size = pct = status = statusText = response = null;
                    }

                } catch (e) {
                    self.log('Error requesting upload progress: ' + e.message);
                }
            };

            
            if (opts.cors && !opts.sessionProgressUrl) {

                if (window.XDomainRequest) {
                    xhr = new window.XDomainRequest();
                    xhr.open('GET', url, true);
                    xhr.onprogress = xhr.ontimeout = function () {};
                    xhr.onload = callback;

                    xhr.onerror = function () {
                        delete self._progKeys[key];
                        key = null;
                        self.log('Error requesting upload progress');
                    };

                } else {
                    return;
                }

            } else {
                var method = !opts.sessionProgressUrl ? 'GET' : 'POST',
                        headers = {},
                        params;

                xhr = ss.newXHR();
                xhr.onreadystatechange = callback;
                xhr.open(method, url, true);

                
                if (opts.sessionProgressUrl) {
                    params = encodeURIComponent(opts.sessionProgressName) + '=' + encodeURIComponent(key);
                    headers['Content-Type'] = 'application/x-www-form-urlencoded';
                }


                if (opts.nginxProgressUrl) {
                    headers[opts.nginxProgressHeader] = key;
                }

                headers['X-Requested-With'] = 'XMLHttpRequest';
                headers['Accept'] = 'application/json, text/javascript, */*; q=0.01';

                ss.extendObj(headers, opts.customProgressHeaders);

                for (var i in headers) {
                    if (headers.hasOwnProperty(i)) {
                        if (opts.encodeHeaders) {
                            xhr.setRequestHeader(i, ss.encodeUTF8(headers[ i ] + ''));

                        } else {
                            xhr.setRequestHeader(i, headers[ i ] + '');
                        }
                    }
                }

                xhr.send((opts.sessionProgressUrl && params) || null);
            }
        },

        _initUpload: function (fileObj) {
            if (false === this._opts.startNonXHR.call(this, fileObj.name, fileObj.btn)) {

                if (this._disabled) {
                    this.enable(true);
                }

                this._active--;
                return;
            }

            this._hasProgUrl = (this._opts.progressUrl ||
                    this._opts.sessionProgressUrl ||
                    this._opts.nginxProgressUrl) ?
                    true : false;

            this._uploadIframe(fileObj, this._progBox, this._sizeBox, this._progBar, this._pctBox, this._abortBtn, this._removeAbort);

            fileObj = this._progBox = this._sizeBox = this._progBar = this._pctBox = this._abortBtn = this._removeAbort = null;
        }
    };

    ss.XhrUpload = {

        _addFiles: function (files) {
            var total = files.length,
                    filename,
                    ext,
                    size,
                    i;

            if (!this._opts.multiple) {
                total = 1;
            }

            for (i = 0; i < total; i++) {
                filename = ss.getFilename(files[i].name);
                ext = ss.getExt(filename);
                size = Math.round(files[i].size / 1024);

                if (false === this._opts.onChange.call(this, filename, ext, this._overBtn, size, files[i])) {
                    return false;
                }

                this._queue.push({
                    id: ss.getUID(),
                    file: files[i],
                    name: filename,
                    ext: ext,
                    btn: this._overBtn,
                    size: size
                });
            }

            return true;
        },

        _uploadXhr: function (fileObj, url, params, headers, sizeBox, progBar, progBox, pctBox, abortBtn, removeAbort) {
            "use strict";

            var self = this,
                    opts = this._opts,
                    xhr = ss.newXHR(),
                    callback,
                    cancel;

            
            if (sizeBox) {
                sizeBox.innerHTML = fileObj.size + 'K';
            }

            
            if (pctBox) {
                pctBox.innerHTML = '0%';
            }

            if (progBar) {
                progBar.style.width = '0%';
            }

            
            callback = function (_, isAbort) {
                var statusText;

                try {
                    
                    if (callback && (isAbort || xhr.readyState === 4)) {
                        callback = undefined;
                        xhr.onreadystatechange = function () {};

                    
                        if (isAbort) {
                    
                            if (xhr.readyState !== 4) {
                                xhr.abort();
                            }

                            opts.onAbort.call(self, fileObj.name, fileObj.btn, fileObj.size);
                            self._last(sizeBox, progBox, pctBox, abortBtn, removeAbort, fileObj, 'abort');

                        } else {
                            if (abortBtn) {
                                ss.removeEvent(abortBtn, 'click', cancel);
                            }

                            try {
                                statusText = xhr.statusText;
                            } catch (e) {
                              
                                statusText = '';
                            }

                            if (xhr.status >= 200 && xhr.status < 300) {
                                opts.endXHR.call(self, fileObj.name, fileObj.size, fileObj.btn);
                                self._finish(fileObj, xhr.status, statusText, xhr.responseText, sizeBox, progBox, pctBox, abortBtn, removeAbort);

                                
                            } else {
                                self._errorFinish(fileObj, xhr.status, statusText, xhr.responseText, 'error', progBox, sizeBox, pctBox, abortBtn, removeAbort);
                            }
                        }
                    }

                } catch (e) {
                    if (!isAbort) {
                        self._errorFinish(fileObj, -1, e.message, false, 'error', progBox, sizeBox, pctBox, abortBtn, removeAbort);
                    }
                }
            };

            if (abortBtn) {
                cancel = function () {
                    ss.removeEvent(abortBtn, 'click', cancel);

                    if (callback) {
                        callback(undefined, true);
                    }
                };

                ss.addEvent(abortBtn, 'click', cancel);
            }

            xhr.onreadystatechange = callback;
            xhr.open(opts.method.toUpperCase(), url, true);
            xhr.withCredentials = !!opts.withCredentials;

            ss.extendObj(headers, opts.customHeaders);

            for (var i in headers) {
                if (headers.hasOwnProperty(i)) {
                    if (opts.encodeHeaders) {
                        xhr.setRequestHeader(i, ss.encodeUTF8(headers[ i ] + ''));

                    } else {
                        xhr.setRequestHeader(i, headers[ i ] + '');
                    }
                }
            }

            ss.addEvent(xhr.upload, 'progress', function (event) {
                if (event.lengthComputable) {
                    var pct = Math.round(event.loaded / event.total * 100);

                    opts.onProgress.call(self, pct);

                    if (pctBox) {
                        pctBox.innerHTML = pct + '%';
                    }

                    if (progBar) {
                        progBar.style.width = pct + '%';
                    }
                }
            });

            opts.onProgress.call(this, 0);

            if (opts.multipart === true) {
                var formData = new FormData();

                var hasFile = false;

                for (var prop in params) {
                    if (params.hasOwnProperty(prop)) {
                        if (prop === opts.name && opts.noParams === true && !self._form) {
                            hasFile = true;
                        }
                        formData.append(prop, params[prop]);
                    }
                }

                if (!hasFile) {
                    formData.append(opts.name, fileObj.file);
                }

                this.log('Commencing upload using multipart form');
                xhr.send(formData);

            } else {
                this.log('Commencing upload using binary stream');
                xhr.send(fileObj.file);
            }

            
            this.removeCurrent(fileObj.id);
        },

        _initUpload: function (fileObj) {
            "use strict";
            

            var params = {},
                    headers = {},
                    url;

            
            if (false === this._opts.startXHR.call(this, fileObj.name, fileObj.size, fileObj.btn)) {

                if (this._disabled) {
                    this.enable(true);
                }

                this._active--;
                return;
            }

            headers['X-Requested-With'] = 'XMLHttpRequest';
            headers['X-File-Name'] = ss.encodeUTF8(fileObj.name);

            if (this._opts.responseType.toLowerCase() == 'json') {
                headers['Accept'] = 'application/json, text/javascript, */*; q=0.01';
            }

            if (!this._opts.multipart) {
                headers['Content-Type'] = 'application/octet-stream';
            }

            if (this._form) {
                ss.extendObj(params, ss.getFormObj(this._form));
            }

            
            ss.extendObj(params, this._opts.data);

            
            url = this._opts.noParams === true ?
                    this._opts.url :
                    this._opts.url + ((this._opts.url.indexOf('?') > -1) ? '&' : '?') + ss.obj2string(params);

            this._uploadXhr(fileObj, url, params, headers, this._sizeBox, this._progBar, this._progBox, this._pctBox, this._abortBtn, this._removeAbort);

            this._sizeBox = this._progBar = this._progBox = this._pctBox = this._abortBtn = this._removeAbort = null;
        }

    };

    ss.DragAndDrop = {

        _dragFileCheck: function (e) {
            if (e.dataTransfer.types) {
                for (var i = 0; i < e.dataTransfer.types.length; i++) {
                    if (e.dataTransfer.types[i] == 'Files') {
                        return true;
                    }
                }
            }

            return false;
        },

        addDropZone: function (elem) {
            var self = this,
                    collection = [];

            ss.addStyles(elem, {
                'zIndex': 16777271
            });

            elem.ondragenter = function (e) {
                e.stopPropagation();
                e.preventDefault();

                if (!self._dragFileCheck(e)) {
                    return false;
                }

                if (collection.length === 0) {
                    ss.addClass(this, self._opts.dragClass);
                }

                if (ss.indexOf(collection, e.target) === -1) {
                    collection.push(e.target);
                }

                return false;
            };

            elem.ondragover = function (e) {
                e.stopPropagation();
                e.preventDefault();

                if (self._dragFileCheck(e)) {
                    e.dataTransfer.dropEffect = 'copy';
                }

                return false;
            };

            elem.ondragend = function () {
                ss.removeClass(this, self._opts.dragClass);
                return false;
            };

            elem.ondragleave = function (e) {
                ss.arrayDelete(collection, e.target);

                if (collection.length === 0) {
                    ss.removeClass(this, self._opts.dragClass);
                }

                return false;
            };

            elem.ondrop = function (e) {
                e.stopPropagation();
                e.preventDefault();

                ss.arrayDelete(collection, e.target);

                if (collection.length === 0) {
                    ss.removeClass(this, self._opts.dragClass);
                }

                if (!self._dragFileCheck(e)) {
                    return;
                }

                if (false !== self._addFiles(e.dataTransfer.files)) {
                    self._cycleQueue();
                }
            };
        }
    };

    ss.extendObj(ss.SimpleUpload.prototype, {

        _createInput: function () {
            "use strict";

            var self = this,
                    div = document.createElement('div');

            this._input = document.createElement('input');
            this._input.type = 'file';
            this._input.name = this._opts.name;

            
            if (XhrOk && !isSafari && this._opts.multipleSelect) {
                this._input.multiple = true;
            }

            
            if ('accept' in this._input && this._opts.accept !== '') {
                this._input.accept = this._opts.accept;
            }

            ss.addStyles(div, {
                'display': 'none',
                'position': 'relative',
                'visibility': 'hidden',
                'overflow': 'hidden',
                'margin': 0,
                'padding': 0,
                'opacity': 0,
                'direction': 'ltr',
                'zIndex': 16777270
            });

            if (div.style.opacity !== '0') {
                div.style.filter = 'alpha(opacity=0)';
            }

            ss.addStyles(this._input, {
                'position': 'absolute',
                'right': 0,
                'margin': 0,
                'padding': 0,
                'fontSize': '480px',
                'fontFamily': 'sans-serif',
                'cursor': 'pointer',
                'height': '100%',
                'zIndex': 16777270
            });

            this._input.turnOff = ss.addEvent(this._input, 'change', function () {
                if (!self._input || self._input.value === '') {
                    return;
                }

                if (false === self._addFiles(XhrOk ? self._input.files : self._input)) {
                    return;
                }

                ss.removeClass(self._overBtn, self._opts.hoverClass);
                ss.removeClass(self._overBtn, self._opts.focusClass);

                self._killInput();

                
                self._createInput();

                
                if (self._opts.autoSubmit) {
                    self.submit();
                }
            });

            if (self._opts.hoverClass !== '') {
                div.mouseOverOff = ss.addEvent(div, 'mouseover', function () {
                    ss.addClass(self._overBtn, self._opts.hoverClass);
                });
            }

            div.mouseOutOff = ss.addEvent(div, 'mouseout', function () {
                self._input.parentNode.style.visibility = 'hidden';

                if (self._opts.hoverClass !== '') {
                    ss.removeClass(self._overBtn, self._opts.hoverClass);
                    ss.removeClass(self._overBtn, self._opts.focusClass);
                }
            });

            if (self._opts.focusClass !== '') {
                this._input.focusOff = ss.addEvent(this._input, 'focus', function () {
                    ss.addClass(self._overBtn, self._opts.focusClass);
                });

                this._input.blurOff = ss.addEvent(this._input, 'blur', function () {
                    ss.removeClass(self._overBtn, self._opts.focusClass);
                });
            }

            div.appendChild(this._input);
            document.body.appendChild(div);
            div = null;
        },

        _last: function (sizeBox, progBox, pctBox, abortBtn, removeAbort, fileObj, textStatus, status, response) {
            "use strict";

            if (sizeBox) {
                sizeBox.innerHTML = '';
            }

            if (pctBox) {
                pctBox.innerHTML = '';
            }

            if (abortBtn && removeAbort) {
                ss.remove(abortBtn);
            }

            if (progBox) {
                ss.remove(progBox);
            }

            
            this._active--;

            sizeBox = progBox = pctBox = abortBtn = removeAbort = null;

            if (this._disabled) {
                this.enable(true);
            }

            
            if (this._destroy &&
                    this._queue.length === 0 &&
                    this._active === 0)
            {
                for (var prop in this) {
                    if (this.hasOwnProperty(prop)) {
                        delete this[ prop ];
                    }
                }

            } else {
                this._opts.onDone.call(this, fileObj.name, status, textStatus, response, fileObj.btn, fileObj.size);

                this._cycleQueue();

                if (this._queue.length === 0 && this._active === 0) {
                    this._opts.onAllDone.call(this);
                }
            }

            fileObj = textStatus = status = response = null;
        },

        _errorFinish: function (fileObj, status, statusText, response, errorType, progBox, sizeBox, pctBox, abortBtn, removeAbort) {
            "use strict";

            this.log('Upload failed: ' + status + ' ' + statusText);
            this._opts.onError.call(this, fileObj.name, errorType, status, statusText, response, fileObj.btn, fileObj.size);
            this._last(sizeBox, progBox, pctBox, abortBtn, removeAbort, fileObj, 'error', status, response);

            statusText = errorType = sizeBox = progBox = pctBox = abortBtn = removeAbort = null;
        },

        _finish: function (fileObj, status, statusText, response, sizeBox, progBox, pctBox, abortBtn, removeAbort) {
            "use strict";

            this.log('Server response: ' + response);

            if (this._opts.responseType.toLowerCase() == 'json') {
                response = ss.parseJSON(response);

                if (response === false) {
                    this._errorFinish(fileObj, status, statusText, false, 'parseerror', progBox, sizeBox, abortBtn, removeAbort);
                    return;
                }
            }

            this._opts.onComplete.call(this, fileObj.name, response, fileObj.btn, fileObj.size);
            this._last(sizeBox, progBox, pctBox, abortBtn, removeAbort, fileObj, 'success', status, response);
            statusText = sizeBox = progBox = pctBox = abortBtn = removeAbort = null;
        },

        _checkFile: function (fileObj) {
            "use strict";

            var extOk = false,
                    i = this._opts.allowedExtensions.length;

            
            if (i > 0) {
                while (i--) {
                    if (this._opts.allowedExtensions[i].toLowerCase() == fileObj.ext.toLowerCase()) {
                        extOk = true;
                        break;
                    }
                }

                if (!extOk) {
                    this.removeCurrent(fileObj.id);
                    this.log('File extension not permitted');
                    this._opts.onExtError.call(this, fileObj.name, fileObj.ext);
                    return false;
                }
            }

            if (fileObj.size &&
                    this._opts.maxSize !== false &&
                    fileObj.size > this._opts.maxSize)
            {
                this.removeCurrent(fileObj.id);
                this.log(fileObj.name + ' exceeds ' + this._opts.maxSize + 'K limit');
                this._opts.onSizeError.call(this, fileObj.name, fileObj.size, fileObj.btn);
                return false;
            }

            fileObj = null;

            return true;
        },

        _killInput: function () {
            "use strict";

            if (!this._input) {
                return;
            }

            if (this._input.turnOff) {
                this._input.turnOff();
            }

            if (this._input.focusOff) {
                this._input.focusOff();
            }

            if (this._input.blurOff) {
                this._input.blurOff();
            }

            if (this._input.parentNode.mouseOverOff) {
                this._input.parentNode.mouseOverOff();
            }

            ss.remove(this._input.parentNode);
            delete this._input;
            this._input = null;
        },

        _cycleQueue: function () {
            "use strict";

            if (this._queue.length > 0 && this._opts.autoSubmit) {
                this.submit(undefined, true);
            }
        },

        _validateForm: function () {
            "use strict";

            if (this._form.checkValidity && !this._form.checkValidity()) {
                return false;

            } else {
                return true;
            }
        }

    });

    if (XhrOk) {
        ss.extendObj(ss.SimpleUpload.prototype, ss.XhrUpload);

    } else {
        ss.extendObj(ss.SimpleUpload.prototype, ss.IframeUpload);
    }

    ss.extendObj(ss.SimpleUpload.prototype, ss.DragAndDrop);

    return ss;

}));

