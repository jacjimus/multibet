/* NCMS | App Script */

$(function(){
	
	//resize
	$(window).resize(_screenSize);
	$(window).trigger('resize');
	
	//scroll-to
	$(document).on('click', '.scroll-to, [scroll-to]', function(e){
		$('.navbar-collapse').collapse('hide');
		let target = $(this).attr('scroll-to') || this.hash;
		let parent = $(this).attr('scroll-parent');
		if (
			'string' === typeof target
			&& (target = target.trim()).length
			&& !['#', '#!'].includes(target)
			&& !target.match(/javascript:/)
		) _scrollTop(target, parent, -62);
	});
	
	//preload images
	_preloadImages(...[...document.querySelectorAll('img')]);
	
	//input controls - trigger change
	setTimeout(() => $('input, textarea').each((i, el) => $(el).trigger('change')));
});

//cookie get
function _cookie_get(key){
	return Cookies.get(key);
}

//cookie set
function _cookie_set(key, val, expires){
	return Cookies.set(key, val, {expires});
}

//cookie delete
function _cookie_delete(key){
	return Cookies.remove(key);
}

//uid
function _uid(){
	return Math.random().toString(36).substring(2) + (new Date()).getTime().toString(36);
}

//reload
function _reload(){
	window.location.reload();
}

//go to url (changes address bar url & title)
function _goto(url, title, _rdr){
	if (_rdr){
		let tmp, params = _queryParams();
		if (_hasKey(params, 'rdr') && (tmp = _tstr(atob(params.rdr)))){
			url = tmp;
		}
	}
	document.title = title || document.title;
	window.history.replaceState({}, null, url);
	setTimeout(() => window.location.href = url);
}

//open url
function _open_url(href, target, download){
	_create_link(href, target, download).clickRemove();
}

//create link
function _create_link(href, target, download){
	let a = document.createElement('a');
	a.href = (href = _tstr(href));
	if (target) a.target = target;
	if (download){
		let filename = _isString(download, 1) ? download : href.substr(href.lastIndexOf('/') + 1);
		filename = filename.trim() || 'download';
		a.download = filename;
	}
	document.body.appendChild(a);
	a.clickRemove = function(){
		this.click();
		this.remove();
	};
	return a;
}

//regex escape
function _regEsc(value){
	return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

//is undefined
function _isUndefined(val, _include_null=false){
	return val === undefined || val === null && _include_null;
}

//is null
function _isNull(val, _include_undefined=false){
	return val === null || val === undefined && _include_undefined;
}

//is boolean
function _isBool(value){
	return 'boolean' === typeof value ? {value} : false;
}

//is string
function _isString(value, filled=false, filled_trim=true){
	return 'string' === typeof value && (filled ? (filled_trim ? value.trim() : value).length : 1) ? {value} : false;
}

//trim string
function _trim(value, omit, direction){
	let reg = (omit = String(omit)).length ? _regEsc(omit) : '';
	if (direction = String(direction).toLowerCase()){
		if (direction == 'left') reg = '^\s*' + reg;
		else if (direction == 'right') reg = reg + '\s*$';
	}
	else reg = '^\s*' + reg + '|' + reg + '\s*$';
	return String(value).replaceAll(new RegExp(reg), '');
}

//to string
function _str(value, trim_string=false, strict=false, stringify=false){
	if (strict) return 'string' === typeof value ? (trim_string ? value.trim() : value) : '';
	if (_isUndefined(value, 1)) return '';
	if (_isBool(value)) return value ? '1' : '';
	if (_isObject(value, 1)) return stringify ? _jsonStringify(value) : '';
	value = String(value);
	return trim_string ? value.trim() : value;
}

//str split
function _str_split(str, glue=' ', _unique=false, _join=false, _trim=true){
	let val = _str(str, _trim).split(glue);
	if (_trim) val = val.map(o => o.trim()).filter(o => o != '');
	if (_unique) val = Array.from(new Set(val));
	if (_join !== false && !_isNull(_join, 1)) val = val.join('string' === typeof _join ? _join : glue);
	return val;
}

//to string trim
function _tstr(value, strict=false, stringify=false){
	return _str(value, true, strict, stringify);
}

//lowercase
function _lower(value, trim_string=false){
	return _str(value, trim_string).toLowerCase();
}

//uppercase
function _upper(value, trim_string=false){
	return _str(value, trim_string).toUpperCase();
}

//slug case
function _slug(val){
	val = _lower(val, 1);
	let replace_accents = [
		'àáäâèéëêìíïîòóöôùúüûñç·/_,:;',
		'aaaaeeeeiiiioooouuuunc------'
	];
	for (let i = 0; i < replace_accents[0].length; i ++){
		val = val.replace(new RegExp(replace_accents[0][i], 'g'), replace_accents[1][i]);
	}
	val.replace(/[^a-z0-9 -]/g, '')
	.replace(/\s+/g, '-')
	.replace(/-+/g, '-');
	return val
}

//snake case
function _snake(val){
	return _lower(val, 1)
	.replace(/\W+/g, ' ')
	.split(/ |\B(?=[A-Z])/)
	.join('_');
}

//studly case
function _studly(val){
	return _snake(val)
	.split('_')
	.map(w => w[0].toUpperCase() + w.substr(1).toLowerCase())
	.join('');
}

//title case
function _title(val, trim_string=false){
	return _str(val, trim_string)
	.replace(/\w\S*/g, match => match[0].toUpperCase() + match.substr(1).toLowerCase());
}

//sentence case
function _sentence(val, trim_string=false){
	return _str(val, trim_string)
	.replace(/(^\w{1}|\.\s*\w{1})/gi, match => match.toUpperCase());
}

//is function
function _isFunc(value){
	return 'function' === typeof value ? {value} : false;
}

//is object
function _isObject(value, include_array=false, include_null=false){
	return 'object' === typeof value && (value ? (Array.isArray(value) ? include_array : 1) : include_null) ? {value} : false;
}

//is keys object
function _isKeysObject(value, include_empty=true){
	return _isObject(value) && value.constructor.name == 'Object' && (include_empty ? 1 : Object.keys(value).length) ? {value} : false;
}

//is array
function _isArray(value, include_empty=true){
	return Array.isArray(value) && (include_empty ? 1 : value.length) ? {value} : false;
}

//parse number
function _parseNumber(value, _default=0){
	let num = parseFloat(String(value).replace(/,/g, ''));
	return !isNaN(num) ? num : _default;
}

//is numeric
function _isNumeric(value, strict=true){
	let num = 'number' === typeof value && !isNaN(value) ? value : (strict ? undefined : _parseNumber(value, undefined));
	return !isNaN(num) ? {value: num} : false;
}

//to number
function _num(value, _default=0, strict=false){
	let res = _isNumeric(value, strict);
	return res ? res.value : _default;
}

//abs number
function _anum(value, _default=0, strict=false){
	return Math.abs(_num(value, _default, strict));
}

//numbers - float
window._isFloat = _isNumeric;
window._float = _num;
window._afloat = _anum;

//numbers - int
function _isInt(value, strict=true){
	let res = _isNumeric(value, strict);
	return res && Number.isInteger(res.value) ? res : false;
}
function _int(value, _default=0, strict=false){
	let res = _isNumeric(value, strict);
	return res ? parseInt(res.value) : _default;
}
function _aint(value, _default=0, strict=false){
	return Math.abs(_int(value, _default, strict));
}

//round
function _round(value, places=2){
	+_num(value, 0, 0).toFixed(_aint(places, 2));
}

//commas
function _commas(value, places=2){
	return _num(value, 0, 0).toFixed(_aint(places, 2)).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

//is alphanumeric
function _isAlphan(value){
	return ['string', 'number'].includes(typeof value) ? {value} : false;
}

//is class
function _isClass(value, name=undefined){
	return _isObject(value) && !['Function', 'Object', 'Array'].includes(value.constructor.name) && (name ? name == value.constructor.name : 1) ? {value} : false;
}

//is date
function _isDate(value){
	return value instanceof Date && !isNaN(value.getTime()) ? {value} : false;
}

//is file
function _isFile(value){
	return value instanceof File;
}

//is element
function _isElement(value){
	try {
		return value instanceof HTMLElement ? {value} : false;
	}
	catch (e){
		return _hasKeys(value, 'nodeType', 'style', 'ownerDocument')
			&& value.nodeType === 1
			&& _isObject(value.style)
			&& _isObject(value.ownerDocument) ? {value} : false;
	}
}

//get element
function _getElement(value, index=0){
	let el = value;
	if (_isFunc(window.$) && (el = $(el)).length) el = (index = _aint(index, 0, 1)) >= 0 && index < el.length ? el[index] : el[0];
	return _isElement(el) ? el : null;
}

//is node element
function _isNode(value){
	return (value = _getElement(value)) && value.nodeType === Node.ELEMENT_NODE ? {value} : false;
}

//is form element
function _isForm(value){
	return (value = _getElement(value)) && value instanceof HTMLFormElement ? {value} : false;
}

//is child element
function _isChild(parent, child){
	child = _getElement(child);
	parent = _getElement(parent);
	if (parent && child){
		let node = child.parentNode;
		while (node != null){
			if (node == parent) return {parent, child};
			node = node.parentNode;
		}
	}
	return false;
}

//is empty
function _empty(value, trim_string=true){
	if (value === null || value === undefined) return true;
	if (_isString(value)) return !((trim_string ? value.trim() : value).length);
	if (_isNumeric(value)) return false;
	if (_isObject(value, 1)) return !Object.values(value).length;
	return false;
}

//isset
function _isset(value, trim_string=true){
	return !_empty(value, trim_string);
}

//json stringify
function _jsonStringify(value){
	let seen = [];
	return JSON.stringify(value, function(key, val){
		if (_isObject(val, 1)){
			if (seen.indexOf(val) >= 0) return;
			seen.push(val);
		}
		return val;
	});
}

//json parse
function _jsonParse(value, _default=undefined, debug=false){
	try {
		return JSON.parse(value);
	}
	catch (error){
		if (debug) console.error('Error _jsonParse: ', {error, value});
		return _default;
	}
}

//like json
function _likeJson(value){
	return _tstr(value).match(/null|true|false|^[\d\.]+$|^".*"$|^\[.*\]$|^\{.*\}$/ig) ? {value} : false;
}

//clone
function _clone(value){
	return _isObject(value, 1) ? _jsonParse(_jsonStringify(value)) : value;
}

//to array
function _arr(value, strict=false, filter_empty=false, trim_string=true){
	let arr = [];
	if (!_empty(value, trim_string)){
		arr = _isArray(value) ? value : (strict ? [] : 'object' === typeof value ? Object.values(value) : [value]);
	}
	if (arr.length) arr = Array.from(filter_empty ? arr.filter(o => !_empty(o)) : arr);
	return arr;
}

//object keys
function _keys(value){
	return _isObject(value, 1) ? Object.keys(value) : [];
}

//has key
function _hasKey(value, key){
	return _isObject(value, 1) && key in value;
}

//has keys found
function _hasKeysFound(value, ...keys){
	let found = [];
	for (let i = 0; i < keys.length; i ++){
		if (_hasKey(value, keys[i])) found.push(keys[i]);
	}
	return found;
}

//has keys
function _hasKeys(value, ...keys){
	return _isArray(keys, 0) && keys.length == _hasKeysFound(value, ...keys).length;
}

//object from keys, values map
function _keysMap(keys, values){
	let res = {};
	if (_isArray(keys, 0)){
		res = keys.reduce((obj, key, index) => {
			obj[key] = _hasKey(values, key) ? values[key] : undefined;
			return obj;
		}, {});
	}
	return res;
}

//merge items
function _merge(...items){
	let buffer = null;
	items.forEach(item => {
		if (_isObject(item)){
			if (!buffer) buffer = {};
			for (let key in item){
				let tmp, val = item[key];
				if (_isObject(val, 1) && _hasKey(buffer, key) && _isObject(tmp = buffer[key], 1)){
					val = _merge(tmp, val); //recurse
				}
				buffer[key] = val;
			}
		}
		else if (_isArray(item)){
			if (!buffer) buffer = [];
			if (_isArray(buffer)) buffer = [...buffer, ...item];
			else if (_isObject(buffer)) buffer = {...buffer, ...item};
			else buffer = item;
		}
		else {
			if (_isArray(buffer)) buffer = [...buffer, ...[item]];
			else if (_isObject(buffer)) buffer = {...buffer, ...[item]};
			else buffer = item;
		}
	});
	return _isObject(buffer, 1) ? _clone(buffer) : buffer;
}

//scroll top
function _scrollTop(target, parent, offset=0, speed=1000, easing='easeInOutExpo'){
	if (!((target = $(target)).length && (parent = (parent = $(parent)).length ? parent : $('html, body')).length)){
		console.error('Error _scrollTop: Invalid target/parent element!', {target, parent, offset, speed, easing});
		return false;
	}
	offset = !isNaN(offset = Number(offset)) ? offset : 0;
	speed = !isNaN(speed = Number(speed)) && speed >= 100 ? speed : 1000;
	easing = easing || 'easeInOutExpo';
	let scrollTop = target.offset().top + offset;
	parent.animate({scrollTop}, speed, easing);
	return scrollTop;
}

//location
function _location(omit){
	//fn object
	function Location(){
		this.omit = [];
		this.protocol = window.location.protocol;
		this.host = window.location.host;
		this.path = window.location.pathname;
		this.query = window.location.search.substr(1);
		this.hash = window.location.hash;
	}
	
	//set omit
	Location.prototype.setOmit = function(k){
		this.omit = (Array.isArray(k) ? k : ('string' === typeof k ? [k] : []))
		.map(o => ['path', 'query', 'hash'].includes(o = 'string' === typeof o ? o.toLowerCase() : 0) ? o : 0)
		.filter(o => !!o);
		return this;
	};
	
	//to string
	Location.prototype.toString = function(){
		let url = this.protocol + '//' + this.host;
		let path = this.omit.indexOf('path') < 0 && this.path ? this.path : '';
		let query = this.omit.indexOf('query') < 0 && this.query ? this.query : '';
		let hash = this.omit.indexOf('hash') < 0 && this.hash ? this.hash : '';
		if (path) url += path;
		url = url .trim('/') + (query || hash ? '/' : '');
		if (query) url += '?' + query;
		if (hash) url += hash;
		return url;
	};
	
	//result
	let loc = new Location();
	if (omit) loc.setOmit(omit);
	return loc;
}

//event prevent default (stop propagation)
function _prevent(event, _stop, _result=false){
	if (event && 'function' === typeof event.preventDefault){
		event.preventDefault();
		if (_stop && 'function' === typeof event.stopPropagation) event.stopPropagation();
	}
	return _result;
}

//screen width size - update body attrs (screen-size, screen-width)
function _screenSize(){
	let size = 'lg', width = window.innerWidth > 0 ? window.innerWidth : screen.width;
	if (width <= 575.98) size = 'xs';
	else if (width > 575.98 && width <= 767.98) size = 'sm';
	else if (width > 767.98 && width <= 991.98) size = 'md';
	else if (width > 991.98 && width <= 1199.98) size = 'lg';
	else if (width > 1199.98) size = 'xl';
	$('body').attr('screen-size', size);
	$('body').attr('screen-width', width);
	return {size, width};
}

//query string to params object
function _queryParams(query){
	query = query || window.location.search.substr(1);
	return query ? (/^[?#]/.test(query) ? query.slice(1) : query).split('&').reduce((params, param) => {
		let [key, value] = param.split('=');
		params[key] = value ? decodeURIComponent(value.replace(/\+/g, ' ')) : '';
		return params;
	}, {}) : {};
}

//preload images
function _preloadImages(...images){
	if (!window._preloadImagesList) window._preloadImagesList = [];
	images.forEach((item, i) => {
		let src;
		if (_isObject(item) && (item = $(item)).length) src = _tstr(item.attr('src'));
		else if (_isString(item, 1)) src = _tstr(item);
		if (!src) return console.error('Invalid preload image item:', i, item);
		const img = new Image();
		img.onload = function(){
			let index = _preloadImagesList.indexOf(this);
			if (index !== -1) _preloadImagesList.splice(index, 1);
		};
		img.onerror = function(){
			console.error('image onerror:', {img, src});
		};
		_preloadImagesList.push(img);
		img.src = _tstr(src);
	});
}

//alert html
function _alertHtml(content, type='alert-info', _dismissible=1){
	let buffer = `<div class="alert ${type} alert-dismissible fade show" role="alert">`;
	buffer += _tstr(content);
	if (_dismissible){
		buffer += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
		buffer += '<span aria-hidden="true">&times;</span>';
		buffer += '</button>';
	}
	buffer += '</div>';
	return buffer;
}

//form data
function _formData(value){
	let form = _getElement(value);
	if (form){
		if (!_isForm(form)){
			let el = document.createElement('form');
			el.innerHTML = form.outerHTML;
			form = el;
		}
		return new FormData(form);
	}
	return _objFormData(value);
}

//object to form data
function _objFormData(obj, fd, pre){
	fd = fd || new FormData();
	if (_isUndefined(obj)) return fd;
	else if (_isArray(obj)) obj.forEach(value => _objFormData(value, fd, _tstr(pre) + '[]'));
	else if (_isObject(obj) && !_isFile(obj) && !_isDate(obj)){
		Object.keys(obj).forEach(key => {
			let value = obj[key];
			if (_isArray(value)){
				while (key.length > 2 && key.lastIndexOf('[]') === key.length - 2){
					key = key.substring(0, key.length - 2);
				}
			}
			let kpre = pre ? (pre + '[' + key + ']') : key;
			_objFormData(value, fd, kpre);
		});
	}
	else fd.append(pre, obj);
	return fd;
}

//form data to string
FormData.prototype.toString = function(){
	return [...this.entries()].map(entry => `${encodeURIComponent(entry[0])}=${encodeURIComponent(entry[1])}`).join('&');
};

//form alert
function _formAlert(form, message, type='alert-info'){
	let tmp, alert_slot;
	if (!(tmp = $(form)).length) return console.error('Invalid form alert selector.', {form, message, type});
	else form = tmp;
	if ((alert_slot = form.find('.alert-slot')).length){
		alert_slot.html(_isString(message, 1) ? _alertHtml(message, type) : '');
	}
}

//form submit error
function _formSubmitError(form, err){
	//error - update validation errors
	let has_errors = [];
	if (_isObject(err.errors)){
		for (let key in err.errors){
			let val = err.errors[key];
			let vstr = _arr(val).join("<br>");
			let input = form.find(`[name="${key}"]`);
			if (input.length){
				let invalid_id = key + '-' + _uid();
				input.addClass('is-invalid');
				input.attr('invalid-uid', invalid_id);
				let invalid_hint = `<div class="invalid-feedback d-block ${key} ${invalid_id}">${vstr}</div>`;
				if (_tstr(input.parent().attr('class')).indexOf('input-group') > -1){
					input.parent().after(invalid_hint);
				}
				else input.after(invalid_hint);
			}
			else has_errors.push(vstr);
		}
	}
	
	//error message
	let message = '', tmp = (_hasKey(err.data, 'message') ? _tstr(err.data.message) : null) || _tstr(err.message);
	if (has_errors.length) message = has_errors.join('<br>');
	if (tmp.length && !message && err.status != 422) message = tmp + (message ? '<br>' : '') + message;
	
	//result - message
	return message;
}

//form busy
function _formBusy(form, btn){
	let tmp = _getElement(form);
	if (!tmp) return console.error('Invalid form element!', {form, btn});
	else form = $(tmp);
	btn = _getElement(btn);
	const state = {
		btn,
		form,
		busy: 0,
		btn_text: null,
		btn_loading: btn ? _btnLoading(btn) : null,
		setBusy: function(is_busy, alert_message, alert_type, btn_text){
			if (is_busy){
				_formAlert(this.form, alert_message, alert_type);
				if (!this.busy){
					this.form.addClass('busy');
					this.form.find('.is-invalid').trigger('change');
				}
				if (this.btn_loading && (!this.busy || this.busy && _isString(btn_text, 1) && btn_text != this.btn_text)){
					this.btn_loading.setLoading(1, btn_text);
					this.btn_text = btn_text;
				}
			}
			else {
				_formAlert(this.form, alert_message, alert_type);
				if (this.btn_loading) this.btn_loading.setLoading();
				this.form.removeClass('busy');
			}
			this.busy = is_busy = Boolean(is_busy);
		},
	};
	return state;
}

//button loading
function _btnLoading(_btn){
	
	//ignore invalid selector
	if (!$(_btn).length) return console.error('Error _btnLoading: Invalid selector!', _btn);
	
	//set button
	const btn = $(_btn);
	
	//set btn values
	let btn_html = btn.html();
	let btn_success = btn.attr('text-success') || 'Success!';
	let btn_error = btn.attr('text-error') || 'Error!';
	let btn_disabled = !!btn.attr('disabled');
	let tmp_class = '', btn_class = btn.attr('class');
	if (btn_class && (btn_class = _tstr(btn_class)).length){
		tmp_class = btn_class.split(' ')
		.filter(o => !o.match(/btn-(outline-)?(primary|secondary|success|danger|warning|info|light|dark|google|facebook|twitter)/))
		.join(' ').trim();
	}
	
	//btn loading object
	const btn_loading = {
		btn,
		btn_html,
		btn_disabled,
		btn_class,
		tmp_class,
		setClass: function(cls){
			if (_isset(cls)) cls = _tstr(this.tmp_class + ' ' + cls);
			else cls = this.btn_class;
			this.btn.attr('class', cls);
			return this;
		},
		setDisabled: function(disabled){
			if (disabled) this.btn.attr('disabled', 'disabled');
			else {
				if (this.btn_disabled) this.btn.attr('disabled', 'disabled');
				else this.btn.removeAttr('disabled');
			}
			return this;
		},
		setHtml: function(html){
			this.btn.html(_isset(html) ? html : this.btn_html);
			return this;
		},
		setLoading: function(status, text, _restore, _callback){
			//loading = 1
			if (status == 1){
				text = text || this.btn.attr('text-loading') || 'Please wait...';
				this.setHtml(`<i class="fas fa-circle-notch fa-spin mr-2"></i>&nbsp;${text}`)
				.setClass('btn-secondary')
				.setDisabled(1);
			}
			
			//success = 2
			else if (status == 2){
				text = text || this.btn.attr('text-success') || 'Success';
				this.setHtml(`<i class="fas fa-check mr-2"></i>&nbsp;${text}`)
				.setClass('btn-success')
				.setDisabled(1);
			}
			
			//error = -1
			else if (status == -1){
				text = text || this.btn.attr('text-error') || 'Error';
				this.setHtml(`<i class="fas fa-exclamation-circle mr-2"></i>&nbsp;${text}`)
				.setClass('btn-danger')
				.setDisabled(1);
			}
			
			//default
			else if (!status){
				this.setHtml().setClass().setDisabled();
			}
			
			//restore
			if (status && _restore && (_restore = _aint(_restore)) > 0){
				setTimeout(() => {
					this.setLoading()
					if (_isFunc(_callback)) _callback.call(this);
				}, _restore);
			}
		},
	};
	
	//result
	return btn_loading;
}

//window.open features (features = csv|object)
function _winOpenFeatures(features){
	let bools = [
		'channelmode', 'directories', 'fullscreen', 'location', 'menubar',
		'resizable', 'scrollbars', 'status', 'titlebar', 'toolbar',
	],
	pixels = ['height', 'left', 'top', 'width'],
	buffer = [],
	buffer_add = (key, val) => {
		key = _lower(key, 1);
		if (bools.includes(key)){
			val = _str(val, 1, 0, 1).toLowerCase();
			val = ['true', 'yes', '1'].includes(val) ? 'yes' : 'no';
			buffer.push(`${key}=${val}`);
		}
		else if (pixels.includes(key)){
			val = _num(val);
			buffer.push(`${key}=${val}`);
		}
	};
	if (_isObject(features)){
		for (let key in features) buffer_add(key, features[key]);
	}
	else if (_isString(features, 1)){
		features.split(',').forEach(item => {
			let i, key, val;
			if (!((item = _tstr(item)).length && (i = item.indexOf('=')) > -1)) return;
			if (((key = _tstr(item.substr(0, i))).length && (val = _tstr(item.substr(i + 1))).length)) return;
			buffer_add(key, val);
		});
	}
	if (buffer.length) return buffer.join(',');
}

//window.open (target: 'name'|_blank|_parent|_self|_top)
function _winOpen(url, title, target, options){
	let opts = {width: 400, height: 500};
	opts = _merge(opts, options);
	let s_left = window.screenLeft !== undefined ? window.screenLeft : window.screen.left;
	let s_top = window.screenTop !== undefined ? window.screenTop : window.screen.top;
	let width = window.innerWidth || document.documentElement.clientWidth || window.screen.width;
	let height = window.innerHeight || document.documentElement.clientHeight || window.screen.height;
	opts.left = ((width / 2) - (opts.width / 2)) + s_left;
	opts.top = ((height / 2) - (opts.height / 2)) + s_top;
	let features = _winOpenFeatures(opts);
	const win = window.open(url, target, features);
	win.loading = function(text='Please wait...'){
		let html = `
			<style>
				html {font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol','Noto Color Emoji';font-size:16px;}
				.wrapper {text-align:center;padding:2rem;color:#888;}
				.text {margin-top:1rem;}
				.spinner {width:30px;-webkit-animation:spin 2s infinite linear;animation:spin 2s infinite linear}
				@-webkit-keyframes spin{0%{-webkit-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}
				@keyframes spin{0%{-webkit-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}
			</style>
			<div class="wrapper">
				<svg class="spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
					<path fill="currentColor" d="M288 39.056v16.659c0 10.804 7.281 20.159 17.686 23.066C383.204 100.434 440 171.518 440 256c0 101.689-82.295 184-184 184-101.689 0-184-82.295-184-184 0-84.47 56.786-155.564 134.312-177.219C216.719 75.874 224 66.517 224 55.712V39.064c0-15.709-14.834-27.153-30.046-23.234C86.603 43.482 7.394 141.206 8.003 257.332c.72 137.052 111.477 246.956 248.531 246.667C393.255 503.711 504 392.788 504 256c0-115.633-79.14-212.779-186.211-240.236C302.678 11.889 288 23.456 288 39.056z"></path>
				</svg>
				${(text = _tstr(text)).length ? '<div class="text">' + text + '</div>' : ''}
			</div>
		`;
		this.document.body.innerHTML = html;
	};
	if (title = _tstr(title)) win.document.title = title;
	if (win.focus) win.focus();
	return win;
}
