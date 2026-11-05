@extends('layouts.app')
@section('title', __('voting.title'))
@section('subtitle', __('voting.subtitle'))
@push('head')<link rel="stylesheet" href="{{ asset('css/voting-admin.css') }}">@endpush
@section('content')
<div class="vote-admin-stats">
    <div><span class="stat-icon">♙</span><strong>{{ $members->count() }}</strong><span>{{ __('voting.members') }}</span></div>
    <div><span class="stat-icon">✓</span><strong>{{ $ballots->count() }}</strong><span>{{ __('voting.voted') }}</span></div>
    <div><span class="stat-icon">◷</span><strong>{{ max(0, $members->count() - $ballots->count()) }}</strong><span>{{ __('voting.not_voted') }}</span></div>
</div>
<div class="vote-public-link"><a class="button" href="{{ route('voting.results.public') }}" target="_blank" rel="noopener">Public results dashboard ↗</a><span>Share this link to follow the results without signing in.</span></div>
@can('voting.manage')
<section class="card vote-admin-card vote-settings-card">
    <div class="section-heading"><div><h2>{{ __('voting.settings') }}</h2><p>{{ __('voting.settings_hint') }}</p></div></div>
    <form method="POST" action="{{ route('voting.admin.settings') }}">@csrf @method('PUT')
        <div class="vote-admin-settings">
            <label>{{ __('voting.voting_status') }}<select name="is_open"><option value="0" @selected(! $settings->is_open)>{{ __('voting.closed') }}</option><option value="1" @selected($settings->is_open)>{{ __('voting.open') }}</option></select></label>
            <label>{{ __('voting.results_visibility') }}<select name="show_results_to_members"><option value="0" @selected(! $settings->show_results_to_members)>{{ __('voting.hidden') }}</option><option value="1" @selected($settings->show_results_to_members)>{{ __('voting.visible') }}</option></select></label>
            <button class="button primary" type="submit">{{ __('voting.save') }}</button>
        </div>
    </form>
</section>
@endcan
<section class="card vote-admin-card vote-data-card" id="voting-data">
    <div class="vote-tabs" role="tablist" aria-label="{{ __('voting.section') }}">
        <button type="button" class="vote-tab active" role="tab" id="vote-tab-ranking" aria-controls="vote-panel-ranking" aria-selected="true" tabindex="0" data-vote-tab="ranking"><span class="tab-icon">▤</span>{{ __('voting.ranking') }} <span class="tab-count">{{ $members->count() }}</span></button>
        <button type="button" class="vote-tab" role="tab" id="vote-tab-choices" aria-controls="vote-panel-choices" aria-selected="false" tabindex="-1" data-vote-tab="choices"><span class="tab-icon">☑</span>{{ __('voting.member_choices') }} <span class="tab-count">{{ $ballots->count() }}</span></button>
    </div>
    <div id="vote-panel-ranking" class="vote-tab-panel" role="tabpanel" aria-labelledby="vote-tab-ranking" tabindex="0">
        <div class="panel-intro"><div><h2>{{ __('voting.ranking') }}</h2><p>{{ __('voting.ranking_note') }}</p></div></div>
        <div class="vote-admin-list">
            @foreach($members as $member)
                <details class="vote-row"><summary><span class="rank-number {{ $loop->iteration <= 8 ? 'top-eight' : '' }}">{{ $loop->iteration }}</span><span class="person-info"><strong>{{ $member->user->name }}</strong><span class="person-meta"><small class="meta-id">{{ __('voting.staff_id') }}: {{ $member->staff_id }}</small>@if($member->club_name)<small class="meta-club">{{ $member->club_name }}</small>@endif<small class="meta-email">{{ $member->user->email }}</small></span></span><span class="vote-count">{{ __('voting.votes', ['count' => $member->votes_received_count]) }}</span><span class="row-chevron" aria-hidden="true">⌄</span></summary>
                    <div class="vote-row-detail"><span class="detail-label">{{ __('voting.chosen_by') }}</span><div class="name-chips">@forelse($members->whereIn('id', $supportersByCandidate->get($member->id, collect())) as $supporter)<span>{{ $supporter->user->name }}</span>@empty <em>{{ __('voting.none') }}</em> @endforelse</div></div>
                </details>
            @endforeach
        </div>
    </div>
    <div id="vote-panel-choices" class="vote-tab-panel" role="tabpanel" aria-labelledby="vote-tab-choices" tabindex="0" hidden>
        <div class="panel-intro"><div><h2>{{ __('voting.member_choices') }}</h2><p>{{ __('voting.choices_hint') }}</p></div></div>
        <div class="vote-admin-list">
            @foreach($members as $member)
                @php $ballot = $ballots->get($member->id); $selectedIds = $ballot ? ($choices->get($ballot->id)?->pluck('candidate_id') ?? collect()) : collect(); @endphp
                <details class="vote-row"><summary><span class="person-info"><strong>{{ $member->user->name }}</strong><span class="person-meta"><small class="meta-id">{{ __('voting.staff_id') }}: {{ $member->staff_id }}</small>@if($member->club_name)<small class="meta-club">{{ $member->club_name }}</small>@endif<small class="meta-email">{{ $member->user->email }}</small></span></span><span class="vote-status {{ $ballot ? 'has-voted' : '' }}">{{ $ballot ? __('voting.has_voted') : __('voting.has_not_voted') }}</span><span class="row-chevron" aria-hidden="true">⌄</span></summary>
                    <div class="vote-row-detail"><span class="detail-label">{{ __('voting.member_choices') }}</span><div class="name-chips">@if($ballot) @foreach($members->whereIn('id', $selectedIds) as $selected)<span>{{ $selected->user->name }}</span>@endforeach @else <em>{{ __('voting.no_choices') }}</em> @endif</div></div>
                </details>
            @endforeach
        </div>
    </div>
</section>
<script>
(() => {
    const tabs = [...document.querySelectorAll('[data-vote-tab]')];
    const activate = (tab, focus = false) => {
        tabs.forEach(item => {
            const active = item === tab;
            item.classList.toggle('active', active);
            item.setAttribute('aria-selected', active ? 'true' : 'false');
            item.tabIndex = active ? 0 : -1;
            document.getElementById('vote-panel-' + item.dataset.voteTab).hidden = !active;
        });
        if (focus) tab.focus();
    };
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activate(tab));
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
            activate(tabs[next], true);
        });
    });
})();
</script>
@endsection
