/* components - alerts */

//alerts object
const _alerts = {
	options: function(options, _default){
		let config = {};
		if (_isObject(options)){
			let opts = _isObject(_default) ? Object.assign(options, _default) : options;
			for (let key in opts){
				if (['message','text','html'].includes(key)) key = 'html';
				if (key == 'type') key = 'icon';
				config[key] = opts[key];
			}
		}
		console.debug('config', config);
		return config;
	},
	block: function(options){
		return Swal.fire(this.options(options, {
			allowOutsideClick: false,
			onBeforeOpen: () => Swal.showLoading(),
		}));
	},
	alert: function(options){
		return Swal.fire(this.options(options, {icon: 'info'}));
	},
	confirm: function(options, on_confirm, on_cancel){
		return Swal.fire(this.options(options, {
			icon: 'question',
			showCancelButton: true,
			cancelButtonColor: '#d33',
			cancelButtonText: 'Cancel',
			confirmButtonColor: '#3085d6',
			confirmButtonText: 'Ok',
		}))
		.then((result) => {
			if (result.isConfirmed){
				if (_isFunc(on_confirm)) on_confirm(result);
			}
			else if (_isFunc(on_cancel)) on_cancel(result);
			//result.isDismissed
			//result.isDenied
			return result;
		});
	},
	close: function(){
		return Promise.resolve(Swal.close());
	},
}
