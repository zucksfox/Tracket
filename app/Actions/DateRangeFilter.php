<?php

namespace App\Actions;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DateRangeFilter
{
    /**
     * Parse and validate a date range from request input.
     * Default: current month. Max range: 93 days.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function fromRequest(Request $request): array
    {
        $data = $request->validate([
            'start' => ['nullable', 'date_format:Y-m-d'],
            'end' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $start = Carbon::parse($data['start'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $end = Carbon::parse($data['end'] ?? now()->toDateString())->endOfDay();

        if ($end->lt($start) || $start->diffInDays($end) >= 93) {
            throw ValidationException::withMessages([
                'end' => 'Pilih tanggal akhir setelah tanggal mulai, dengan rentang maksimal 93 hari.',
            ]);
        }

        return [$start, $end];
    }
}
