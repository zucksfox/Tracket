<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Sumber data lonceng notifikasi stok kritis di halaman backoffice.
 */
class SparepartNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return response()->json([
            'parts' => Sparepart::query()
                ->where('stock', '<=', 2)
                ->orderBy('stock')
                ->orderBy('name')
                ->get(['id', 'name', 'stock', 'updated_at'])
                ->map(fn (Sparepart $part) => [
                    'id' => $part->id,
                    'name' => $part->name,
                    'stock' => $part->stock,
                    'updated_at' => $part->updated_at?->toISOString(),
                ])
                ->values(),
        ]);
    }
}
