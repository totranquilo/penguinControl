@extends ('layout.master')

@section ('pageTitle')
Gebruikers &bull; Staff
@endsection

@section ('content')
<div data-magellan-expedition="fixed">
	<dl class="sub-nav">
		<dd data-magellan-arrival="build">
			<a href="#users">Gebruikers ({{ $usersCount }})</a>
		</dd>
		<dd data-magellan-arrival="build">
			<a href="#expired">Vervallen ({{ $expiredCount }})</a>
		</dd>
		<dd data-magellan-arrival="js">
			<a href="#pending">Nog te valideren ({{ $pendingCount }})</a>
		</dd>
	</dl>
</div>

<fieldset>
	<legend id="users">Gebruikers</legend>
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					<a href="{{ $url }}/order/uid">UID</a>
				</th>
				<th>
					Gebruikersnaam
				</th>
				<th>
					Naam
				</th>
				<th>
					r-nummer
				</th>
				<th>
					<a href="{{ $url }}/order/gid">Primaire groep</a>
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($users as $user)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/login" title="Aanmelden als gebruiker" class="button tiny">
							<img src="/img/icons/login.png" alt="Login" />
						</a><a href="/staff/user/user/{{ $user->id }}/expire" title="Vervaldatum wijzigen" class="button tiny">
							<img src="/img/icons/expire.png" alt="Expire" />
						</a><a href="/staff/user/user/{{ $user->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a><a href="/staff/user/user/{{ $user->id }}/remove" title="Verwijderen" class="button tiny alert remove confirm">
							<img src="/img/icons/remove.png" alt="Verwijderen" />
						</a>
					</div>
				</td>
				<td>{{ $user->uid }}</td>
				<td>{{ $user->getUserInfo ()->username }}</td>
				<td>{{ $user->getUserInfo ()->getFullName () }}</td>
				<td>{{ $user->getUserInfo ()->schoolnr }}</td>
				<td>
					<span class="{{ $user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($user->getGroup ()->name) }}</span>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	<div class="right">
		<a href="/staff/user/user/create" title="Toevoegen" class="button radius">
			<img src="/img/icons/add.png" alt="Toevoegen" />
		</a>
	</div>
</fieldset>

<fieldset>
	<legend id="expired">Vervallen</legend>
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					<a href="{{ $url }}/order/uid">UID</a>
				</th>
				<th>
					Gebruikersnaam
				</th>
				<th>
					Naam
				</th>
				<th>
					r-nummer
				</th>
				<th>
					<a href="{{ $url }}/order/gid">Primaire groep</a>
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($expired as $user)
			<tr class="expired">
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/login" title="Aanmelden als gebruiker" class="button tiny">
							<img src="/img/icons/login.png" alt="Login" />
						</a><a href="/staff/user/user/{{ $user->id }}/expire" title="Vervaldatum wijzigen" class="button tiny alert">
							<img src="/img/icons/expire.png" alt="Expire" />
						</a><a href="/staff/user/user/{{ $user->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a><a href="/staff/user/user/{{ $user->id }}/remove" title="Verwijderen" class="button tiny alert remove confirm">
							<img src="/img/icons/remove.png" alt="Verwijderen" />
						</a>
					</div>
				</td>
				<td>{{ $user->uid }}</td>
				<td>{{ $user->getUserInfo ()->username }}</td>
				<td>{{ $user->getUserInfo ()->getFullName () }}</td>
				<td>{{ $user->getUserInfo ()->schoolnr }}</td>
				<td>
					<span class="{{ $user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($user->getGroup ()->name) }}</span>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</fieldset>

<fieldset>
	<legend id="pending">Nog te valideren</legend>
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Gebruikersnaam</th>
				<th>Naam</th>
				<th>E-mailadres</th>
				<th>r-nummer</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($pending as $user) {{-- Let op; $user is hier UserInfo, niet User --}}
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/validate" title="Valideren" class="button tiny">
							<img src="/img/icons/validate.png" alt="Valideren" />
						</a><a href="/staff/user/user/{{ $user->id }}/reject" title="Weigeren" class="button tiny alert remove confirm">
							<img src="/img/icons/reject.png" alt="Weigeren" />
						</a>
					</div>
				</td>
				<td>{{ $user->username }}</td>
				<td>{{ $user->getFullName () }}</td>
				<td>{{ $user->email }}</td>
				<td>{{ $user->schoolnr }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</fieldset>
@endsection