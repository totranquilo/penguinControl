@extends ('layout.master')

@section ('pageTitle')
Edit forwarding address
@endsection

@section ('content')
<form action="/mail/forward/{{ $mFwd->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Edit forwarding address</legend>
		<div class="row">
			<div class="large-7 medium-6 small-12 column">
				<label>E-mail address:
					<input type="text" name="source" value="{{ Input::old ('source', $mFwd->source) }}" required />
				</label>
				<small class="error">Required field</small>
			</div>
			<div class="large-5 medium-6 small-12 column">
				<label>Domain:
					{{ Form::select
						(
							'domain',
							$domains,
							Input::old ('domain', substr ($mFwd->source, strpos ($mFwd->email, '@')))
						)
					}}
				</label>
				<small class="error">Required field</small>
			</div>
		</div>
		<div class="row">
			<div class="large-12 medium-12 small-12 column">
				<label>Destination:
					<input type="email" name="destination" value="{{ Input::old ('destination', $mFwd->destination) }}" required />
				</label>
				<small class="error">Enter a valid e-mail address.</small>
			</div>
		</div>
		@section ('custom_fields')
		@show
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $mFwd->id }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection