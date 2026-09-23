<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * The admin and staff order lists: their search, status and date filters,
 * and the same filters again for an export. One query, so a downloaded list
 * always holds exactly the orders the screen was showing.
 */
class OrderList
{
    /**
     * The filters a request asks for.
     *
     * Cast rather than lean on input()'s default: submitting the filter form
     * with a field cleared sends `status=`, which ConvertEmptyStringsToNull
     * turns into null. The key is present, so the default never applies, and
     * an un-cast null would sail past the !== '' guard into a
     * `where status is null` that matches no order at all.
     *
     * @return array{search: string, status: string, date: string}
     */
    public function filters(Request $request): array
    {
        return [
            'search' => (string) $request->input('search', ''),
            'status' => (string) $request->input('status', ''),
            'date' => (string) $request->input('date', ''),
        ];
    }

    /**
     * Every order the filters let through, newest first.
     *
     * @param  array{search: string, status: string, date: string}  $filters
     */
    public function query(array $filters): Builder
    {
        $query = Order::query()->latest();
        $search = $filters['search'];

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('payment_reference', 'like', "%{$search}%")
                    ->orWhere('office', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('fullname', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        switch ($filters['date']) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                break;
        }

        return $query;
    }

    /**
     * The filters in words, for the heading of an exported list:
     * "All statuses · This month (September 2026) · Search: “Registrar”".
     *
     * @param  array{search: string, status: string, date: string}  $filters
     */
    public function describe(array $filters): string
    {
        $now = Carbon::now();

        $parts = [
            $filters['status'] !== '' ? Order::statusLabel($filters['status']) : 'All statuses',
            match ($filters['date']) {
                'today' => 'Today (' . $now->format('F j, Y') . ')',
                'week' => 'This week (' . $this->span($now->copy()->startOfWeek(), $now->copy()->endOfWeek()) . ')',
                'month' => 'This month (' . $now->format('F Y') . ')',
                default => 'All time',
            },
        ];

        if ($filters['search'] !== '') {
            $parts[] = 'Search: “' . $filters['search'] . '”';
        }

        return implode(' · ', $parts);
    }

    /** "September 21–27, 2026", or across a month or year boundary in full. */
    private function span(Carbon $start, Carbon $end): string
    {
        if ($start->year !== $end->year) {
            return $start->format('F j, Y') . ' – ' . $end->format('F j, Y');
        }

        if ($start->month !== $end->month) {
            return $start->format('F j') . ' – ' . $end->format('F j, Y');
        }

        return $start->format('F j') . '–' . $end->format('j, Y');
    }
}
