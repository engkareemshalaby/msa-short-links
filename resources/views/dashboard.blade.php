@extends('layouts.app')
@section('title', __('Dashboard'))
@section('subtitle', __('A live overview of your short-link performance.'))
@section('content')
<div class="stats-grid">
    @foreach([
        [__('Total links'), $stats['links'], __('All saved links'), '↗'],
        [__('Active links'), $stats['active'], __('Currently redirecting'), '✓'],
        [__('Total visits'), number_format($stats['visits']), __('Bots excluded'), '⌁'],
        [__('Unique visitors'), number_format($stats['unique']), __('Based on anonymous fingerprint'), '◎'],
        [__('Visits today'), number_format($stats['today']), now()->format('M d, Y'), '◷'],
    ] as [$label,$value,$hint,$icon])
    <div class="stat-card"><div class="stat-label"><span>{{ $label }}</span><span class="stat-icon">{{ $icon }}</span></div><div class="stat-value">{{ $value }}</div><div class="stat-hint">{{ $hint }}</div></div>
    @endforeach
</div>
<div class="dashboard-grid">
    <div class="card"><div class="card-header"><div><h2>{{ __('Traffic trend') }}</h2><p>{{ __('Visits and unique visitors over the last 14 days') }}</p></div><a class="button small" href="{{ route('analytics.index') }}">{{ __('Full report') }} →</a></div><div class="card-body"><div class="chart-box"><canvas id="trafficChart"></canvas></div></div></div>
    <div class="card"><div class="card-header"><div><h2>{{ __('Device mix') }}</h2><p>{{ __('Where your audience opens links') }}</p></div></div><div class="card-body"><div class="chart-box" style="height:190px"><canvas id="deviceChart"></canvas></div><div class="mini-list">@forelse($devices as $device)<div class="mini-row"><span class="dot"></span><div class="grow"><strong>{{ __($device->device_type ?? 'Unknown') }}</strong></div><span class="metric">{{ number_format($device->total) }}</span></div>@empty<div class="empty-state">{{ __('No visit data yet.') }}</div>@endforelse</div></div></div>
</div>
<div class="dashboard-grid">
    <div class="card table-card"><div class="card-header"><div><h2>{{ __('Top performing links') }}</h2><p>{{ __('Ranked by genuine visits') }}</p></div><a class="button small" href="{{ route('links.index') }}">{{ __('View all') }}</a></div><div class="table-wrap"><table class="data-table"><thead><tr><th>{{ __('Link') }}</th><th>{{ __('Created by') }}</th><th>{{ __('Visits') }}</th><th></th></tr></thead><tbody>@forelse($topLinks as $link)<tr><td><span class="link-title">{{ $link->title ?: __('Untitled link') }}</span><span class="link-destination">{{ $link->destination_url }}</span></td><td>{{ $link->creator?->name }}</td><td><strong>{{ number_format($link->visits_count) }}</strong></td><td><a class="button small" href="{{ route('links.show',$link) }}">{{ __('View') }}</a></td></tr>@empty<tr><td colspan="4"><div class="empty-state"><strong>{{ __('No links yet') }}</strong>{{ __('Create your first short link to see performance here.') }}</div></td></tr>@endforelse</tbody></table></div></div>
    <div class="card"><div class="card-header"><div><h2>{{ __('Top referrers') }}</h2><p>{{ __('Sources sending the most traffic') }}</p></div></div><div class="card-body mini-list">@forelse($referrers as $referrer)<div class="mini-row"><div class="avatar">↗</div><div class="grow"><strong>{{ $referrer->referer_host }}</strong><small>{{ __('Traffic source') }}</small></div><span class="metric">{{ number_format($referrer->total) }}</span></div>@empty<div class="empty-state">{{ __('No referrer data yet.') }}</div>@endforelse</div></div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
<script>
const daily=@json($daily);const navy='#072841',navySoft='rgba(7,40,65,.10)',green='#538F3F';
new Chart(document.getElementById('trafficChart'),{type:'line',data:{labels:daily.map(x=>x.day),datasets:[{label:@json(__('Visits')),data:daily.map(x=>x.total),borderColor:navy,backgroundColor:navySoft,fill:true,tension:.38,pointRadius:2},{label:@json(__('Unique')),data:daily.map(x=>x.unique),borderColor:green,tension:.38,pointRadius:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:7,font:{size:10}}}},scales:{x:{grid:{display:false},ticks:{font:{size:9}}},y:{beginAtZero:true,ticks:{precision:0,font:{size:9}},grid:{color:'#e8eeeb'}}}}});
const devices=@json($devices);new Chart(document.getElementById('deviceChart'),{type:'doughnut',data:{labels:devices.map(x=>x.device_type),datasets:[{data:devices.map(x=>x.total),backgroundColor:['#072841','#538F3F','#7899A9','#8FB184','#C5D3C0'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,cutout:'70%',plugins:{legend:{display:false}}}});
</script>
@endpush
