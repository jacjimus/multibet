/* cookies consent */
$(function(){
	
	//redacted localstorage
	localStorage.removeItem('cookies-consent');
	
	//accept
	$('#cookies-consent-accept').click(function(e){
		$('#cookies-consent').addClass('d-none');
		_cookie_set('cookies-consent', String(_int((new Date()).getTime()/1000)), 365);
	});
	
	//check
	_checkCookiesConsent();
});

//cookies consent check
function _checkCookiesConsent(){
	let has_consent = _int(_cookie_get('cookies-consent'));
	if (!has_consent) $('#cookies-consent').removeClass('d-none');
}
