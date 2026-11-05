<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\Voting\Member;
use App\Models\Voting\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicResultsController extends Controller
{
    public function __invoke(): View
    {
        $settings = Setting::firstOrCreate(['id' => 1]);
        $members = Member::with('user')->withCount('votesReceived')->get()
            ->sort(fn ($a, $b) => $b->votes_received_count <=> $a->votes_received_count
                ?: strcmp($a->user->name, $b->user->name))
            ->values();
        $ballots = DB::table('voting_ballots')->orderBy('created_at')->get(['created_at']);
        $totalMembers = $members->count();
        $votesCast = $ballots->count();
        $lastVoteAt = $ballots->last()?->created_at;
        $chartMax = max(1, (int) $members->max('votes_received_count'));
        $cutoff = $members->get(7)?->votes_received_count;
        $tiedAtCutoff = $cutoff !== null && $members->filter(fn ($member) => $member->votes_received_count > $cutoff)->count() < 8
            && $members->filter(fn ($member) => $member->votes_received_count === $cutoff)->count() > 1;
        $timeline = $ballots->groupBy(fn ($ballot) => \Carbon\Carbon::parse($ballot->created_at, 'UTC')->setTimezone('Africa/Cairo')->format('Y-m-d'))
            ->map(fn ($group, $date) => ['date' => $date, 'count' => $group->count()])
            ->values();

        return view('voting.public-results', compact(
            'settings', 'members', 'totalMembers', 'votesCast', 'lastVoteAt', 'chartMax', 'tiedAtCutoff', 'timeline'
        ));
    }
}
