@extends('layouts.app')
@section('title', __('Bulk create links'))
@section('subtitle', __('Paste from Excel, duplicate rows, or load a CSV. Review everything before saving.'))
@section('content')
@if(session('created_links'))
<div class="card"><div class="card-body">
    <label class="field"><span>{{ __('Created links') }}</span><textarea readonly rows="6" dir="ltr">{{ implode("\n", session('created_links')) }}</textarea></label>
    <button class="button primary" type="button" data-copy="{{ implode("\n", session('created_links')) }}">{{ __('Copy all links') }}</button>
</div></div>
@endif
<div class="card"><div class="card-body">
    <details><summary>{{ __('Load CSV') }}</summary>
        <form method="POST" action="{{ route('links.bulk.store') }}" enctype="multipart/form-data">
            @csrf
            <label class="field"><span>{{ __('CSV file') }}</span><input type="file" name="csv" accept=".csv,text/csv" required></label>
            <p>{{ __('Required column: destination_url. Optional columns: title, code.') }}</p>
            <button class="button" type="submit">{{ __('Load for review') }}</button>
        </form>
    </details>
</div></div>
<form method="POST" action="{{ route('links.bulk.store') }}" id="bulk-form">
    @csrf
    <div class="card"><div class="card-body">
        <div class="form-grid">
            <label class="field"><span>{{ __('Campaign') }}</span><select name="campaign_id"><option value="">{{ __('No campaign') }}</option>@foreach($campaigns as $campaign)<option value="{{ $campaign->id }}" @selected(old('campaign_id') == $campaign->id)>{{ $campaign->name }}</option>@endforeach</select></label>
            <div class="field"><span>{{ __('Tags for all links') }}</span><div class="bulk-tags">@foreach($tags as $tag)<label><input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" @checked(in_array($tag->id, old('tag_ids', [])))> {{ $tag->name }}</label>@endforeach</div></div>
        </div>
        <label class="field"><span>{{ __('Paste from Excel') }}</span><textarea id="bulk-paste" rows="3" dir="ltr" placeholder="destination_url&#9;code&#9;title"></textarea><small>{{ __('Copy columns in this order: destination URL, slug, optional name. No header needed. Maximum 200 rows.') }}</small></label>
        <button class="button" id="paste-rows" type="button">{{ __('Add pasted rows') }}</button>
        <p>{{ __('Leave the slug blank for a random code. Leave the name blank to display the slug or code.') }}</p>
    </div>
    <div class="table-wrap"><table class="data-table bulk-table"><thead><tr><th>#</th><th>{{ __('Domain URL') }}</th><th>{{ __('Custom slug') }}</th><th>{{ __('Link name') }}</th><th></th></tr></thead><tbody id="bulk-rows"></tbody></table></div>
    <div class="card-body">
        <div id="bulk-feedback" role="status" aria-live="polite"></div>
        <div class="form-footer">
            <button class="button" id="add-row" type="button">{{ __('Add row') }}</button>
            <button class="button" id="check-rows" type="button">{{ __('Check rows') }}</button>
            <button class="button primary" id="save-bulk" type="submit">{{ __('Create all links') }}</button>
        </div>
    </div></div>
</form>
<template id="bulk-row-template"><tr>
    <td class="row-number"></td>
    <td><input type="url" data-field="destination_url" dir="ltr" required maxlength="5000" placeholder="https://..."><small data-error="destination_url"></small></td>
    <td><input data-field="code" dir="ltr" maxlength="50" pattern="[A-Za-z0-9][A-Za-z0-9_-]{1,49}" placeholder="apply-now-ar"><small data-error="code"></small></td>
    <td><input data-field="title" maxlength="255" placeholder="{{ __('Optional') }}"><small data-error="title"></small></td>
    <td><div class="actions"><button class="button small" type="button" data-action="duplicate">{{ __('Duplicate') }}</button><button class="button small" type="button" data-action="remove">{{ __('Remove') }}</button></div></td>
</tr></template>
@endsection
@push('head')
<style>
.bulk-table{min-width:850px}.bulk-table td{vertical-align:top}.bulk-table input{width:100%;min-width:130px;padding:10px;border:1px solid var(--border);border-radius:8px}.bulk-table td:nth-child(2){width:40%}.bulk-table [aria-invalid="true"]{border-color:#b42318}.bulk-table small{display:block;color:#b42318;max-width:320px;white-space:normal}.bulk-tags{display:flex;flex-wrap:wrap;gap:12px}.bulk-tags input{width:auto}#bulk-feedback{white-space:pre-line;margin-block:12px}.card{margin-bottom:16px}
</style>
@endpush
@push('scripts')
<script>
(() => {
    const form = document.getElementById('bulk-form');
    const body = document.getElementById('bulk-rows');
    const feedback = document.getElementById('bulk-feedback');
    const paste = document.getElementById('bulk-paste');
    const limitMessage = @js(__('Use up to 200 links per batch.'));
    const renumber = () => [...body.rows].forEach((row, index) => {
        row.querySelector('.row-number').textContent = index + 1;
        row.querySelectorAll('[data-field]').forEach(input => {
            input.name = 'rows[' + index + '][' + input.dataset.field + ']';
            input.setAttribute('aria-label', (index + 1) + ' ' + input.dataset.field);
        });
    });
    function addRow(data = {}, after = null) {
        if (body.rows.length >= 200) { feedback.textContent = limitMessage; return; }
        const row = document.getElementById('bulk-row-template').content.firstElementChild.cloneNode(true);
        row.querySelectorAll('[data-field]').forEach(input => input.value = data[input.dataset.field] || '');
        if (after) after.after(row); else body.append(row);
        renumber();
        return row;
    }
    const initialRows = @js(old('rows', [['destination_url' => '', 'code' => '', 'title' => '']]));
    (Array.isArray(initialRows) && initialRows.length ? initialRows : [{}]).forEach(row => addRow(row));
    function showErrors(errors) {
        feedback.textContent = Object.values(errors).flat().join('\n');
        Object.entries(errors).forEach(([key, messages]) => {
            const match = key.match(/^rows\.(\d+)\.(destination_url|code|title)$/);
            if (!match) return;
            const row = body.rows[Number(match[1])];
            if (!row) return;
            row.querySelector('[data-error="' + match[2] + '"]').textContent = messages.join(' ');
            row.querySelector('[data-field="' + match[2] + '"]').setAttribute('aria-invalid', 'true');
        });
    }
    function clearErrors() {
        feedback.textContent = '';
        body.querySelectorAll('[data-error]').forEach(el => el.textContent = '');
        body.querySelectorAll('[aria-invalid]').forEach(el => el.removeAttribute('aria-invalid'));
    }
    showErrors(@js($errors->getMessages()));
    form.addEventListener('input', clearErrors);
    document.getElementById('add-row').addEventListener('click', () => { clearErrors(); addRow()?.querySelector('input').focus(); });
    body.addEventListener('click', event => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        clearErrors();
        const row = button.closest('tr');
        if (button.dataset.action === 'remove') { row.remove(); if (!body.rows.length) addRow(); renumber(); return; }
        const data = Object.fromEntries([...row.querySelectorAll('[data-field]')].map(input => [input.dataset.field, input.value]));
        data.code = '';
        addRow(data, row)?.querySelector('[data-field="code"]').focus();
    });
    // Parse tab-separated clipboard cells, including Excel's quoted multiline cells.
    function parseTsv(text) {
        const rows = []; let row = [], cell = '', quoted = false;
        for (let i = 0; i < text.length; i++) {
            const char = text[i];
            if (char === '"') {
                if (quoted && text[i + 1] === '"') { cell += '"'; i++; }
                else if (quoted || cell === '') quoted = !quoted;
                else cell += char;
            } else if (!quoted && (char === '\t' || char === '\n' || char === '\r')) {
                row.push(cell); cell = '';
                if (char !== '\t') { rows.push(row); row = []; if (char === '\r' && text[i + 1] === '\n') i++; }
            } else cell += char;
        }
        if (quoted) throw new Error(@js(__('The pasted text has an unclosed quote. Check the copied cells.')));
        row.push(cell); rows.push(row);
        return rows.filter(cells => cells.some(cell => cell.trim() !== ''));
    }
    document.getElementById('paste-rows').addEventListener('click', () => {
        clearErrors();
        try {
            const rows = parseTsv(paste.value);
            if (!rows.length) return;
            if (rows.some(cells => cells.length > 3)) throw new Error(@js(__('Paste only three columns: destination URL, slug, name.')));
            const emptyRows = [...body.rows].filter(row => [...row.querySelectorAll('input')].every(input => !input.value.trim()));
            if (body.rows.length - emptyRows.length + rows.length > 200) throw new Error(limitMessage);
            emptyRows.forEach(row => row.remove());
            rows.forEach(([destination_url, code, title]) => addRow({destination_url, code, title}));
            paste.value = '';
        } catch (error) { feedback.textContent = error.message; }
    });
    let busy = false;
    async function validate(save) {
        if (busy || !form.reportValidity()) return;
        busy = true; clearErrors();
        const controls = [...form.querySelectorAll('input, select, textarea, button')];
        const data = new FormData(form); data.set('intent', 'validate');
        controls.forEach(el => el.disabled = true);
        let submitting = false;
        try {
            const response = await fetch(form.action, {method: 'POST', body: data, headers: {'Accept': 'application/json'}});
            const result = await response.json();
            if (response.status === 422) { showErrors(result.errors); return; }
            if (!response.ok) throw new Error();
            feedback.textContent = result.message;
            if (save) {
                controls.forEach(el => el.disabled = false);
                submitting = true;
                document.getElementById('save-bulk').disabled = true;
                HTMLFormElement.prototype.submit.call(form);
            }
        } catch (error) { feedback.textContent = @js(__('Could not check the rows. Please try again.')); }
        finally { if (!submitting) { controls.forEach(el => el.disabled = false); busy = false; } }
    }
    document.getElementById('check-rows').addEventListener('click', () => validate(false));
    form.addEventListener('submit', event => { event.preventDefault(); validate(true); });
})();
</script>
@endpush
