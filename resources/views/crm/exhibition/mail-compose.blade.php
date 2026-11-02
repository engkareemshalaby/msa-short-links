@extends('layouts.app')
@section('title', __('Send mail'))
@section('subtitle', __('Write an email to the registrations selected by your filters.'))
@section('content')
<div class="card"><div class="card-body mail-form">
    <p><strong>{{ number_format($registrationCount) }}</strong> {{ __('matching registrations') }}</p>
    <form id="mail-form" method="POST" action="{{ route('crm.exhibition.mail.preview') }}">
        @csrf
        @foreach($filters as $name => $value)
            @if(is_scalar($value))<input type="hidden" name="filters[{{ $name }}]" value="{{ $value }}">@endif
        @endforeach
        <label class="field"><span>{{ __('Subject') }}</span><input name="subject" maxlength="255" required value="{{ old('subject', session('exhibition_mail_draft.subject')) }}"></label>
        <label class="field"><span>{{ __('Email body') }}</span></label>
        <div class="mail-toolbar" role="toolbar" aria-label="{{ __('Formatting') }}">
            <button type="button" data-command="bold" title="Bold"><b>B</b></button>
            <button type="button" data-command="italic" title="Italic"><i>I</i></button>
            <button type="button" data-command="underline" title="Underline"><u>U</u></button>
            <button type="button" data-command="insertUnorderedList" title="Bulleted list">• List</button>
            <button type="button" data-command="insertOrderedList" title="Numbered list">1. List</button>
            <button type="button" data-command="formatBlock" data-value="h2" title="Heading">H2</button>
            <button type="button" data-command="formatBlock" data-value="p" title="Paragraph">P</button>
            <button type="button" id="mail-link">{{ __('Link') }}</button>
            <button type="button" id="mail-image">{{ __('Image URL') }}</button>
        </div>
        <div id="mail-editor" class="mail-editor" contenteditable="true" role="textbox" aria-multiline="true" aria-label="{{ __('Email body') }}">{!! app(\App\Services\ExhibitionMailContent::class)->clean(old('body_html', session('exhibition_mail_draft.body_html', ''))) !!}</div>
        <input type="hidden" name="body_html" id="body-html">
        <p class="mail-hint">{{ __('Images must use a public HTTPS URL. Each email address receives one copy.') }}</p>
        <label class="mail-check"><input type="checkbox" name="include_parents" value="1" @checked(old('include_parents', session('exhibition_mail_draft.include_parents')))> {{ __('Send to parents/guardians as well') }}</label>
        <div class="mail-actions"><a class="button" href="{{ route('crm.exhibition.index', $filters) }}">{{ __('Back') }}</a><button class="button primary" type="submit">{{ __('Review recipients and email') }}</button></div>
    </form>
</div></div>
@endsection
@push('head')<style>.mail-form{max-width:900px}.mail-form .field{display:block;margin:16px 0}.mail-form .field input{width:100%}.mail-toolbar{display:flex;flex-wrap:wrap;gap:5px;padding:8px;border:1px solid #d6dfe5;border-radius:8px 8px 0 0}.mail-toolbar button{background:white;border:1px solid #d6dfe5;border-radius:5px;padding:6px 10px;cursor:pointer}.mail-editor{min-height:300px;padding:18px;border:1px solid #d6dfe5;border-top:0;border-radius:0 0 8px 8px;outline:none;line-height:1.7}.mail-editor:focus{border-color:#4b9844}.mail-editor img{max-width:100%;height:auto}.mail-hint{color:#637585;font-size:13px}.mail-check{display:flex;gap:8px;align-items:center;margin:20px 0}.mail-actions{display:flex;gap:10px;justify-content:flex-end}</style>@endpush
@push('scripts')<script>
const editor = document.getElementById('mail-editor');
document.querySelectorAll('[data-command]').forEach(button => button.addEventListener('click', () => {
    editor.focus();
    document.execCommand(button.dataset.command, false, button.dataset.value || null);
}));
document.getElementById('mail-link').addEventListener('click', () => {
    const url = prompt(@js(__('Paste an HTTPS link')));
    if (url && /^https?:\/\//i.test(url)) { editor.focus(); document.execCommand('createLink', false, url); }
});
document.getElementById('mail-image').addEventListener('click', () => {
    const url = prompt(@js(__('Paste a public HTTPS image URL')));
    if (url && /^https:\/\//i.test(url)) { editor.focus(); document.execCommand('insertImage', false, url); }
});
document.getElementById('mail-form').addEventListener('submit', () => {
    document.getElementById('body-html').value = editor.innerHTML;
});
</script>@endpush
