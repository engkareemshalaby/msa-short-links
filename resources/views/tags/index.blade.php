@extends('layouts.app')

@section('title', __('Tags'))
@section('subtitle', __('Organize related links and review performance by tag.'))

@section('content')
    <div class="overview-grid">
        <div class="overview-card"><span>{{ __('Tags') }}</span><strong>{{ number_format($tags->count()) }}</strong><small>{{ __('Total tags') }}</small></div>
        <div class="overview-card"><span>{{ __('Tagged links') }}</span><strong>{{ number_format($totalLinks) }}</strong><small>{{ __('Links assigned across tags') }}</small></div>
        <div class="overview-card"><span>{{ __('Tagged visits') }}</span><strong>{{ number_format($totalVisits) }}</strong><small>{{ __('Human visits across tagged links') }}</small></div>
    </div>

    <div class="management-layout">
        <section>
            <div class="section-heading"><h2>{{ __('Tag performance') }}</h2><p>{{ __('Open any related link to see its detailed analytics.') }}</p></div>
            <div class="tags-grid">
                @forelse ($tags as $tag)
                    <article class="tag-card" style="--tag-color:{{ $tag->color }}">
                        <header><div><i></i><h3>{{ $tag->name }}</h3></div><div class="tag-stats"><span><b>{{ number_format($tag->links_count) }}</b>{{ __('Links') }}</span><span><b>{{ number_format($tag->total_visits) }}</b>{{ __('Visits') }}</span></div></header>
                        <div class="tag-links">
                            @forelse ($tag->links as $link)
                                <a href="{{ route('links.show', $link) }}"><span><b>{{ $link->title ?: __('Untitled link') }}</b><small>{{ $link->short_url }}</small></span><strong>{{ number_format($link->visits_count) }}<small>{{ __('visits') }}</small></strong><em>›</em></a>
                            @empty
                                <div class="empty-tag">{{ __('No links use this tag yet.') }}</div>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <div class="card"><div class="empty-state"><strong>{{ __('No tags yet.') }}</strong>{{ __('Create your first tag using the form.') }}</div></div>
                @endforelse
            </div>
        </section>

        <aside class="sticky-panel card">
            <div class="card-header"><div><h2>{{ __('Create tag') }}</h2><p>{{ __('Use a short name and a recognizable color.') }}</p></div></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tags.store') }}" class="form-grid">@csrf
                    <label class="field full"><span>{{ __('Tag name') }}</span><input name="name" value="{{ old('name') }}" required placeholder="Admissions"><small>{{ __('Used internally to group related links.') }}</small></label>
                    <label class="field full"><span>{{ __('Color') }}</span><div class="color-picker-control"><input id="tagColor" type="color" name="color" value="{{ old('color', '#072841') }}"><output id="tagColorValue" for="tagColor">{{ old('color', '#072841') }}</output></div><small>{{ __('Choose the color used to identify this tag.') }}</small></label>
                    <div class="form-footer"><button class="button primary">＋ {{ __('Create tag') }}</button></div>
                </form>
            </div>
        </aside>
    </div>
@endsection

@push('head')
    <style>
        .overview-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px}.overview-card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:17px 19px}.overview-card>span{display:block;color:var(--muted);font-size:11px;font-weight:700}.overview-card strong{display:block;color:#072841;font-size:27px;margin:8px 0 3px}.overview-card small{color:var(--muted);font-size:10px}.management-layout{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:20px;align-items:start}.section-heading{margin:2px 0 13px}.section-heading h2{font-size:16px;margin:0}.section-heading p{color:var(--muted);font-size:12px;margin:4px 0 0}.tags-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.tag-card{background:#fff;border:1px solid var(--line);border-top:3px solid var(--tag-color);border-radius:14px;overflow:hidden}.tag-card>header{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:15px 17px}.tag-card>header>div:first-child{display:flex;align-items:center;gap:8px}.tag-card h3{font-size:14px;margin:0}.tag-card header i{width:10px;height:10px;border-radius:50%;background:var(--tag-color)}.tag-stats{display:flex;gap:7px}.tag-stats span{min-width:58px;text-align:center;background:#f5f8f6;border-radius:8px;padding:6px;color:var(--muted);font-size:9px}.tag-stats b{display:block;color:#072841;font-size:15px}.tag-links{border-top:1px solid var(--line)}.tag-links>a{display:grid;grid-template-columns:minmax(0,1fr) 48px 10px;gap:9px;align-items:center;padding:11px 16px;border-bottom:1px solid #edf0ed}.tag-links>a:last-child{border-bottom:0}.tag-links>a:hover{background:#fafcf9}.tag-links>a>span{min-width:0}.tag-links b{display:block;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.tag-links small{display:block;color:var(--muted);font-size:9px;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.tag-links>a>strong{text-align:center;font-size:12px}.tag-links>a>strong small{font-weight:400}.tag-links em{font-size:18px;color:var(--tag-color);font-style:normal}.empty-tag{padding:16px;color:var(--muted);font-size:11px}.sticky-panel{position:sticky;top:112px}.sticky-panel .form-footer{grid-column:1/-1}.color-picker-control{display:flex;align-items:center;gap:10px;border:1px solid #dfe4eb;border-radius:10px;background:#fff;padding:6px}.color-picker-control input[type=color]{width:50px;height:35px;padding:0;border:0;border-radius:7px;cursor:pointer;background:transparent}.color-picker-control output{font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:12px;color:var(--muted)}@media(max-width:1100px){.management-layout{grid-template-columns:1fr}.sticky-panel{position:static}}@media(max-width:750px){.overview-grid,.tags-grid{grid-template-columns:1fr}}
    </style>
@endpush

@push('scripts')
    <script>
        const tagColor = document.getElementById('tagColor');
        const tagColorValue = document.getElementById('tagColorValue');
        if (tagColor && tagColorValue) tagColor.addEventListener('input', () => tagColorValue.value = tagColor.value.toUpperCase());
    </script>
@endpush
