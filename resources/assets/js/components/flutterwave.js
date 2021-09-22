/* flutterwave */

//ready
$(function(){
	
	//make payment
	$('#make-payment').click(function(e){
		let form = $(this).closest('#checkout');
		if (!form.length) return console.error('Invalid checkout form.');
		let tmp, public_key, tx_ref, uid, amount, name, email, phone_number;
		if ((tmp = form.find('#public_key')).length) public_key = tmp.val();
		if ((tmp = form.find('#tx_ref')).length) tx_ref = tmp.val();
		if ((tmp = form.find('#uid')).length) uid = _int(tmp.val());
		if ((tmp = form.find('#amount')).length) amount = _num(tmp.val());
		if ((tmp = form.find('#name')).length) name = tmp.val();
		if ((tmp = form.find('#email')).length) email = tmp.val();
		if ((tmp = form.find('#phone_number')).length) phone_number = tmp.val();
		let data = {public_key, tx_ref, uid, amount, name, email, phone_number};
		console.info('checkout', data);
		_flutterwave_checkout(data);
	});
	console.log('ready');
});

//flutterwave checkout
function _flutterwave_checkout({public_key, tx_ref, uid, amount, name, email, phone_number}){
	let redirect_url = location.protocol + '//' + location.hostname + '/api/callback/flutterwave';
	FlutterwaveCheckout({
		public_key,
		tx_ref,
		amount,
		currency: "KES",
		country: "KE",
		payment_options: "card,mobilemoney,ussd",
		redirect_url: redirect_url,
		meta: {uid, timestamp: Math.ceil((new Date()).getTime()/1000)},
		customer: {email, phone_number, name},
		callback: function(data){
			console.log('FlutterwaveCheckout callback:', data);
		},
		onclose: function(){
			console.log('FlutterwaveCheckout onclose');
		},
		/*
		customizations: {
			title: 'Bing Predict',
			description: 'Payment for premium account',
			logo: 'http://www.bingpredict.com/logo.png',
		},
		*/
	});
}
