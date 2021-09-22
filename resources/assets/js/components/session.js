/* session */

//ready
$(function(){
	
	//logout click
	$(document).on('click', '[data-logout]', function(e){
		_prevent(e);
		_session.removeToken();
		_goto(_tstr($(this).attr('href')) || '/logout', 'Signing out...');
	});
	
	//login form success - listener
	$(document).on('submit-success', '#login-form', (e, res) => _session.save(res.data));
	
	//oauth click
	$(document).on('click', '[data-oauth]', function(e){
		let el = $(this);
		let provider = _tstr(el.attr('data-oauth'));
		if (!provider) return console.error('Undefined data-oauth provider.', el);
		return _session.oauth(provider, _tstr(el.attr('title')) || _tstr(el.text()));
	});
	
	//oauth message - listener
	window.addEventListener('message', (e) => {
		let token, expires;
		if (!(e.origin === window.origin && _hasKey(e.data, 'token') && (token = _tstr(e.data.token)).length)) return;
		if (_hasKey(e.data, 'expires')) expires = e.data.expires;
		_session.save({token, expires});
	}, false);
	
	//resend email verification - listener
	$(document).on('click', '[resend-email-verification]', function(e){
		//request opts
		let el = $(this)
		, tmp = _tstr(el.attr('resend-email-verification'))
		, data = String(_formData(_jsonParse(atob(decodeURIComponent(tmp)))))
		, form = el.closest('#login-form')
		, url = '/api/email/resend'
		, form_busy = _formBusy(form);
		
		//request
		form_busy.setBusy(1, '<i class="fas fa-spinner fa-spin"></i> Sending verification email...', 'alert-info');
		_post(url, data)
		.then(res => {
			//success
			let message = _hasKey(res, 'data') && _hasKey(res.data, 'status') && _isArray(res.data.status) ? res.data.status.join('<br>') : 'Request successful.';
			form_busy.setBusy(0, message, 'alert-success');
		})
		.catch(err => {
			//error
			let message = _formSubmitError(form, err);
			form_busy.setBusy(0, message, 'alert-danger');
		});
	});
	
	//init session user
	_session.setUser();
});

//session object
const _session = {
	user: null,
	tokenName: 'token',
	getToken: function(){
		return _cookie_get(this.tokenName);
	},
	saveToken: function(token, expires){
		if (_isString(token, 1)){
			token = _tstr(token);
			expires = (expires = _int(expires) ? Math.ceil(expires/86400) : 0) >= 1 ? expires : null;
			_cookie_set(this.tokenName, token, expires);
			return {token, expires};
		}
		else console.error('Invalid save token.', {token, expires});
	},
	removeToken: function(){
		_cookie_delete(this.tokenName);
	},
	reset: function(reload){
		this.user = null;
		this.removeToken();
		if (reload) _reload();
	},
	load: function(reload){
		let params = _queryParams();
		if (_hasKey(params, 'rdr')){
			return _goto(atob(params.rdr));
		}
		if (reload) _reload();
		else this.setUser();
	},
	save: function(data){
		if (!_hasKeys(data, 'token')) return console.error('Invalid session save data.', data);
		let { token, expires } = data;
		this.saveToken(token, expires);
		//_goto('/', 'Please wait...', 1);
		this.load(1);
	},
	check: function(){
		let ncms_session = $('meta[name="ncms-session"]');
		if (ncms_session.length) return _tstr(ncms_session.attr('content'));
	},
	setUser: function(){
		let token = this.getToken();
		if (!_isString(token, 1)) return;
		_get('/api/user')
		.then(res => {
			this.user = res.data;
			if (!_isString(this.check(), 1)) _reload();
		})
		.catch(err => this.reset());
	},
	oauth: function(provider, title){
		if (!(provider = _tstr(provider)).length) return console.error('Oauth login provider is undefined.');
		let url, win = _winOpen('', title = _tstr(title) || _title(`${provider} OAuth`));
		win.loading();
		_post(`/api/oauth/${provider}`)
		.then(res => {
			if (!(_hasKey(res, 'data') && _hasKey(res.data, 'url') && (url = _tstr(res.data.url)).length)){
				win.close();
				return console.error('OAuth fetch url unsupported response.', {provider, title}, res);
			}
			win.location.href = url;
		})
		.catch(err => console.error('OAuth Failure:', {provider, title}, err));
	},
};
