@extends('layouts.page')

@section('page-title', config('app.name') . ' | Email Verification')

@section('page-slot-class', 'bg-white pb-5')

@section('page-slot')
<div class="text-center mt-4">
	<h2 class="section-heading text-uppercase">Privacy Policy</h2>
	<h3 class="section-subheading text-muted mb-4">Make sure you have read and understood the terms below.</h3>
</div>
<div class="bg-light p-4">
	<h4 id="website">Bing Predict</h4>
	<p>
		Your privacy is very important to us. We are committed to protecting your privacy online.<br>
		We use information we collect about you to provide you with the services which you may access through our web site.<br>
		When you browse our website, we do not collect any personal information.
	</p>
	<p>
		We may ask you to submit personal data to this web site (such as your name, your e-mail address, your mobile phone number)
		to enable us to provide you with the services available through this web site. If you choose to withhold requested information,
		we may not be able to provide you the services required.
	</p>
	<p>We will use the information you provide to us for the following purposes:</p>
	<ul>
		<li>To activate your membership account.</li>
		<li>Communicate with you.</li>
		<li>Provide you with personalized page content and/or layout.</li>
		<li>Send you forgotten password to your personal account.</li>
		<li>Send you information about including our products, services information and commercial/advertising materials.</li>
	</ul>
	<p>
		We do not sell, trade or rent your information provided to us to others.<br>
		We can however transfer this information to other subject in case of sale of the Site.<br>
		We may need to disclose your provided information to third parties where this is required by law.<br>
		We may provide statistics about visitors, sales, traffic, etc. to third party vendors but these will not include any identifying information.
	</p>
	<p>
		Users who don’t wish to receive our emails anymore can unsubscribe by writing to Site provider via contact form.<br>
		When you visit our Site, we automatically log your IP address and pages visited. It helps us administer the Site.<br>
		You consent to our collection and use of information and to the use of that information in accordance with the privacy policy set out above.
	</p>
	<p>
		We may change our Privacy Policy from time to time. We encourage visitors to frequently check this page for any changes to our Privacy Policy.
		Your continued use of this site after any change in this Privacy Policy will constitute your acceptance of such change.
	</p>
	<h5 id="cookies">Cookies</h5>
	<p>
		A “cookie” is a string of information that a website stores on a visitor’s computer,
		and that the visitor’s browser provides to the website each time the visitor returns.
	</p>
	<p>
		This website uses cookies to help identify and track visitors, their interaction and their website access preferences.<br>
		Visitors who do not wish to have cookies placed on their computers should set their browsers to refuse cookies before using this website,
		with the drawback that certain features may not function properly without the aid of cookies.
	</p>
</div>
@endsection
