@extends ('layout.master')

@section ('css')
@parent
<link rel="stylesheet" media="print" href="/css/print.css" />
@endsection

@section ('pageTitle')
Facturatie &bull; Staff
@endsection

@section ('js')
@parent
<script type="text/javascript">
	$(document).ready() {
	$('#selectAllUserLog').change(function () {
		$('input[name="userLogId[]"]').prop('checked', $(this).prop("checked"));
	});
		$('input[name="userLogId[]"]').change(function () {
		$('#selectAllUserLog').prop('checked', false);
	});
	});
</script>
@endsection

@section ('content')
<p>{{ $count }} zoekresultaten</p>

{{ $paginationOn ? $userlogs->links () : '' }}
<form action="/staff/user/log/edit/checked" method="post">
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					Gebruikersnaam
				</th>
				<th>
					r-nummer
				</th>
				<th>
					Datum/Tijd
				</th>
				<th>
					Nieuw
				</th>
				<th>
					Facturatiestatus
				</th>
				<th>
					Primaire groep
				</th>
				<th>
					<input type="checkbox" id="selectAllUserLog" value="true">
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($userlogs as $userlog)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/log/{{ $userlog->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a><a href="/staff/user/log/{{ $userlog->id }}/remove" title="Verwijderen" class="button tiny alert remove confirm">
							<img src="/img/icons/remove.png" alt="Verwijderen" />
						</a>
					</div>
				</td>
				<td>{{ $userlog->user_info->username }}</td>
				<td>{{ $userlog->user_info->schoolnr }}</td>
				<td>{{ $userlog->time }}</td>
				<td><img src="/img/icons/{{ $userlog->nieuw?'validate.png':'reject.png'; }}" alt="" /></td>
				<td>{{ $boekhoudingBetekenis[$userlog->boekhouding]}}</td>
				<td>
					@if (! empty ($userlog->user_info->user))
					<span class="{{ $userlog->user_info->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($userlog->user_info->user->getGroup ()->name) }}</span>
					@endif
				</td>
				<td>
					<input type="checkbox" name="userLogId[]" value="{{$userlog->id}}">
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $paginationOn ? $userlogs->links () : '' }}

	<div class="right">
		<label>Gefactureerd:
			{{ Form::select
				    (
					    'boekhouding',
					    $boekhoudingBetekenis,
					    0
				    )
			}}
		</label>
		<input type="submit" name="submit" value="Verander facturatiestatus" class="button radius"/>
	</div>
</form>

<div id="modalSearch" class="reveal-modal" data-reveal>
	<div class="row">
		<div class="large-12 column">
			<h2>Zoeken</h2>

			<form action="{{ $searchUrl }}" method="GET">
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>Gebruikersnaam:
							<input type="text" name="username" />
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>Naam:
							<input type="text" name="name" />
						</label>
					</div>
				</div>
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>E-mailadres:
							<input type="text" name="email" />
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>Studentnummer:
							<input type="text" name="schoolnr" />
						</label>
					</div>
				</div>
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>Van:
							<input type="date" name="time_van" />
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>Tot:
							<input type="date" name="time_tot" />
						</label>
					</div>
				</div>
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>Gefactureerd:
							{{ Form::select
								(
									'boekhouding',
									array
									(
										'all' => 'Alles',
										'-1'=>'Niet te factureren',
										'0'=>'Nog te factureren',
										'1'=>'Gefactureerd'
									)
								)
							}}
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>Nieuw:
							{{ Form::select
								(
									'nieuw',
									array
									(
										'all' => 'Alles',
										'0' => 'Nee',
										'1' => 'Ja',
									)
								)
							}}
						</label>
					</div>
				</div>
				<label>Pagination:
					<input type="checkbox" name="pagination" value="true" checked="checked"/> Pagination
				</label>

				<button>Zoeken</button>
			</div>
		</div>
	</form>

	<a class="close-reveal-modal">&#215;</a>
</div>
@endsection