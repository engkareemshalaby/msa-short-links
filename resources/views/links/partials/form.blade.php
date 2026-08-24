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

    <label class="field full">
        <span>{{ __('Destination URL') }}</span>
        <textarea name="destination_url" required placeholder="https://www.msa.edu.eg/...">{{ old('destination_url', $link?->destination_url) }}</textarea>
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
