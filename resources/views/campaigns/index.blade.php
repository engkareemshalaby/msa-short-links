@extends('layouts.app')

@section('title', __('Campaigns & tags'))
@section('subtitle', __('Create campaigns, group related links and follow their performance.'))

@section('content')
    <div class="campaign-layout">
        <section class="campaign-list">
            <div class="section-heading">
                <div>
                    <h2>{{ __('Campaign performance') }}</h2>
                    <p>{{ __('Each campaign shows its related links and total human visits.') }}</p>
                </div>
                <span class="badge muted">{{ trans_choice(':count campaigns', $campaigns->count(), ['count' => $campaigns->count()]) }}</span>
            </div>

            @forelse ($campaigns as $campaign)
                <article class="campaign-card">
                    <div class="campaign-summary">
                        <div>
                            <h3>{{ $campaign->name }}</h3>
                            @if ($campaign->description)
                                <p>{{ $campaign->description }}</p>
                            @endif
                            <div class="utm-chips">
                                @foreach (['utm_source' => 'Source', 'utm_medium' => 'Medium', 'utm_campaign' => 'Campaign'] as $field => $label)
                                    @if ($campaign->{$field})
                                        <span><small>{{ $label }}</small>{{ $campaign->{$field} }}</span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="campaign-stats">
                            <div><strong>{{ number_format($campaign->links_count) }}</strong><span>{{ __('Links') }}</span></div>
                            <div><strong>{{ number_format($campaign->total_visits) }}</strong><span>{{ __('Visits') }}</span></div>
                        </div>
                    </div>

                    <div class="campaign-links">
                        <div class="campaign-links-title">{{ __('Campaign links') }}</div>
                        @forelse ($campaign->links as $link)
                            <div class="campaign-link-row">
                                <div class="campaign-link-main">
                                    <a href="{{ route('links.show', $link) }}">{{ $link->title ?: __('Untitled link') }}</a>
                                    <span>{{ $link->short_url }}</span>
                                </div>
                                <span class="campaign-link-destination">{{ $link->destination_url }}</span>
                                <strong>{{ number_format($link->visits_count) }} <small>{{ __('visits') }}</small></strong>
                                <a class="button small" href="{{ route('links.show', $link) }}">{{ __('View') }}</a>
                            </div>
                        @empty
                            <div class="campaign-empty">{{ __('No links have been assigned to this campaign yet.') }}</div>
                        @endforelse
                    </div>
                </article>
            @empty
                <div class="card"><div class="card-body empty-state"><strong>{{ __('No campaigns yet.') }}</strong>{{ __('Create a campaign, then select it when creating a short link.') }}</div></div>
            @endforelse
        </section>

        <aside class="campaign-side">
            <div class="card">
                <div class="card-header"><div><h2>{{ __('Create campaign') }}</h2><p>{{ __('Optional UTM values are added to its links.') }}</p></div></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('campaigns.store') }}" class="form-grid">@csrf
                        <label class="field full"><span>{{ __('Name') }}</span><input name="name" required placeholder="Admissions 2026"></label>
                        <label class="field"><span>UTM source</span><input name="utm_source" placeholder="facebook"></label>
                        <label class="field"><span>UTM medium</span><input name="utm_medium" placeholder="social"></label>
                        <label class="field full"><span>UTM campaign</span><input name="utm_campaign" placeholder="admissions-2026"></label>
                        <label class="field full"><span>{{ __('Description') }}</span><textarea name="description" placeholder="{{ __('Optional internal description') }}"></textarea></label>
                        <div class="form-footer"><button class="button primary">{{ __('Create campaign') }}</button></div>
                    </form>
                </div>
            </div>

            <div class="card tags-card">
                <div class="card-header"><h2>{{ __('Tags') }}</h2></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tags.store') }}" class="form-grid">@csrf
                        <label class="field"><span>{{ __('Name') }}</span><input name="name" required></label>
                        <label class="field"><span>{{ __('Color') }}</span><input name="color" value="#538F3F" pattern="#[0-9A-Fa-f]{6}"></label>
                        <div class="form-footer"><button class="button">{{ __('Create tag') }}</button></div>
                    </form>
                    <div class="tag-list">
                        @forelse ($tags as $tag)
                            <article class="tag-performance">
                                <div class="tag-performance-head"><div><span style="color:{{ $tag->color }}">●</span><strong>{{ $tag->name }}</strong></div><div class="tag-performance-stats"><span><b>{{ number_format($tag->links_count) }}</b> {{ __('links') }}</span><span><b>{{ number_format($tag->total_visits) }}</b> {{ __('visits') }}</span></div></div>
                                @if ($tag->links->isNotEmpty())
                                    <div class="tag-performance-links">
                                        @foreach ($tag->links->take(3) as $link)
                                            <a href="{{ route('links.show', $link) }}"><span>{{ $link->title ?: '/'.$link->code }}</span><small>{{ number_format($link->visits_count) }} {{ __('visits') }}</small></a>
                                        @endforeach
                                        @if ($tag->links->count() > 3)<span class="tag-more">+{{ $tag->links->count() - 3 }} {{ __('more links') }}</span>@endif
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p>{{ __('No tags yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection

@push('head')
    <style>
        .campaign-layout{display:grid;grid-template-columns:minmax(0,1fr) 355px;gap:22px;align-items:start}.section-heading{display:flex;justify-content:space-between;align-items:center;margin:0 0 14px}.section-heading h2,.campaign-summary h3{margin:0;color:var(--text)}.section-heading p,.campaign-summary p{margin:5px 0 0;color:var(--muted);font-size:13px}.campaign-card{background:#fffdf8;border:1px solid var(--border);border-radius:16px;margin-bottom:16px;overflow:hidden}.campaign-summary{padding:20px 22px;display:flex;justify-content:space-between;gap:20px}.utm-chips{display:flex;flex-wrap:wrap;gap:7px;margin-top:14px}.utm-chips span{background:#edf5ec;color:#315e35;padding:5px 8px;border-radius:7px;font-size:12px}.utm-chips small{opacity:.7;margin-inline-end:5px}.campaign-stats{display:flex;gap:9px;align-self:center}.campaign-stats div{min-width:82px;text-align:center;background:#f4f7f4;border-radius:10px;padding:10px}.campaign-stats strong{display:block;font-size:21px;color:#072841}.campaign-stats span{display:block;font-size:11px;color:var(--muted);margin-top:2px}.campaign-links{border-top:1px solid var(--border)}.campaign-links-title{padding:10px 22px;background:#f8faf8;color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}.campaign-link-row{display:grid;grid-template-columns:minmax(180px,1fr) minmax(150px,1.3fr) 70px auto;gap:14px;align-items:center;padding:13px 22px;border-top:1px solid #edf0ed}.campaign-link-row:first-of-type{border-top:0}.campaign-link-main a{display:block;font-weight:700;color:#072841;text-decoration:none}.campaign-link-main span,.campaign-link-destination{display:block;color:var(--muted);font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.campaign-link-main span{margin-top:3px;color:#538F3F}.campaign-link-row>strong{font-size:14px;text-align:end}.campaign-link-row>strong small{display:block;color:var(--muted);font-weight:500;font-size:10px}.campaign-empty{padding:18px 22px;color:var(--muted);font-size:13px}.campaign-side{display:grid;gap:20px}.tags-card .form-footer{margin-top:4px}.tag-list{border-top:1px solid var(--border);margin-top:16px;padding-top:11px}.tag-list p{color:var(--muted);font-size:13px}.tag-performance{padding:11px 0;border-bottom:1px solid #edf0ed}.tag-performance:last-child{border-bottom:0}.tag-performance-head{display:flex;justify-content:space-between;gap:8px;align-items:center}.tag-performance-head strong{margin-inline-start:5px}.tag-performance-stats{display:flex;gap:8px;color:var(--muted);font-size:11px}.tag-performance-stats b{color:#072841}.tag-performance-links{margin-top:8px;padding-inline-start:12px;display:grid;gap:5px}.tag-performance-links a{display:flex;justify-content:space-between;gap:8px;color:#50685a;text-decoration:none;font-size:12px}.tag-performance-links a span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.tag-performance-links a small{color:var(--muted);white-space:nowrap}.tag-more{font-size:11px;color:#538F3F}@media(max-width:1050px){.campaign-layout{grid-template-columns:1fr}.campaign-side{grid-template-columns:1fr 1fr}}@media(max-width:680px){.campaign-summary,.section-heading{align-items:flex-start;flex-direction:column}.campaign-link-row{grid-template-columns:1fr auto}.campaign-link-destination{display:none}.campaign-link-row>.button{grid-column:2}.campaign-side{grid-template-columns:1fr}}
    </style>
@endpush
