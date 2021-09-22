/* components - modals */

$(function(){
	console.debug('components.modals');
});

//make modal
function _modal(options){
	//options
	let _opts = {
		backdrop: true, //boolean|'static'
		keyboard: true,
		focus: true,
		show: true,
		
		size: null, //null|sm|lg
		
		id: 'modal-' + _uid(),
		centered: false,
		
		wrapperClass: null,
		modalClass: null,
		
		headerClass: null,
		headerTitle: null,
		headerClose: true,
		headerHtml: null,
		
		bodyClass: null,
		bodyHtml: null,
		
		footerClass: null,
		footerHtml: null,
		btnClose: {
			enabled: true,
			icon: null,
			label: 'Close',
			type: 'button',
			classes: 'btn btn-secondary',
			callback: null,
		},
		btnPrimary: {
			enabled: true,
			icon: null,
			label: 'Primary',
			type: 'button',
			classes: 'btn btn-primary',
			callback: null,
		},
		btnSecondary: {
			enabled: true,
			icon: null,
			label: 'Secondary',
			type: 'button',
			classes: 'btn btn-dark',
			callback: null,
		},
	},
	opts = _opts.assign(_opts, options);
	
	//vars
	let modal_class = 'modal-dialog';
	if (opts.center) modal_class += ' modal-dialog-centered';
	if (['sm', 'lg'].includes(opts.size)) modal_class += ' modal-' + opts.size;
	
	
	//modal html
	let html = `<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog">`;
	html += `<div class="modal-dialog" role="document">`;
	html += ``;
	html += ``;
	html += ``;
	html += ``;
	html += ``;
}
