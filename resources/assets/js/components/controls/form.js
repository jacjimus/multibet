/* x-controls.form */

$(function(){
	
	//form reset - listener
	$(document).on('reset', 'form', function(e){
		console.debug('form reset', $(this));
		$(this).find('input, textarea').trigger('change');
	});

	//form submit - listener
	$(document).on('submit', 'form', function(e){
		
		//prevent default
		_prevent(e);
		
		//submit opts
		let form = $(this)
		, type = _tstr(form.attr('method')) || 'post'
		, url = form.attr('action')
		, data = String(_formData(form));
		
		//form opts
		let no_reset = _isset(form.attr('no-reset'))
		, success_busy = _isset(form.attr('success-busy'))
		, submit_btn = form.find('[type="submit"]');
		
		//submit btn alt
		submit_btn = submit_btn.length ? submit_btn : form.find('.submit-btn');
		
		//form busy
		let form_busy = _formBusy(form, submit_btn);
		form_busy.setBusy(1);
		
		//form submit request
		_request(url, data, type)
		.then(res => {
			//success - form busy update
			form_busy.setBusy(0, res.message, 'alert-success');
			if (success_busy) form.addClass('busy'); //success busy
			if (!no_reset) form.trigger('reset'); //trigger reset
			form.trigger('submit-success', res); //trigger event "submit-success"
		})
		.catch(err => {
			//error
			let message = _formSubmitError(form, err);
			form_busy.setBusy(0, message, 'alert-danger');
			form.trigger('submit-error', err); //trigger "submit-error"
		});
	});
});

