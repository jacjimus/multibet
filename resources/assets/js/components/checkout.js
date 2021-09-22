/* checkout */

//ready
$(function(){
	//checkout-form submit - listener
	$('#checkout-form').on('submit', function(e){
		
		//prevent default
		_prevent(e, 1);
		
		//set vars
		let form = $(this), btn = form.find('#make-payment');
		if (!btn.length) return console.error('Failed to locate #make-payment.');
		let data = String(_formData(form));
		
		//form busy
		let form_busy = _formBusy(form, btn);
		form_busy.setBusy(1, null, null, 'Sending STK Request...');
		
		//stk push request
		_post('/api/stk-push', data)
		.then(res => {
			
			//success - check response
			let trans_poll, trans_id, message, phone_number, phone_number_input;
			if (!(
				_hasKey(res, 'data')
				&& _hasKeys(res.data, 'trans_id', 'message', 'phone_number')
				&& (trans_id = _tstr(res.data.trans_id))
				&& (message = _tstr(res.data.message))
				&& (phone_number = _tstr(res.data.phone_number))
			)){
				let msg = 'Unexpected stk-push response.';
				form_busy.setBusy(0, msg, 'alert-danger');
				return console.error(msg, res);
			}
			
			//form busy update
			form_busy.setBusy(1, message, 'alert-success', 'Awaiting your payment...');
			
			//update phone number input
			if ((phone_number_input = form.find('#phone_number')).length){
				phone_number_input.val(res.data.phone_number).trigger('change');
			}
			
			//stk-poll method
			trans_poll = () => {
				
				//stk-poll request
				_get(`/api/stk-poll/${trans_id}`)
				.then(res => {
					
					//success - check response
					let status;
					if (!(
						_hasKey(res, 'data')
						&& _hasKey(res.data, 'status')
						&& (status = _tstr(res.data.status))
					)){
						let msg = 'Unexpected stk-poll response.';
						form_busy.setBusy(0, msg, 'alert-danger');
						return console.error(msg, res);
					}
					let message = (_hasKey(res.data, 'message') ? _tstr(res.data.message) : null) || `Payment ${status}.`;
					
					//check status
					if (status == 'pending'){
						setTimeout(() => trans_poll(), 5000); //call stk-poll (delayed)
					}
					else if (status == 'success'){
						form_busy.setBusy(0, message, 'alert-success');
						_goto('/', 'Redirecting...', 1); //redirect
					}
					else {
						form_busy.setBusy(0, message, 'alert-warning'); //failure
					}
				})
				.catch(err => {
					form_busy.setBusy(0, err.message, 'alert-danger'); //error
				});
			};
			
			//call stk-poll
			trans_poll();
		})
		.catch(err => {
			let message = (_hasKey(err.data, 'message') ? _tstr(err.data.message) : null) || err.message;
			form_busy.setBusy(0, message, 'alert-danger'); //error
		});
	});
});
