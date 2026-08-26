<div class="form-grid">
    <label class="field">
        <span>{{ __('Internal title') }}</span>
        <input name="title" value="{{ old('title', $link?->title) }}" placeholder="{{ __('e.g. Fall admission campaign') }}">
        <small>{{ __('Only visible to dashboard users.') }}</small>
    </label>

    <label class="field">
        <span>{{ __('Expiration date') }}</span>
        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $link?->expires_at?->format('Y-m-d\TH:i')) }}">
        <small>{{ __('Optional. Leave empty for no expiration.') }}</small>
    </label>

    <label class="field">
        <span>{{ __('Visit limit') }}</span>
        <input type="number" name="max_visits" min="1" value="{{ old('max_visits', $link?->max_visits) }}" placeholder="{{ __('Unlimited') }}">
        <small>{{ __('Optional. The link stops redirecting after this number of human visits.') }}</small>
    </label>

    <label class="field full">
        <span>{{ __('Destination URL') }}</span>
        <textarea name="destination_url" required placeholder="https://www.msa.edu.eg/...">{{ old('destination_url', $link?->destination_url) }}</textarea>
    </label>

    <label class="field">
        <span>{{ __('Campaign') }}</span>
        <select name="campaign_id"><option value="">{{ __('No campaign') }}</option>@foreach ($campaigns as $campaign)<option value="{{ $campaign->id }}" @selected(old('campaign_id', $link?->campaign_id) == $campaign->id)>{{ $campaign->name }}</option>@endforeach</select>
        <small>{{ __('Campaign UTM values can be used when creating links.') }}</small>
    </label>

    <label class="field">
        <span>{{ __('Tags') }}</span>
        <select name="tag_ids[]" multiple size="3">@foreach ($tags as $tag)<option value="{{ $tag->id }}" @selected(in_array($tag->id, old('tag_ids', $link?->tags->pluck('id')->all() ?? [])))>{{ $tag->name }}</option>@endforeach</select>
        <small>{{ __('Use Ctrl / Cmd to select more than one.') }}</small>
    </label>

    @if (! $link)
        <div class="field full">
            <span>{{ __('Short code type') }}</span>
            <div class="radio-cards">
                <label class="radio-card">
                    <input type="radio" name="code_type" value="random" {{ old('code_type', 'random') === 'random' ? 'checked' : '' }} onchange="toggleCustom()">
                    <span><strong>{{ __('Random 6-digit code') }}</strong><small>{{ __('Example: 482913. Generated securely and guaranteed unique.') }}</small></span>
                </label>
                <label class="radio-card">
                    <input type="radio" name="code_type" value="custom" {{ old('code_type') === 'custom' ? 'checked' : '' }} onchange="toggleCustom()">
                    <span><strong>{{ __('Custom slug') }}</strong><small>{{ __('Example: apply-now or open-day.') }}</small></span>
                </label>
            </div>
        </div>

        <label class="field full" id="customCode">
            <span>{{ __('Custom slug') }}</span>
            <input name="custom_code" value="{{ old('custom_code') }}" placeholder="apply-now" pattern="[A-Za-z0-9][A-Za-z0-9_-]{1,49}">
            <small>{{ __('Use letters, numbers, hyphens or underscores. At least 2 characters.') }}</small>
        </label>
    @else
        <label class="field full">
            <span>{{ __('Short code / slug') }}</span>
            <input name="code" value="{{ old('code', $link->code) }}" required pattern="[A-Za-z0-9][A-Za-z0-9_-]{1,49}">
        </label>
    @endif

    <label class="checkbox field full">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $link?->is_active ?? true) ? 'checked' : '' }}>
        <span>{{ __('Active and ready to redirect') }}</span>
    </label>

    <label class="field">
        <span>{{ __('Password protection') }}</span>
        <input type="password" name="access_password" autocomplete="new-password" placeholder="{{ $link?->access_password ? __('Leave blank to keep current password') : __('Optional password (6+ characters)') }}">
        <small>{{ $link?->access_password ? __('Leave both fields blank to keep the current password.') : __('Visitors must enter this before opening the destination.') }}</small>
    </label>

    <label class="field">
        <span>{{ __('Confirm password') }}</span>
        <input type="password" name="access_password_confirmation" autocomplete="new-password" placeholder="{{ __('Repeat password') }}">
    </label>

    @if ($pixels->isNotEmpty())
        <label class="checkbox field full">
            <input type="checkbox" name="retargeting_enabled" value="1" {{ old('retargeting_enabled', $link?->retargeting_enabled ?? false) ? 'checked' : '' }}>
            <span>{{ __('Enable approved retargeting pixels for this link') }}</span>
        </label>
        <label class="field full">
            <span>{{ __('Retargeting pixels') }}</span>
            <select name="pixel_ids[]" multiple size="3">@foreach ($pixels as $pixel)<option value="{{ $pixel->id }}" @selected(in_array($pixel->id, old('pixel_ids', $link?->pixels->pluck('id')->all() ?? [])))>{{ $pixel->name }}{{ $pixel->provider ? ' · '.$pixel->provider : '' }}</option>@endforeach</select>
            <small>{{ __('Only use pixels with a lawful notice and consent where required.') }}</small>
        </label>
    @endif
</div>

<div class="form-footer">
    <a class="button" href="{{ $link ? route('links.show', $link) : route('links.index') }}">{{ __('Cancel') }}</a>
    <button class="button primary" type="submit">{{ $link ? __('Save changes') : __('Create short link') }}</button>
</div>

@if (! $link)
    @push('scripts')
        <script>
            function toggleCustom() {
                document.getElementById('customCode').style.display = document.querySelector('[name=code_type]:checked').value === 'custom' ? 'block' : 'none';
            }

            toggleCustom();
        </script>
    @endpush
@endif
