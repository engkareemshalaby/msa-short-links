@php
    $selectedTagIds = array_map('intval', old('tag_ids', $link ? $link->tags->pluck('id')->all() : []));
    $advancedHasErrors = $errors->has('expires_at') || $errors->has('max_visits') || $errors->has('campaign_id')
        || $errors->has('new_campaign_name') || $errors->has('access_password') || $errors->has('pixel_ids');
@endphp

<div class="form-grid link-primary-fields">
    <label class="field full">
        <span>{{ __('Link name') }}</span>
        <input name="title" value="{{ old('title', $link?->title) }}" placeholder="{{ __('e.g. Fall admission campaign') }}">
        <small>{{ __('A clear internal name that helps your team identify this link. It is not shown to visitors.') }}</small>
    </label>

    <label class="field full">
        <span>{{ __('Domain URL') }}</span>
        <textarea name="destination_url" required placeholder="https://www.msa.edu.eg/...">{{ old('destination_url', $link?->destination_url) }}</textarea>
        <small>{{ __('Paste the complete page URL visitors should be redirected to, including https://.') }}</small>
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
            <small>{{ __('Choose an automatic 6-digit code or enter a memorable custom slug.') }}</small>
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

    <div class="field full tag-picker">
        <span>{{ __('Tags') }}</span>
        @if ($tags->isNotEmpty())
            <div class="tag-options">
                @foreach ($tags as $tag)
                    <label class="tag-option">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTagIds, true))>
                        <i style="background:{{ $tag->color }}"></i><span>{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>
        @else
            <small>{{ __('No tags have been created yet.') }}</small>
        @endif
        <small>{{ __('Select one or more tags to organize and filter related links.') }}</small>

        @if (! $link)
            <details class="inline-create" @if(old('new_tags')) open @endif>
                <summary>＋ {{ __('Create new tags') }}</summary>
                <div class="inline-create-body">
                    <label class="field"><span>{{ __('Tag names') }}</span><input name="new_tags" value="{{ old('new_tags') }}" placeholder="Admissions, Facebook"></label>
                    <label class="field color-field"><span>{{ __('Color') }}</span><input type="color" name="new_tag_color" value="{{ old('new_tag_color', '#072841') }}"></label>
                    <small class="full">{{ __('Separate multiple tags with commas. They will be created and selected automatically.') }}</small>
                </div>
            </details>
        @endif
    </div>
</div>

<details class="advanced-options" @if($link || $advancedHasErrors) open @endif>
    <summary>
        <span><strong>{{ __('More options') }}</strong><small>{{ __('Expiration, visit limit, campaign, password and status') }}</small></span>
        <b aria-hidden="true">⌄</b>
    </summary>

    <div class="form-grid advanced-options-body">
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

        <div class="field full">
            <span>{{ __('Campaign') }}</span>
            <select name="campaign_id">
                <option value="">{{ __('No campaign') }}</option>
                @foreach ($campaigns as $campaign)
                    <option value="{{ $campaign->id }}" @selected(old('campaign_id', $link?->campaign_id) == $campaign->id)>{{ $campaign->name }}</option>
                @endforeach
            </select>
            <small>{{ __('Campaign UTM values are added automatically to the destination URL.') }}</small>

            @if (! $link)
                <details class="inline-create campaign-inline-create" @if(old('new_campaign_name')) open @endif>
                    <summary>＋ {{ __('Create a new campaign') }}</summary>
                    <div class="inline-create-body">
                        <label class="field full"><span>{{ __('Campaign name') }}</span><input name="new_campaign_name" value="{{ old('new_campaign_name') }}" placeholder="Admissions 2026"></label>
                        <label class="field"><span>UTM source</span><input name="new_campaign_utm_source" value="{{ old('new_campaign_utm_source') }}" placeholder="facebook"></label>
                        <label class="field"><span>UTM medium</span><input name="new_campaign_utm_medium" value="{{ old('new_campaign_utm_medium') }}" placeholder="social"></label>
                        <label class="field full"><span>UTM campaign</span><input name="new_campaign_utm_campaign" value="{{ old('new_campaign_utm_campaign') }}" placeholder="admissions-2026"></label>
                        <label class="field full"><span>Full tracking URL (optional)</span><textarea name="new_campaign_tracking_url" id="newCampaignTrackingUrl" placeholder="Paste the complete URL with tracking parameters">{{ old('new_campaign_tracking_url') }}</textarea><small>Paste a complete URL and the campaign fields will be filled automatically.</small></label>
                        <label class="field"><span>UTM content</span><input name="new_campaign_utm_content" value="{{ old('new_campaign_utm_content') }}" placeholder="organic"></label>
                        <label class="field"><span>ref</span><input name="new_campaign_ref" value="{{ old('new_campaign_ref') }}" placeholder="website"></label>
                        <label class="field"><span>bref</span><input name="new_campaign_bref" value="{{ old('new_campaign_bref') }}" placeholder="web-topmenu"></label>
                        <label class="field"><span>sem</span><input name="new_campaign_sem" value="{{ old('new_campaign_sem') }}" placeholder="91"></label>
                        <label class="field full"><span>{{ __('Description') }}</span><textarea name="new_campaign_description">{{ old('new_campaign_description') }}</textarea></label>
                        <small class="full">{{ __('If you enter a new campaign, it will be created and assigned to this link.') }}</small>
                    </div>
                </details>
            @endif
        </div>

        <label class="field">
            <span>{{ __('Password protection') }}</span>
            <input type="password" name="access_password" autocomplete="new-password" placeholder="{{ $link?->access_password ? __('Leave blank to keep current password') : __('Optional password (6+ characters)') }}">
            <small>{{ $link?->access_password ? __('Leave both fields blank to keep the current password.') : __('Visitors must enter this before opening the destination.') }}</small>
        </label>

        <label class="field">
            <span>{{ __('Confirm password') }}</span>
            <input type="password" name="access_password_confirmation" autocomplete="new-password" placeholder="{{ __('Repeat password') }}">
            <small>{{ __('Enter the same password again to prevent typing mistakes.') }}</small>
        </label>

        <label class="checkbox field full">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $link?->is_active ?? true) ? 'checked' : '' }}>
            <span>{{ __('Active and ready to redirect') }}</span>
            <small>{{ __('Turn this off to save the link without allowing public redirects.') }}</small>
        </label>

        @if ($pixels->isNotEmpty())
            <label class="checkbox field full">
                <input type="checkbox" name="retargeting_enabled" value="1" {{ old('retargeting_enabled', $link?->retargeting_enabled ?? false) ? 'checked' : '' }}>
                <span>{{ __('Enable approved retargeting pixels for this link') }}</span>
                <small>{{ __('Use only when this link is part of an approved advertising campaign.') }}</small>
            </label>
            <label class="field full">
                <span>{{ __('Retargeting pixels') }}</span>
                <select name="pixel_ids[]" multiple size="3">@foreach ($pixels as $pixel)<option value="{{ $pixel->id }}" @selected(in_array($pixel->id, old('pixel_ids', $link ? $link->pixels->pluck('id')->all() : [])))>{{ $pixel->name }}{{ $pixel->provider ? ' · '.$pixel->provider : '' }}</option>@endforeach</select>
                <small>{{ __('Only use pixels with a lawful notice and consent where required.') }}</small>
            </label>
        @endif
    </div>
</details>

<div class="form-footer">
    <a class="button" href="{{ $link ? route('links.show', $link) : route('links.index') }}">{{ __('Cancel') }}</a>
    <button class="button primary" type="submit">{{ $link ? __('Save changes') : __('Create short link') }}</button>
</div>

@push('head')
    <style>
        .link-primary-fields{margin-bottom:20px}.tag-options{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}.tag-option{display:flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid var(--border);border-radius:9px;background:#fff;cursor:pointer}.tag-option:has(input:checked){border-color:#538f3f;background:#f0f6ed;color:#315e35}.tag-option input{width:auto;margin:0}.tag-option i{width:8px;height:8px;border-radius:50%}.inline-create{margin-top:10px;border:1px dashed #bfcdc3;border-radius:10px;background:#fafcf9}.inline-create>summary{padding:10px 12px;color:#386f3c;font-size:13px;font-weight:700;cursor:pointer;list-style:none}.inline-create>summary::-webkit-details-marker{display:none}.inline-create-body{display:grid;grid-template-columns:1fr 120px;gap:12px;padding:2px 12px 13px}.campaign-inline-create .inline-create-body{grid-template-columns:1fr 1fr}.advanced-options{border:1px solid var(--border);border-radius:12px;background:#fafcf9;margin-top:8px;overflow:hidden}.advanced-options>summary{display:flex;align-items:center;justify-content:space-between;padding:15px 17px;cursor:pointer;list-style:none}.advanced-options>summary::-webkit-details-marker{display:none}.advanced-options>summary span{display:grid;gap:3px}.advanced-options>summary small{font-weight:400;color:var(--muted)}.advanced-options>summary b{font-size:20px;transition:transform .2s}.advanced-options[open]>summary b{transform:rotate(180deg)}.advanced-options-body{border-top:1px solid var(--border);padding:18px}.advanced-options textarea{min-height:85px}.advanced-options .checkbox{flex-wrap:wrap}.advanced-options .checkbox small{flex-basis:100%;margin-inline-start:24px}@media(max-width:680px){.inline-create-body,.campaign-inline-create .inline-create-body{grid-template-columns:1fr}.color-field input{height:44px}}
    </style>
@endpush

@if (! $link)
    @push('scripts')
        <script>
            function toggleCustom() {
                const customCode = document.getElementById('customCode');
                const selectedType = document.querySelector('[name=code_type]:checked');
                if (customCode && selectedType) customCode.style.display = selectedType.value === 'custom' ? 'block' : 'none';
            }
            toggleCustom();
            document.getElementById('newCampaignTrackingUrl')?.addEventListener('input', function () { try { const p = new URL(this.value).searchParams; const map={utm_source:'new_campaign_utm_source',utm_medium:'new_campaign_utm_medium',utm_campaign:'new_campaign_utm_campaign',utm_content:'new_campaign_utm_content',ref:'new_campaign_ref',bref:'new_campaign_bref',sem:'new_campaign_sem'}; Object.entries(map).forEach(([k,n])=>{const e=document.querySelector('[name="'+n+'"]');if(e&&p.has(k)&&!e.value)e.value=p.get(k);}); } catch(e) {} });
        </script>
    @endpush
@endif
