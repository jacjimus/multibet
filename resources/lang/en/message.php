<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Message Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for messaging purposes.
    |
    */

    'hello' => 'Hello!',
	'hello-name' => 'Hello :name!',
	'verify-email-intro' => 'Please click the button below to verify your email address.',
	'verify-email-action' => 'Verify Email',
	'verify-email-outro' => 'If you did not create an account, no further action is required.',
	'verify-email-salutation' => "Regards,<br>" . config('app.name'),
	'action-paste' => "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\ninto your web browser:",
	
	'contact-form-subject' => 'Contact Form - :name',
	'contact-form-greeting' => 'Hello,',
	'contact-form-intro' => 'You have received a new contact form message:',
	'contact-form-table' => '<table cellpadding="0" cellspacing="0" style="border:1px solid #000;width:100%;">'
		. '<tr>'
		. '<td style="padding:5px 10px;border-bottom:1px solid #888;border-right:1px solid #888;font-weight:bold;">Name</td>'
		. '<td style="padding:5px 10px;border-bottom:1px solid #888;">:name</td>'
		. '</tr>'
		. '<tr>'
		. '<td style="padding:5px 10px;border-bottom:1px solid #888;border-right:1px solid #888;font-weight:bold;">Email</td>'
		. '<td style="padding:5px 10px;border-bottom:1px solid #888;">:email</td>'
		. '</tr>'
		. '<tr>'
		. '<td style="padding:5px 10px;border-bottom:1px solid #888;border-right:1px solid #888;font-weight:bold;">Phone</td>'
		. '<td style="padding:5px 10px;border-bottom:1px solid #888;">:phone</td>'
		. '</tr>'
		. '<tr>'
		. '<td style="padding:5px 10px;border-bottom:1px solid #888;border-right:1px solid #888;font-weight:bold;">Message</td>'
		. '<td style="padding:5px 10px;border-bottom:1px solid #888;">:message</td>'
		. '</tr>'
		. '<tr>'
		. '<td style="padding:5px 10px;border-right:1px solid #888;font-weight:bold;">Timestamp</td>'
		. '<td style="padding:5px 10px;">:timestamp</td>'
		. '</tr>'
		. '</table>',
	'contact-form-salutation' => '<br>Regards,<br><b>' . config('app.name') . '</b>',
	'contact-form-success' => 'Your message has been sent successfully!',
	'contact-form-error' => 'Error processing message.',
];
