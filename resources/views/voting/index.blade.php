<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#072841">
    <title>Member voting · MSA University</title>
    <link rel="icon" href="{{ asset('images/msa-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/voting.css') }}"><link rel="stylesheet" href="{{ asset('css/voting-ballot.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="ballot-page">
<svg class="icon-defs" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><symbol id="i-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol><symbol id="i-users" viewBox="0 0 24 24"><path d="M16 20v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 20v-2a4 4 0 0 0-3-3.87M16 2.13a4 4 0 0 1 0 7.75"/></symbol><symbol id="i-lock" viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></symbol><symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6"/></symbol><symbol id="i-spark" viewBox="0 0 24 24"><path d="m12 2 1.8 7.2L21 11l-7.2 1.8L12 20l-1.8-7.2L3 11l7.2-1.8L12 2ZM19 18l.6 1.4L21 20l-1.4.6L19 22l-.6-1.4L17 20l1.4-.6L19 18Z"/></symbol></svg>
<header class="ballot-header"><div class="ballot-wrap ballot-header-inner"><a class="ballot-brand" href="{{ route('voting.index') }}"><img src="{{ asset('images/msa-logo-wide.png') }}" alt="MSA University"><span class="brand-divider"></span><strong>Member voting</strong></a><div class="ballot-account"><span class="account-name">{{ auth()->user()->name }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="signout-button">Sign out <svg class="ui-icon"><use href="#i-arrow"/></svg></button></form></div></div></header>
<main class="ballot-wrap">
    <section class="ballot-hero"><span class="ballot-eyebrow"><svg class="ui-icon"><use href="#i-spark"/></svg> MSA UNIVERSITY ELECTION</span><h1>Choose your <span>eight members.</span></h1><p>Make your selections from the members below. Take your time to review them before submitting your final vote.</p><div class="hero-rule"></div></section>
    @if(session('success'))<div class="ballot-notice success" role="status"><svg class="ui-icon"><use href="#i-check"/></svg>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="ballot-notice error" role="alert">{{ $errors->first() }}</div>@endif
    @if($ballot)
        <section class="ballot-complete"><span class="complete-icon"><svg class="ui-icon"><use href="#i-check"/></svg></span><h2>Your vote has been recorded.</h2><p>Thank you for taking part. Your selection is final.</p><div class="selected-list"><strong>Your selections</strong><div>@foreach($candidates->whereIn('id', $choices) as $chosen)<span>{{ $chosen->user->name }}</span>@endforeach</div></div></section>
    @elseif(! $settings->is_open)
        <section class="ballot-complete"><span class="complete-icon muted"><svg class="ui-icon"><use href="#i-lock"/></svg></span><h2>Voting is currently closed.</h2><p>Please check back when voting opens.</p></section>
    @else
        <form method="POST" action="{{ route('voting.store') }}" id="vote-form">@csrf
            <div class="ballot-section-head"><div><span class="section-icon"><svg class="ui-icon"><use href="#i-users"/></svg></span><div><h2>Available members</h2><p>{{ $candidates->count() }} people to choose from · Your name is excluded</p></div></div><span class="selection-pill"><svg class="ui-icon"><use href="#i-check"/></svg><strong id="selected-count">0</strong> / 8 selected</span></div>
            <div class="ballot-grid">@foreach($candidates as $candidate)<label class="member-card"><input type="checkbox" name="choices[]" value="{{ $candidate->id }}" @checked(in_array($candidate->id, old('choices', [])))><span class="member-check"><svg class="ui-icon"><use href="#i-check"/></svg></span><span class="member-avatar">{{ mb_substr($candidate->user->name, 0, 1) }}</span><span class="member-name">{{ $candidate->user->name }}</span></label>@endforeach</div>
            <div class="ballot-action-bar"><div class="ballot-action-inner"><div class="progress-copy"><span class="progress-icon"><svg class="ui-icon"><use href="#i-check"/></svg></span><div><strong id="selection-message">Choose 8 members to continue</strong><small>Your vote cannot be changed after submission.</small><span class="progress-track"><span id="progress-fill"></span></span></div></div><button id="submit-vote" type="submit" disabled>Review & submit <svg class="ui-icon"><use href="#i-arrow"/></svg></button></div></div>
        </form>
    @endif
    @if($results)<section class="ballot-results"><h2>Results</h2><ol>@foreach($results as $result)<li><span>{{ $result->user->name }}</span><strong>{{ $result->votes_received_count }} votes</strong></li>@endforeach</ol></section>@endif
</main>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" defer></script>
<script>
const boxes = [...document.querySelectorAll('.member-card input')];
const count = document.getElementById('selected-count');
const submit = document.getElementById('submit-vote');
const message = document.getElementById('selection-message');
const progress = document.getElementById('progress-fill');
function refresh() {
    if (!count) return;
    const chosen = boxes.filter(box => box.checked).length;
    count.textContent = chosen;
    submit.disabled = chosen !== 8;
    message.textContent = chosen === 8 ? 'Your 8 selections are ready' : `Choose ${8 - chosen} more ${8 - chosen === 1 ? 'member' : 'members'}`;
    progress.style.width = `${chosen * 12.5}%`;
    boxes.forEach(box => {
        box.disabled = !box.checked && chosen >= 8;
        box.closest('.member-card').classList.toggle('selected', box.checked);
    });
}
boxes.forEach(box => box.addEventListener('change', refresh));
refresh();
document.getElementById('vote-form')?.addEventListener('submit', async event => {
    event.preventDefault();
    if (boxes.filter(box => box.checked).length !== 8) return;
    if (!window.Swal) { document.getElementById('vote-confirm-fallback').showModal(); return; }
    const result = await Swal.fire({
        title: 'Submit your vote?',
        text: 'You have selected 8 members. Your choices cannot be changed after submission.',
        icon: 'question', showCancelButton: true,
        confirmButtonText: 'Yes, submit my vote', cancelButtonText: 'Review choices',
        confirmButtonColor: '#538F3F', cancelButtonColor: '#62717a', reverseButtons: true,
        customClass: { popup: 'msa-swal' }
    });
    if (result.isConfirmed) submitVote();
});
function submitVote() {
    const form = document.getElementById('vote-form');
    if (!form || submit.disabled) return;
    submit.disabled = true;
    HTMLFormElement.prototype.submit.call(form);
}
</script>
<dialog id="vote-confirm-fallback" class="vote-dialog"><h2>Submit your vote?</h2><p>You have selected 8 members. Your choices cannot be changed after submission.</p><div><button type="button" onclick="this.closest('dialog').close()">Review choices</button><button type="button" onclick="this.closest('dialog').close();submitVote()">Yes, submit my vote</button></div></dialog>
</body></html>
