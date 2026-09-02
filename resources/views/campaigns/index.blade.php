@extends('layouts.app')

@section('title', __('Campaigns'))
@section('subtitle', __('Group related short links and measure campaign performance.'))

@section('content')
    <div class="overview-grid">
        <div class="overview-card"><span>{{ __('Campaigns') }}</span><strong>{{ number_format($campaigns->count()) }}</strong><small>{{ __('Total campaigns') }}</small></div>
        <div class="overview-card"><span>{{ __('Campaign links') }}</span><strong>{{ number_format($totalLinks) }}</strong><small>{{ __('Links assigned to campaigns') }}</small></div>
        <div class="overview-card"><span>{{ __('Campaign visits') }}</span><strong>{{ number_format($totalVisits) }}</strong><small>{{ __('Human visits across campaign links') }}</small></div>
    </div>

    <div class="management-layout">
        <section>
            <div class="section-heading">
                <div><h2>{{ __('Campaign performance') }}</h2><p>{{ __('Review every campaign and the performance of its links.') }}</p></div>
            </div>

            @forelse ($campaigns as $campaign)
                <article class="performance-card">
                    <header class="performance-header">
                        <div class="campaign-identity">
                            <span class="campaign-mark">◇</span>
                            <div><h3>{{ $campaign->name }}</h3><p>{{ $campaign->description ?: __('No description added.') }}</p></div>
                        </div>
                        <div class="performance-stats">
                            <span><b>{{ number_format($campaign->links_count) }}</b>{{ __('Links') }}</span>
                            <span><b>{{ number_format($campaign->total_visits) }}</b>{{ __('Visits') }}</span>
                        </div>
                    </header>

                    @if ($campaign->utm_source || $campaign->utm_medium || $campaign->utm_campaign)
                        <div class="utm-row">
                            @foreach (['utm_source' => 'Source', 'utm_medium' => 'Medium', 'utm_campaign' => 'Campaign'] as $field => $label)
                                @if ($campaign->{$field})<span><small>{{ $label }}</small>{{ $campaign->{$field} }}</span>@endif
                            @endforeach
                        </div>
                    @endif

                    <div class="links-table">
                        <div class="links-table-head"><span>{{ __('Link') }}</span><span>{{ __('Domain URL') }}</span><span>{{ __('Visits') }}</span><span></span></div>
                        @forelse ($campaign->links as $link)
                            <div class="links-table-row">
                                <div><a href="{{ route('links.show', $link) }}">{{ $link->title ?: __('Untitled link') }}</a><small>{{ $link->short_url }}</small></div>
                                <span class="destination">{{ $link->destination_url }}</span>
                                <strong>{{ number_format($link->visits_count) }}</strong>
                                <a class="button small" href="{{ route('links.show', $link) }}">{{ __('View') }}</a>
                            </div>
                        @empty
                            <div class="empty-row">{{ __('No links have been assigned to this campaign yet.') }}</div>
                        @endforelse
                    </div>
                </article>
            @empty
                <div class="card"><div class="empty-state"><strong>{{ __('No campaigns yet.') }}</strong>{{ __('Create your first campaign using the form.') }}</div></div>
            @endforelse
        </section>

        <aside class="sticky-panel card">
            <div class="card-header"><div><h2>{{ __('Create campaign') }}</h2><p>{{ __('UTM values are optional and will be added to assigned links.') }}</p></div></div>
            <div class="card-body">
                <form method="POST" action="{{ route('campaigns.store') }}" class="form-grid">@csrf
                    <label class="field full"><span>{{ __('Campaign name') }}</span><input name="name" value="{{ old('name') }}" required placeholder="Admissions 2026"><small>{{ __('A clear internal name for the campaign.') }}</small></label>
                    <label class="field"><span>UTM source</span><input name="utm_source" value="{{ old('utm_source') }}" placeholder="facebook"><small>{{ __('Traffic platform or source.') }}</small></label>
                    <label class="field"><span>UTM medium</span><input name="utm_medium" value="{{ old('utm_medium') }}" placeholder="social"><small>{{ __('Marketing channel type.') }}</small></label>
                    <label class="field full"><span>UTM campaign</span><input name="utm_campaign" value="{{ old('utm_campaign') }}" placeholder="admissions-2026"><small>{{ __('Tracking name added to the URL.') }}</small></label>
                    <label class="field full"><span>Full tracking URL (optional)</span><textarea name="tracking_url" id="trackingUrl" placeholder="https://example.com/page?utm_source=facebook&ref=website">{{ old('tracking_url') }}</textarea><small>Paste a complete URL and the tracking fields will be filled automatically.</small></label>
                    <label class="field"><span>UTM content</span><input name="utm_content" value="{{ old('utm_content') }}" placeholder="organic"></label>
                    <label class="field"><span>ref</span><input name="ref" value="{{ old('ref') }}" placeholder="website"></label>
                    <label class="field"><span>bref</span><input name="bref" value="{{ old('bref') }}" placeholder="web-topmenu"></label>
                    <label class="field"><span>sem</span><input name="sem" value="{{ old('sem') }}" placeholder="91"></label>
                    <label class="field full"><span>{{ __('Description') }}</span><textarea name="description" placeholder="{{ __('Optional internal description') }}">{{ old('description') }}</textarea></label>
                    <div class="form-footer"><button class="button primary">＋ {{ __('Create campaign') }}</button></div>
                </form>
            </div>
        </aside>
    </div>
@endsection

@push('head')
    <style>
        .overview-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px}.overview-card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:17px 19px}.overview-card>span{display:block;color:var(--muted);font-size:11px;font-weight:700}.overview-card strong{display:block;color:#072841;font-size:27px;margin:8px 0 3px}.overview-card small{color:var(--muted);font-size:10px}.management-layout{display:grid;grid-template-columns:minmax(0,1fr) 355px;gap:20px;align-items:start}.section-heading{margin:2px 0 13px}.section-heading h2{font-size:16px;margin:0}.section-heading p{color:var(--muted);font-size:12px;margin:4px 0 0}.performance-card{background:#fff;border:1px solid var(--line);border-radius:15px;overflow:hidden;margin-bottom:15px}.performance-header{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 20px}.campaign-identity{display:flex;align-items:center;gap:12px;min-width:0}.campaign-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:10px;background:#eaf0f3;color:#072841;font-size:20px}.campaign-identity h3{font-size:15px;margin:0}.campaign-identity p{color:var(--muted);font-size:11px;margin:4px 0 0}.performance-stats{display:flex;gap:8px}.performance-stats span{min-width:73px;text-align:center;padding:8px 10px;background:#f5f8f6;border-radius:9px;color:var(--muted);font-size:10px}.performance-stats b{display:block;color:#072841;font-size:18px;margin-bottom:2px}.utm-row{display:flex;gap:7px;flex-wrap:wrap;padding:0 20px 14px}.utm-row span{background:#edf5ea;color:#3f7032;border-radius:7px;padding:6px 8px;font-size:11px}.utm-row small{opacity:.7;margin-inline-end:5px}.links-table{border-top:1px solid var(--line)}.links-table-head,.links-table-row{display:grid;grid-template-columns:minmax(160px,1fr) minmax(180px,1.25fr) 60px 55px;gap:12px;align-items:center}.links-table-head{padding:9px 20px;background:#f8faf8;color:var(--muted);font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.05em}.links-table-row{padding:12px 20px;border-top:1px solid #edf0ed}.links-table-row:first-of-type{border-top:0}.links-table-row>div{min-width:0}.links-table-row a:not(.button){font-size:12px;font-weight:700}.links-table-row small{display:block;color:#538f3f;font-size:10px;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.links-table-row .destination{color:var(--muted);font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.links-table-row>strong{text-align:center;font-size:13px}.empty-row{padding:17px 20px;color:var(--muted);font-size:12px}.sticky-panel{position:sticky;top:112px}.sticky-panel .form-grid{gap:14px}.sticky-panel .form-footer{grid-column:1/-1}@media(max-width:1050px){.management-layout{grid-template-columns:1fr}.sticky-panel{position:static}.sticky-panel .form-grid{grid-template-columns:1fr 1fr}}@media(max-width:700px){.overview-grid{grid-template-columns:1fr}.performance-header{align-items:flex-start;flex-direction:column}.links-table-head{display:none}.links-table-row{grid-template-columns:1fr auto}.links-table-row .destination{display:none}.sticky-panel .form-grid{grid-template-columns:1fr}}
    </style>
@endpush
@push('scripts')
<script>document.getElementById('trackingUrl')?.addEventListener('input',function(){try{const p=new URL(this.value).searchParams;['utm_source','utm_medium','utm_campaign','utm_content','ref','bref','sem'].forEach(k=>{const e=document.querySelector('[name="'+k+'"]');if(e&&p.has(k)&&!e.value)e.value=p.get(k)})}catch(e){}});</script>
@endpush
