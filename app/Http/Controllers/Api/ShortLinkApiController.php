<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShortLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShortLinkApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(ShortLink::query()->where('created_by', $request->user()->id)->latest()->paginate(25));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'destination_url' => ['required', 'url:http,https', 'max:5000'],
            'title' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'regex:/^[a-zA-Z0-9][A-Za-z0-9_-]{1,49}$/', Rule::unique('short_links', 'code')],
            'expires_at' => ['nullable', 'date'],
        ]);
        $code = strtolower($data['code'] ?? $this->randomCode());
        $link = ShortLink::create([
            'title' => $data['title'] ?? null, 'code' => $code, 'destination_url' => $data['destination_url'],
            'code_type' => isset($data['code']) ? 'custom' : 'random', 'is_active' => true,
            'expires_at' => $data['expires_at'] ?? null, 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
        ]);

        return response()->json($link, 201);
    }

    public function show(Request $request, ShortLink $link): JsonResponse
    {
        abort_unless($link->created_by === $request->user()->id || $request->user()->hasRole('Super Admin'), 403);
        return response()->json($link->loadCount('visits'));
    }

    private function randomCode(): string
    {
        do { $code = (string) random_int(100000, 999999); } while (ShortLink::withTrashed()->where('code', $code)->exists());
        return $code;
    }
}
