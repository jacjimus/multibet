{{-- default --}}
<meta charset="utf-8">
<meta name="google" content="notranslate">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="ncms-env" content="{{ config('app.env') }}">

@if($app_user)
<meta name="ncms-session" content="{{ base64_encode(bcrypt('user-' . $app_user->id)) }}">
@endif

{{-- no cache --}}
@if (x_isset_b($noCache))
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
@endif

{{-- is page --}}
@if (x_isset_b($metaPage))

{{-- owner --}}
@if (strlen($tmp = trim(isset($metaOwner) ? $metaOwner : config('app.owner'))))
<meta name="owner" content="{{ $tmp }}">
@endif

{{-- type --}}
@if (strlen($tmp = trim(isset($metaType) ? $metaType : 'page')))
<meta property="og:type" content="{{ $tmp }}">
@endif

{{-- url --}}
@if (strlen($tmp = trim(isset($metaUrl) ? $metaUrl : url(''))))
<meta property="og:url" content="{{ $tmp }}">
@endif

{{-- name --}}
@if (strlen($tmp = trim(isset($metaName) ? $metaName : config('app.name'))))
<meta itemprop="name" content="{{ $tmp }}">
<meta property="og:site_name" content="{{ $tmp }}">
@endif

{{-- title --}}
@if (strlen($tmp = trim(isset($metaTitle) ? $metaTitle : (strlen($tmp = trim($__env->yieldContent('page-title'))) ? $tmp : config('app.name')))))
<meta name="title" content="{{ $tmp }}">
<meta itemprop="title" content="{{ $tmp }}">
<meta property="og:title" content="{{ $tmp }}">
@endif

{{-- description --}}
@if (strlen($tmp = trim(isset($metaDescription) ? $metaDescription : config('app.description'))))
<meta name="description" content="{{ $tmp }}">
<meta itemprop="description" content="{{ $tmp }}">
<meta property="og:description" content="{{ $tmp }}">
@endif

{{-- keywords --}}
@if (strlen($tmp = trim(isset($metaKeywords) ? $metaKeywords : config('app.keywords'))))
<meta name="keywords" content="{{ $tmp }}">
@endif

{{-- image --}}
@if (strlen($tmp = trim(isset($metaImage) ? $metaImage : (strlen($tmp = trim(config('app.image'))) ? asset($tmp) : ''))))
<meta itemprop="image" content="{{ $tmp }}">
<meta property="og:image" content="{{ $tmp }}">
@endif

@endif
{{-- /meta page --}}
