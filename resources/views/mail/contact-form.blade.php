@component('mail::message')
# @lang('New contact form message.')

@component('mail::table')
| @lang('Name') | {{ $data['name'] }} |
| :--- | :--- |
| @lang('Email') | {{ $data['email'] }} |
| @lang('Phone') | {{ $data['phone'] }} |
| @lang('Message') | {{ $data['message'] }} |
| @lang('Timestamp') | {{ $data['timestamp'] }} |
@endcomponent

@endcomponent
