/* x-inputs.input */

$(function(){
	
	//.is-invalid - listener
	$(document).on('keyup change', '.is-invalid', function(e){
		let invalid_uid = $(this).attr('invalid-uid');
		if (invalid_uid){
			$(`.invalid-feedback.${invalid_uid}`).remove();
			$(this).removeAttr('invalid-uid');
		}
		$(this).removeClass('is-invalid');
	});
	
	//input.input-clear - listener
	$('input.input-clear').on('keyup change', function(e){
		let input = $(this), value = input.val(), btn = input.closest('.input-group').find('.input-clear-btn');
		if (!btn.length) return console.error('Error .input-clear-btn missing.', input);
		if (_isset(value)) btn.removeClass('d-none');
		else btn.addClass('d-none');
	});
	
	//.input-clear-btn - click listener
	$('.input-clear-btn').click(function(e){
		let btn = $(this), input = btn.closest('.input-group').find('input.input-clear');
		if (!input.length) return console.error('Error inputs.input-clear missing.', btn);
		input.val('').trigger('change');
	});
	
	//input.toggle-password - change listener (autohide toggle btn)
	$('input.toggle-password').on('change', function(e){
		let input = $(this), btn = input.closest('.input-group').find('.toggle-password-btn');
		if (!btn.length) return console.error('Error .toggle-password-btn missing.', input);
		if (input.attr('disabled') && btn.attr('class').indexOf('d-none') < 0) btn.addClass('d-none');
		else if (!input.attr('disabled') && btn.attr('class').indexOf('d-none') > -1) btn.removeClass('d-none');
	});
	
	//.toggle-password-btn - click listener
	$('.toggle-password-btn').click(function(e){
		return _inputTogglePassword($(this));
	});
});

//input toggle password
function _inputTogglePassword(btn, input, set_showing){
	
	//btn, input
	btn = btn && (btn = $(btn)).length ? btn : null;
	input = input && (input = $(input)).length ? input : null;
	if (btn && !input) input = (input = btn.closest('.input-group').find('input.toggle-password')).length ? input : null;
	else if (!btn && input) btn = (btn = input.closest('.input-group').find('.toggle-password-btn')).length ? btn : null;
	if (!btn && !input) return console.error('Error _inputTogglePassword: Undefined btn/input selector.', _keysMap(['show', 'btn', 'input'], _arr(arguments)));
	
	//vars
	let title_show = btn.attr('title-show')
	, title_hide = btn.attr('title-hide')
	, hidden = btn.find('.icon.password-hidden')
	, showing = btn.find('.icon.password-showing');
	
	//toggle
	if (input.length && hidden.length && showing.length){
		let input_type = _isset(set_showing) ? (set_showing ? 'password' : 'text') : _lower(input.attr('type'), 1);
		let _setShowing = (show) => {
			if (show){
				input.attr('type', 'text');
				if (title_hide != '') input.attr('title', title_hide);
				hidden.addClass('d-none');
				showing.removeClass('d-none');
			}
			else {
				input.attr('type', 'password');
				if (title_show != '') input.attr('title', title_show);
				showing.addClass('d-none');
				hidden.removeClass('d-none');
			}
		};
		_setShowing(input_type == 'password');
	}
}
