@extends ('layout.master')

@section ('pageTitle')
Pagina bewerken &bull; Staff
@endsection

@section ('js')
@parent
<script type="text/javascript" src="/js/ace/ace.js"></script>
<script type="text/javascript" src="/js/embedAce.js"></script>
@endsection

@section ('content')
<form action="/staff/page/{{ $page->name }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Pagina bewerken</legend>
		<div class="row">
			<div class="large-6 medium-12 small-12 column">
				<label>Titel:
					<input type="text" name="title" value="{{ Input::old ('title', $page->title) }}" required />
				</label>
				<small class="error">Verplicht veld</small>
			</div>
			<div class="large-4 medium-6 small-12 column">
				<label>Status:
					{{ Form::select
					(
						'published',
						array
						(
							'-1' => 'Concept',
							'0' => 'Gepubliceerd',
							'1' => 'Gepubliceerd met link in menu'
						),
						Input::old ('published', $page->published)
					)
					}}
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-2 medium-6 small-12 column">
				<label>Gewicht:
					<input type="number" name="weight" value="{{ Input::old ('weight', $page->weight) }}" min="-127" max="127" required />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
		</div>
		<div>
			<label>Inhoud (HTML):
				<div id="editor"></div>
				<textarea name="content" required>{{ Input::old ('content', $page->content) }}</textarea>
			</label>
			<small class="error">Verplicht veld</small>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $page->name }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection
