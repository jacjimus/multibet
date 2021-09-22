<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@else
@if (trim($slot) === config('app.name') && file_exists(resource_path(config('app.logo'))))
<img src="{{ asset('logo.png') }}" class="logo" alt="{{ $slot }}">
@else
{{ $slot }}
@endif
@endif
</a>
</td>
</tr>
