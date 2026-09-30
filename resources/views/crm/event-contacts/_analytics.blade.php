<section class="contact-overview" aria-label="{{ __('Contact overview') }}">
    <div class="contact-stat"><span>{{ __('Contacts') }}</span><strong>{{ number_format($contactStats['total']) }}</strong><small>{{ __('Matching current filters') }}</small></div>
    <div class="contact-stat"><span>{{ __('Exhibitions') }}</span><strong>{{ number_format($contactStats['exhibitions']) }}</strong><small>{{ __('Represented in results') }}</small></div>
    <div class="contact-stat"><span>{{ __('Tagged contacts') }}</span><strong>{{ number_format($contactStats['tagged']) }}</strong><small>{{ __('Contacts with at least one tag') }}</small></div>
    <div class="contact-stat"><span>{{ __('Tags') }}</span><strong>{{ number_format($contactStats['tags']) }}</strong><small>{{ __('Used in current results') }}</small></div>
</section>

@pushOnce('head')
<style>
.contact-overview{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:16px}.contact-stat{background:#fff;border:1px solid var(--line);border-radius:12px;padding:13px 15px}.contact-stat span,.contact-stat small{display:block;color:var(--muted);font-size:9px}.contact-stat span{font-weight:700;text-transform:uppercase;letter-spacing:.05em}.contact-stat strong{display:block;font-size:22px;line-height:1.15;margin:6px 0 3px;color:var(--text)}.contact-charts{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px}.contact-chart-card .card-header{padding:14px 17px}.contact-chart-card .card-header h2{font-size:13px}.contact-chart-card .card-header p{font-size:9px}.contact-chart-card .card-body{padding:14px 17px}.contact-chart-box{height:230px}@media(max-width:800px){.contact-overview{grid-template-columns:1fr 1fr}.contact-charts{grid-template-columns:1fr}}@media(max-width:480px){.contact-overview{grid-template-columns:1fr}}
</style>
@endPushOnce

@pushOnce('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
<script>
const eventContactCharts={exhibitions:@json($exhibitionChart),tags:@json($tagChart)};
const eventContactChartOptions={responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{precision:0,font:{size:9}},grid:{color:'#e8eeeb'}},y:{grid:{display:false},ticks:{font:{size:9}}}}};
const barValueLabels={id:'barValueLabels',afterDatasetsDraw(chart){const {ctx}=chart;chart.data.datasets.forEach((dataset,datasetIndex)=>{const meta=chart.getDatasetMeta(datasetIndex);meta.data.forEach((bar,index)=>{const value=dataset.data[index];const background=Array.isArray(dataset.backgroundColor)?dataset.backgroundColor[index]:dataset.backgroundColor;const hex=typeof background==='string'&&/^#[0-9a-f]{6}$/i.test(background)?background:'#072841';const rgb=[1,3,5].map(position=>parseInt(hex.slice(position,position+2),16));const light=(rgb[0]*299+rgb[1]*587+rgb[2]*114)/1000>165;ctx.save();ctx.fillStyle=light?'#072841':'#fff';ctx.font='700 11px Inter, Tajawal, sans-serif';ctx.textAlign='right';ctx.textBaseline='middle';ctx.shadowColor=light?'rgba(255,255,255,.45)':'rgba(0,0,0,.22)';ctx.shadowBlur=2;ctx.fillText(Number(value).toLocaleString(),bar.x-8,bar.y);ctx.restore();});});}};
new Chart(document.getElementById('exhibitionContactsChart'),{type:'bar',data:{labels:eventContactCharts.exhibitions.map(row=>row.event_name),datasets:[{data:eventContactCharts.exhibitions.map(row=>row.total),backgroundColor:'#072841',borderRadius:5}]},options:eventContactChartOptions,plugins:[barValueLabels]});
new Chart(document.getElementById('tagContactsChart'),{type:'bar',data:{labels:eventContactCharts.tags.map(row=>row.name),datasets:[{data:eventContactCharts.tags.map(row=>row.total),backgroundColor:eventContactCharts.tags.map(row=>row.color),borderRadius:5}]},options:eventContactChartOptions,plugins:[barValueLabels]});
</script>
@endPushOnce
