/* request */

//ready
$(function(){
	
	//ajax setup
	$.ajaxSetup({
		beforeSend: function(xhr, settings){
			let url = settings.url;
			if (!(!url.match(/https?/i) || url.indexOf(window.location.hostname) > -1)) return console.debug('external request', url);
			let tmp, csrf_token = (tmp = _isString($('meta[name="csrf-token"]').attr('content'), 1)) ? tmp.value : null;
			let locale = (tmp = _isString($('html').attr('lang'), 1)) ? tmp.value : 'en';
			let token = window._session ? _session.getToken() : null;
			if (csrf_token) xhr.setRequestHeader('X-CSRF-TOKEN', csrf_token);
			if (locale) xhr.setRequestHeader('Accept-Language', locale);
			if (token) xhr.setRequestHeader('Authorization', `Bearer ${token}`);
		},
	});
	
	//request link click
	$(document).on('click', '[request]', function(e){
		_prevent(e);
		let link = $(this), href = link.attr('href'), method = link.attr('request'), busy_text = link.attr('busy-text');
		console.log('request click:', {href, method, busy_text});
	});
});

//get request
function _get(url){
	return _request(url);
}

//post request
function _post(url, data){
	return _request(url, data, 'post');
}

//request promise
function _request(url, data, type='GET'){
	return new Promise(function(resolve, reject){
		url = _tstr(url);
		type = _requestType(type);
		$.ajax({
			type, url, data,
			processData: false,
			contentType: 'application/x-www-form-urlencoded',
			success: function(data, type, xhr){
				return resolve(_requestResponse({data, type, xhr}));
			},
			error: function(xhr, type, error){
				return reject(_requestResponse({xhr, type, error}));
			},
		});
	});
}

//request type
function _requestType(type){
	let types = ['GET','POST','PUT','DELETE'];
	type = _upper(type, 1);
	return types.includes(type) ? type : 'GET';
}

//request response
function _requestResponse({xhr, type, data, error}){
	if (!_hasKeys(xhr, 'status', 'responseText')){
		return console.error('Invalid request response xhr object!', {xhr, type, data, error});
	}
	let {status, responseText, responseJSON} = xhr;
	let tmp, errors, message, tmp_data = responseText;
	if (_isObject(responseJSON)){
		tmp_data = responseJSON;
		if (_hasKey(responseJSON, 'message') && (tmp = _tstr(responseJSON.message)).length) message = tmp;
		else if (_hasKey(responseJSON, 'error') && (tmp = _tstr(responseJSON.error)).length) message = tmp;
		if (_hasKey(responseJSON, 'errors') && _isObject(tmp = responseJSON.errors, 1)) errors = tmp;
	}
	else if (xhr.getResponseHeader('content-type').indexOf('text/plain') > -1 && (tmp = _tstr(responseText)).length) message = tmp;
	if (!message) message = _tstr(error);
	if (error && _isString(message, 1) && ![422].includes(status)) message = `[${status}] ${message}`;
	data = data ? data : tmp_data;
	return {type, message, data, errors, error, status, xhr};
}
