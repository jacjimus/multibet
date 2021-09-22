<!-- contact -->
<section id="contact" class="page-section" style="background-image:url('{{ asset('assets/images/fstats/map-bg.png') }}');">
	<div class="container">
		
		<!-- contact intro -->
		<div class="text-center">
			<h2 class="section-heading text-uppercase text-white">Contact Us</h2>
			
			<h5 class="text-center text-primary">Customer care number 0718 981122</h5>
			
			<h3 class="section-subheading text-white mb-5">
				You can send us your feedback using the form below and our team will respond to your enquiry promptly.
			</h3>
		</div>
		
		<!-- contact form -->
		<x-controls.form
			id="contact-form"
			action="/api/contact-form"
			method="post">
			
			<!-- form grid -->
			<div class="row align-items-stretch mb-5">
				
				<!-- col-left -->
				<div class="col-md-6">
					
					<!-- name -->
					<x-controls.form-group>
						<x-inputs.input
							id="name"
							name="name"
							clear
							required
							placeholder="Your Name *"
							btn-class="btn-secondary" />
					</x-controls.form-group>
					
					<!-- email -->
					<x-controls.form-group>
						<x-inputs.input
							type="email"
							id="email"
							name="email"
							clear
							required
							placeholder="Your Email *"
							btn-class="btn-secondary" />
					</x-controls.form-group>

					<!-- phone -->
					<x-controls.form-group class="mb-md-0">
						<x-inputs.input
							type="tel"
							id="phone"
							name="phone"
							clear
							required
							placeholder="Your Phone *"
							btn-class="btn-secondary" />
					</x-controls.form-group>
					
				</div>
				<!-- /col-left -->
				
				<!-- col-right -->
				<div class="col-md-6">
					
					<!-- message -->
					<x-controls.form-group class="form-group-textarea mb-md-0">
						<x-inputs.textarea
							id="message"
							name="message"
							required
							placeholder="Your Message *" />
					</x-controls.form-group>
					
				</div>
				<!-- /col-right -->
			
			</div>
			<!-- /form-grid -->
			
			<!-- alert-slot -->
			<x-controls.form-group class="alert-slot" />
				
			<!-- submit -->
			<x-controls.form-group class="text-center">
				<button class="btn btn-primary btn-xl text-uppercase" style="min-width:220px;" type="submit" text-success="@lang('Message Sent!')" text-loading="@lang('Sending...')">
					Send Message
				</button>
			</x-controls.form-group>
			
		</x-controls.form>
		<!-- /contact form -->
	
	</div>
</section>
<!-- /contact -->

{{-- contact css --}}
@section('page-head')
	@parent
	<x-controls.css :href="asset('assets/css/fstats/contact.css')" />
@endsection
