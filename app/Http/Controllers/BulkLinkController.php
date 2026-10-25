<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Tag;
use App\Services\ShortLinkCreator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BulkLinkController extends Controller
{
    public function create(): View
    {
        return view('links.bulk', [
            'campaigns' => Campaign::where('is_active', true)->orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->hasFile('csv')) {
            $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
            $handle = fopen($request->file('csv')->getRealPath(), 'r');
            $rows = [];
            try {
                $header = array_map(fn ($value) => strtolower(trim((string) $value, "\xEF\xBB\xBF \t\n\r")), fgetcsv($handle, escape: '') ?: []);
                if (! in_array('destination_url', $header, true) || count($header) !== count(array_unique($header))) {
                    throw ValidationException::withMessages(['csv' => __('CSV must have unique headers including destination_url.')]);
                }
                while (($row = fgetcsv($handle, escape: '')) !== false) {
                    if ($row === [null]) {
                        continue;
                    }
                    if (count($row) !== count($header)) {
                        throw ValidationException::withMessages(['csv' => __('CSV row :row has a different number of columns.', ['row' => count($rows) + 2])]);
                    }
                    $data = array_combine($header, $row);
                    $rows[] = array_intersect_key($data, array_flip(['destination_url', 'code', 'title']));
                    if (count($rows) > 200) {
                        throw ValidationException::withMessages(['csv' => __('Use up to 200 links per batch.')]);
                    }
                }
            } finally {
                fclose($handle);
            }

            return redirect()->route('links.bulk.create')->withInput(['rows' => $rows])
                ->with('success', __('CSV loaded. Review the rows before creating links.'));
        }

        // Normalize before checking uniqueness, including duplicates within this batch.
        $rows = $request->input('rows');
        if (is_array($rows)) {
            foreach ($rows as &$row) {
                if (! is_array($row)) {
                    continue;
                }
                foreach (['destination_url', 'code', 'title'] as $field) {
                    if (isset($row[$field]) && is_string($row[$field])) {
                        $row[$field] = trim($row[$field]);
                        if ($field === 'code') {
                            $row[$field] = strtolower($row[$field]);
                        }
                        if ($row[$field] === '') {
                            $row[$field] = null;
                        }
                    }
                }
            }
            unset($row);
            $request->merge(['rows' => $rows]);
        }

        $data = $request->validate([
            'rows' => ['required', 'array', 'list', 'min:1', 'max:200'],
            'rows.*' => ['required', 'array'],
            'rows.*.destination_url' => ['required', 'url:http,https', 'max:5000'],
            'rows.*.title' => ['nullable', 'string', 'max:255'],
            'rows.*.code' => ['nullable', 'string', 'regex:/^[a-z0-9][a-z0-9_-]{1,49}$/', 'distinct', Rule::unique('short_links', 'code'), Rule::notIn(ShortLinkController::RESERVED)],
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ], [
            'rows.*.code.distinct' => __('Row :position: this slug is repeated in the batch.'),
            'rows.*.code.unique' => __('Row :position: this slug is already used, including archived links.'),
            'rows.*.code.not_in' => __('Row :position: this slug is reserved.'),
            'rows.*.code.regex' => __('Row :position: use 2-50 letters, numbers, hyphens or underscores, starting with a letter or number.'),
            'rows.*.destination_url.required' => __('Row :position: enter a destination URL.'),
            'rows.*.destination_url.url' => __('Row :position: enter a complete http or https URL.'),
        ]);

        if ($request->input('intent') === 'validate') {
            return response()->json(['message' => __('All rows are valid. Ready to create links.')]);
        }

        try {
            $links = DB::transaction(function () use ($data, $request) {
                $created = [];
                // Reserve custom codes first so a generated numeric code cannot take one.
                $rows = $data['rows'];
                uasort($rows, fn ($a, $b) => (int) filled($b['code'] ?? null) <=> (int) filled($a['code'] ?? null));
                foreach ($rows as $index => $row) {
                    $created[$index] = app(ShortLinkCreator::class)->create([
                        'title' => $row['title'] ?? null,
                        'destination_url' => $row['destination_url'],
                        'code_type' => filled($row['code'] ?? null) ? 'custom' : 'random',
                        'custom_code' => $row['code'] ?? null,
                        'campaign_id' => $data['campaign_id'] ?? null,
                        'tag_ids' => $data['tag_ids'] ?? [],
                        'is_active' => true,
                    ], $request->user()->id)->short_url;
                }
                ksort($created);

                return array_values($created);
            });
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['rows' => __('A slug was just taken. Check the rows and try again. No links were created.')]);
        }

        return redirect()->route('links.bulk.create')
            ->with('success', __('Created :count links.', ['count' => count($links)]))
            ->with('created_links', $links);
    }
}
