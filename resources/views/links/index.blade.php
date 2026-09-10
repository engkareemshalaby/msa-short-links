@extends('layouts.app')
@section('title', __('Short Links'))
@section('subtitle', __('Create, organize and monitor every branded link.'))
@section('content')
<div class="card links-filter-card">
    <div class="card-body">
        <form class="filter-row links-filters" method="GET">
            <label class="field search-field"><span>{{ __('Search') }}</span><input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by title, code or destination...') }}"></label>
            <label class="field"><span>{{ __('Tag') }}</span><select name="tag_id"><option value="">{{ __('All tags') }}</option>@foreach($tags as $tag)<option value="{{ $tag->id }}" @selected((string)request('tag_id') === (string)$tag->id)>{{ $tag->name }}</option>@endforeach</select></label>
            <label class="field"><span>{{ __('Campaign') }}</span><select name="campaign_id"><option value="">{{ __('All campaigns') }}</option>@foreach($campaigns as $campaign)<option value="{{ $campaign->id }}" @selected((string)request('campaign_id') === (string)$campaign->id)>{{ $campaign->name }}</option>@endforeach</select></label>
            <label class="field"><span>{{ __('Created by') }}</span><select name="created_by"><option value="">{{ __('All creators') }}</option>@foreach($creators as $creator)<option value="{{ $creator->id }}" @selected((string)request('created_by') === (string)$creator->id)>{{ $creator->name }}</option>@endforeach</select></label>
            <label class="field"><span>{{ __('Status') }}</span><select name="status"><option value="">{{ __('All statuses') }}</option><option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option><option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option><option value="expired" @selected(request('status') === 'expired')>{{ __('Expired') }}</option></select></label>
            <label class="field"><span>{{ __('Archive') }}</span><select name="archive"><option value="current" @selected(request('archive', 'current') === 'current')>{{ __('Current links') }}</option><option value="archived" @selected(request('archive') === 'archived')>{{ __('Archived links') }}</option><option value="all" @selected(request('archive') === 'all')>{{ __('All links') }}</option></select></label>
            <label class="field date-field"><span>{{ __('Created from') }}</span><input type="date" name="created_from" value="{{ request('created_from') }}"></label>
            <label class="field date-field"><span>{{ __('Created to') }}</span><input type="date" name="created_to" value="{{ request('created_to') }}"></label>
            <div class="filter-actions"><button class="button primary" type="submit">{{ __('Apply filters') }}</button>@if(request()->hasAny(['search','tag_id','campaign_id','created_by','status','archive','created_from','created_to']))<a class="button ghost" href="{{ route('links.index') }}">{{ __('Clear') }}</a>@endif</div>
        </form>
    </div>
</div>
<div class="toolbar links-count"><span class="badge muted">{{ trans_choice(':count links', $links->total(), ['count'=>$links->total()]) }}</span></div>
<div class="card table-card"><div class="table-wrap"><table class="data-table"><thead><tr><th>{{ __('Link') }}</th><th>{{ __('Short URL') }}</th><th>{{ __('Type') }}</th><th>{{ __('Status') }}</th><th>{{ __('Created by') }}</th><th>{{ __('Visits') }}</th><th></th></tr></thead><tbody>
@forelse($links as $link)<tr @class(['archived-row' => $link->trashed()])><td>@if($link->trashed())<span class="link-title">{{ $link->title ?: __('Untitled link') }}</span>@else<a class="link-title" href="{{ route('links.show',$link) }}">{{ $link->title ?: __('Untitled link') }}</a>@endif<span class="link-destination">{{ $link->destination_url }}</span></td><td><span class="short-url">{{ $link->short_url }}</span></td><td><span class="badge purple">{{ __($link->code_type === 'random' ? '6-digit code' : 'Custom slug') }}</span></td><td>@if($link->trashed())<span class="badge muted">{{ __('Archived') }}</span>@elseif(!$link->is_active)<span class="badge muted">{{ __('Inactive') }}</span>@elseif($link->expires_at?->isPast())<span class="badge warning">{{ __('Expired') }}</span>@else<span class="badge success">{{ __('Active') }}</span>@endif</td><td>{{ $link->creator?->name }}<span class="link-destination">{{ $link->created_at->format('M d, Y') }}</span></td><td><strong>{{ number_format($link->visits_count) }}</strong></td><td><div class="actions">@if($link->trashed())@can('links.delete')<form method="POST" action="{{ route('links.restore', $link->id) }}">@csrf @method('PATCH')<button class="button small primary" type="submit">{{ __('Restore') }}</button></form>@endcan @else<button class="button small" type="button" data-copy="{{ $link->short_url }}">{{ __('Copy') }}</button><a class="button small" href="{{ route('links.show',$link) }}">{{ __('View') }}</a>@can('links.update')<a class="button small" href="{{ route('links.edit',$link) }}">{{ __('Edit') }}</a>@endcan @endif</div></td></tr>
@empty<tr><td colspan="7"><div class="empty-state"><strong>{{ __('No links found') }}</strong>{{ __('Create your first short link or adjust your filters.') }}</div></td></tr>@endforelse
</tbody></table></div>{{ $links->onEachSide(1)->links('pagination.msa') }}</div>
@endsection
@push('head')
<style>
.links-filter-card{margin-bottom:14px}.links-filters .search-field{min-width:260px;flex:2}.links-filters .field{flex:1;min-width:145px}.links-filters .date-field{min-width:155px}.filter-actions{display:flex;gap:8px;align-items:center}.links-count{justify-content:flex-end;margin-bottom:10px}.archived-row{background:#fafafa}.archived-row .short-url,.archived-row .link-title{color:#7b8492}@media(max-width:760px){.links-filters .field{width:100%;min-width:0}.filter-actions{width:100%}.filter-actions .button{flex:1}}
</style>
@endpush
