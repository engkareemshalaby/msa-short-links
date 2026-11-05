<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="60">
    <meta name="theme-color" content="#102a46">
    <title>Voting results · MSA University</title>
    <link rel="icon" href="{{ asset('images/msa-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/voting-results.css') }}">
</head>
<body>
<header class="site-header"><div class="wrap header-inner"><img src="{{ asset('images/msa-logo-wide.png') }}" alt="MSA University"><span>Member voting · Results dashboard</span></div></header>
<main class="wrap">
    <section class="hero">
        <div><span class="eyebrow">MSA UNIVERSITY ELECTION</span><h1>Voting results<span>.</span></h1><p>Live overview of member voting. This page refreshes every minute.</p></div>
        <div class="status {{ $settings->is_open ? 'open' : 'closed' }}"><span class="status-dot"></span>{{ $settings->is_open ? 'Voting open' : 'Voting closed' }}</div>
    </section>

    <div class="time-strip"><span>As of <strong>{{ now('Africa/Cairo')->format('d M Y') }}</strong></span><span>Last vote <strong>{{ $lastVoteAt ? \Carbon\Carbon::parse($lastVoteAt, 'UTC')->setTimezone('Africa/Cairo')->format('d M Y') : 'No votes yet' }}</strong></span></div>

    <section class="stats" aria-label="Participation summary">
        <div class="stat"><span>Eligible members</span><strong>{{ $totalMembers }}</strong><small>Registered voters</small></div>
        <div class="stat"><span>Votes submitted</span><strong>{{ $votesCast }}</strong><small>Completed ballots</small></div>
        <div class="stat"><span>Participation</span><strong>{{ $totalMembers ? round($votesCast / $totalMembers * 100) : 0 }}%</strong><small>{{ max(0, $totalMembers - $votesCast) }} still to vote</small></div>
        <div class="stat"><span>Selections recorded</span><strong>{{ $members->sum('votes_received_count') }}</strong><small>Eight selections per ballot</small></div>
    </section>

    @if($settings->is_open)<div class="notice">Voting is still open. Rankings may change as more ballots are submitted.</div>@endif
    @if($votesCast && $tiedAtCutoff)<div class="notice tie">There is a tie at the eighth-place boundary. The final eight cannot be confirmed from the current vote totals alone.</div>@endif

    <div class="dashboard-grid">
        <section class="panel ranking"><div class="panel-heading"><div><span class="eyebrow">CANDIDATE RANKING</span><h2>Votes by member</h2></div><span class="panel-count">{{ $totalMembers }} members</span></div>
            @forelse($members as $member)
                <div class="rank-row"><span class="rank-number">{{ $loop->iteration }}</span><span class="rank-name">{{ $member->user->name }}</span><div class="bar-track" role="img" aria-label="{{ $member->votes_received_count }} votes"><span style="width: {{ $member->votes_received_count / $chartMax * 100 }}%"></span></div><strong class="rank-votes">{{ $member->votes_received_count }}</strong></div>
            @empty
                <p class="empty">No members are available yet.</p>
            @endforelse
        </section>
        <aside class="side-panels">
            <section class="panel participation"><span class="eyebrow">TURNOUT</span><h2>Participation</h2><div class="donut" style="--progress: {{ $totalMembers ? min(100, $votesCast / $totalMembers * 100) : 0 }}%"><span><strong>{{ $totalMembers ? round($votesCast / $totalMembers * 100) : 0 }}%</strong><small>voted</small></span></div><p>{{ $votesCast }} of {{ $totalMembers }} members have voted.</p></section>
            <section class="panel activity"><span class="eyebrow">VOTING ACTIVITY</span><h2>Ballots over time</h2>
                @forelse($timeline as $period)
                    <div class="activity-row"><time datetime="{{ $period['date'] }}">{{ \Carbon\Carbon::parse($period['date'])->format('d M Y') }}</time><div class="activity-track"><span style="width: {{ $period['count'] / max(1, $timeline->max('count')) * 100 }}%"></span></div><strong>{{ $period['count'] }}</strong></div>
                @empty
                    <p class="empty">Activity will appear after the first vote.</p>
                @endforelse
                <small class="timezone">Daily totals · Cairo date</small>
            </section>
        </aside>
    </div>
    <footer>MSA University · Member voting · Dates shown in Cairo time zone</footer>
</main>
</body>
</html>
