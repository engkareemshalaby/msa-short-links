@extends('layouts.app')
@section('title', __('Jordan exhibition analytics'))
@section('subtitle', __('Understand registration volume, student interests, and admissions progress.'))
@section('content')
<div class="card analytics-filter"><div class="card-body"><form class="filter-row"><label class="field"><span>{{ __('Date range') }}</span><select name="days"><option value="7" @selected($days===7)>{{ __('Last 7 days') }}</option><option value="30" @selected($days===30)>{{ __('Last 30 days') }}</option><option value="90" @selected($days===90)>{{ __('Last 90 days') }}</option><option value="365" @selected($days===365)>{{ __('Last 12 months') }}</option></select></label><button class="button primary">{{ __('Apply filters') }}</button><a class="button" href="{{ route('crm.exhibition.index') }}">← {{ __('Back to registrations') }}</a></form></div></div>

<div class="stats-grid exhibition-stats">
    <div class="stat-card"><div class="stat-label"><span>{{ __('Total registrations') }}</span><span class="stat-icon">◎</span></div><div class="stat-value">{{ number_format($total) }}</div><div class="stat-hint">{{ trans_choice('Across :count days', $days, ['count'=>$days]) }}</div></div>
    <div class="stat-card"><div class="stat-label"><span>{{ __('New') }}</span><span class="stat-icon">＋</span></div><div class="stat-value">{{ number_format($newCount) }}</div><div class="stat-hint">{{ __('Awaiting first contact') }}</div></div>
    <div class="stat-card"><div class="stat-label"><span>{{ __('Contacted') }}</span><span class="stat-icon">✓</span></div><div class="stat-value">{{ number_format($contactedCount) }}</div><div class="stat-hint">{{ __('Admissions follow-up started') }}</div></div>
    <div class="stat-card"><div class="stat-label"><span>{{ __('Applied') }}</span><span class="stat-icon">↗</span></div><div class="stat-value">{{ number_format($appliedCount) }}</div><div class="stat-hint">{{ __('Application submitted') }}</div></div>
</div>

<div class="card trend-card"><div class="card-header"><div><h2>{{ __('Registrations over time') }}</h2><p>{{ __('Daily registrations during the selected period.') }}</p></div></div><div class="card-body"><div class="chart-box"><canvas id="registrationTrend"></canvas></div></div></div>

<div class="chart-grid">
    <div class="card"><div class="card-header"><div><h2>{{ __('Admissions status') }}</h2><p>{{ __('Current progress of registered students.') }}</p></div></div><div class="card-body"><div class="small-chart"><canvas id="statusChart"></canvas></div></div></div>
    <div class="card"><div class="card-header"><div><h2>{{ __('Top interested faculties') }}</h2><p>{{ __('Most selected faculties across all registrations.') }}</p></div></div><div class="card-body"><div class="bar-chart"><canvas id="facultyChart"></canvas></div></div></div>
    <div class="card"><div class="card-header"><div><h2>{{ __('Certificate distribution') }}</h2><p>{{ __('Educational systems represented by students.') }}</p></div></div><div class="card-body"><div class="bar-chart"><canvas id="certificateChart"></canvas></div></div></div>
    <div class="card"><div class="card-header"><div><h2>{{ __('Registrant & contact preferences') }}</h2><p>{{ __('Who registered and how they prefer to be reached.') }}</p></div></div><div class="card-body split-charts"><div><h3>{{ __('Registrant') }}</h3><div class="micro-chart"><canvas id="roleChart"></canvas></div></div><div><h3>{{ __('Contact method') }}</h3><div class="micro-chart"><canvas id="contactChart"></canvas></div></div></div></div>
</div>
@endsection
@push('head')<style>.analytics-filter{margin-bottom:18px}.exhibition-stats{grid-template-columns:repeat(4,1fr)}.trend-card{margin-bottom:20px}.chart-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}.small-chart{height:300px}.bar-chart{height:320px}.split-charts{display:grid;grid-template-columns:1fr 1fr;gap:18px}.split-charts h3{text-align:center;font-size:11px;color:#68777f;margin:0 0 10px}.micro-chart{height:250px}@media(max-width:1000px){.exhibition-stats{grid-template-columns:1fr 1fr}.chart-grid{grid-template-columns:1fr}}@media(max-width:600px){.exhibition-stats{grid-template-columns:1fr}.split-charts{grid-template-columns:1fr}.filter-row{align-items:stretch}.filter-row .field,.filter-row .button{width:100%}}</style>@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
<script>
const palette=['#072841','#538F3F','#7899A9','#8FB184','#D5A64A','#9A6FB0','#D17767','#4C8C91','#B8C99F','#526B7A','#AAC8D5'];
const common={responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:7,font:{size:10}}}}};
const daily=@json($daily);new Chart(document.getElementById('registrationTrend'),{type:'line',data:{labels:daily.map(x=>x.day),datasets:[{label:@json(__('Registrations')),data:daily.map(x=>x.total),borderColor:'#072841',backgroundColor:'rgba(83,143,63,.13)',fill:true,tension:.35,pointRadius:2}]},options:{...common,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{maxTicksLimit:15,font:{size:9}}},y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#e8eeeb'}}}}});
const statuses=@json($statuses);new Chart(document.getElementById('statusChart'),{type:'doughnut',data:{labels:statuses.map(x=>x.status),datasets:[{data:statuses.map(x=>x.total),backgroundColor:palette,borderWidth:0}]},options:{...common,cutout:'67%'}});
const faculties=@json($faculties);new Chart(document.getElementById('facultyChart'),{type:'bar',data:{labels:Object.keys(faculties),datasets:[{data:Object.values(faculties),backgroundColor:'#538F3F',borderRadius:6}]},options:{...common,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{precision:0}},y:{grid:{display:false},ticks:{font:{size:9}}}}}});
const certificates=@json($certificates);new Chart(document.getElementById('certificateChart'),{type:'bar',data:{labels:certificates.map(x=>x.certificate_type),datasets:[{data:certificates.map(x=>x.total),backgroundColor:'#072841',borderRadius:6}]},options:{...common,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{font:{size:9},maxRotation:35}},y:{beginAtZero:true,ticks:{precision:0}}}}});
function doughnut(id,rows,key){new Chart(document.getElementById(id),{type:'doughnut',data:{labels:rows.map(x=>x[key]),datasets:[{data:rows.map(x=>x.total),backgroundColor:palette,borderWidth:0}]},options:{...common,cutout:'62%'}})}doughnut('roleChart',@json($roles),'registrant_role');doughnut('contactChart',@json($contacts),'preferred_contact_method');
</script>
@endpush
