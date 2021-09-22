/* verify */

//ready
$(function(){
	
	//verification alert
	const _verifyAlert = (msg, type, icon) => {
		let html = `<i class="fas fa-${icon} text-${type}" style="width:30px;height:30px;"></i><p class="text-${type}">${msg}</p>`;
		$('#verify-alert').html(html);
		$('#verify-login').removeClass('d-none');
	};
	
	//perform verification
	_post('/api' + window.location.href.replace(window.location.protocol + '//' + window.location.hostname, ''))
	.then(res => _verifyAlert(res.data.status, 'success', 'check'))
	.catch(err => _verifyAlert(err.data.status, 'danger', 'exclamation-triangle'));
});
