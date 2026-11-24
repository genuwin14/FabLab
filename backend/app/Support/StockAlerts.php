<?php

namespace App\Support;

use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Texture;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\OutOfStockAlert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Cache;

/**
 * What the stock alert banner at the top of every admin and staff page says.
 *
 * The panel asked for the low-stock alert to appear at the top of the screen
 * and stay there until the user dismisses it. A toast closes itself and the
 * bell only counts, so the alert is a banner under the navbar, and it holds
 * two things:
 *
 *   - every item on the Stock Monitoring list (at or below its low-stock
 *     line) that this reader has not dismissed yet, so stock that was
 *     already low before anyone was watching still gets said;
 *   - every unread low- or out-of-stock notification, which also covers a
 *     single size and colour of a garment running low while the garment's
 *     total is fine.
 *
 * Dismissing marks those notifications read and remembers which items were
 * on the list. The banner comes back only for something new: an item that
 * goes low later, or one that recovered and went low again.
 */
class StockAlerts
{
    public const TYPES = [LowStockAlert::class, OutOfStockAlert::class];

    /** How many alerts the banner names before it says "and N more". */
    public const SHOWN = 3;

    /**
     * @return array{count: int, out_of_stock: bool, items: list<array{title: string, body: string, tone: string}>, more: int}
     */
    public function for(?User $user): array
    {
        if (! $user || ! in_array($user->role, ['admin', 'staff'], true)) {
            return $this->summarise([]);
        }

        $watchlist = $this->watchlist();
        $dismissed = $this->dismissed($user, array_keys($watchlist));

        $entries = [];

        // Unread alerts first, newest first, one per item: an item that ran
        // low and then out has two, and only the newer one is still true. An
        // item still on the list is described as it stands now, not as it
        // stood when the alert was raised.
        foreach ($this->unreadAlerts($user) as $alert) {
            $key = $this->alertKey($alert);
            if (isset($entries[$key])) {
                continue;
            }

            $entries[$key] = $watchlist[$key] ?? [
                'title' => (string) ($alert->data['title'] ?? 'Stock alert'),
                'body' => (string) ($alert->data['body'] ?? ''),
                'tone' => $alert->type === OutOfStockAlert::class ? 'danger' : 'warning',
            ];
        }

        // Then whatever is low that nobody has dismissed: out of stock first.
        $rest = array_diff_key($watchlist, $entries, array_flip($dismissed));
        uasort($rest, fn (array $a, array $b) => ($b['tone'] === 'danger') <=> ($a['tone'] === 'danger'));

        return $this->summarise(array_values($entries + $rest));
    }

    /**
     * The banner's dismiss button: every unread stock alert becomes read, and
     * everything low right now counts as seen.
     */
    public function dismiss(User $user): void
    {
        $user->unreadNotifications()
            ->whereIn('type', self::TYPES)
            ->update(['read_at' => now()]);

        Cache::forever($this->cacheKey($user), array_keys($this->watchlist()));
    }

    /**
     * Everything on the Stock Monitoring list, described, keyed the way the
     * alerts name their item: "Raw Material:12".
     *
     * @return array<string, array{title: string, body: string, tone: string}>
     */
    private function watchlist(): array
    {
        $items = Product::needsRestock()->get()
            ->concat(RawMaterial::needsRestock()->get())
            ->concat(Texture::needsRestock()->get());

        $watchlist = [];
        foreach ($items as $item) {
            $watchlist[$this->itemKey($item)] = $this->describe($item);
        }

        return $watchlist;
    }

    /** @return array{title: string, body: string, tone: string} */
    private function describe(Model $item): array
    {
        if ($item->currentStock() <= 0) {
            return [
                'title' => "Out of stock: {$item->name}",
                'body' => 'Restock this ' . strtolower($item->stockItemType()) . ' to keep it available.',
                'tone' => 'danger',
            ];
        }

        return [
            'title' => "Low stock: {$item->name}",
            'body' => 'Only ' . $this->number($item->currentStock()) . " {$item->stockUnit()} left (threshold "
                . $this->number((float) $item->stockThreshold()) . ').',
            'tone' => 'warning',
        ];
    }

    /**
     * What this reader dismissed, less anything no longer low: an item that
     * recovered and runs low again is news again.
     *
     * @param  list<string>  $low
     * @return list<string>
     */
    private function dismissed(User $user, array $low): array
    {
        $stored = Cache::get($this->cacheKey($user), []);
        $kept = array_values(array_intersect(is_array($stored) ? $stored : [], $low));

        if (count($kept) !== count(is_array($stored) ? $stored : [])) {
            Cache::forever($this->cacheKey($user), $kept);
        }

        return $kept;
    }

    /** @return \Illuminate\Support\Collection<int, DatabaseNotification> */
    private function unreadAlerts(User $user)
    {
        return $user->unreadNotifications()
            ->whereIn('type', self::TYPES)
            ->latest()
            // A runaway backlog must not slow every page; naming three of two
            // hundred says the same as three of a thousand.
            ->limit(200)
            ->get();
    }

    /**
     * @param  list<array{title: string, body: string, tone: string}>  $entries
     * @return array{count: int, out_of_stock: bool, items: list<array{title: string, body: string, tone: string}>, more: int}
     */
    private function summarise(array $entries): array
    {
        return [
            'count' => count($entries),
            'out_of_stock' => in_array('danger', array_column($entries, 'tone'), true),
            'items' => array_slice($entries, 0, self::SHOWN),
            'more' => max(0, count($entries) - self::SHOWN),
        ];
    }

    private function itemKey(Model $item): string
    {
        return $item->stockItemType() . ':' . $item->getKey();
    }

    private function alertKey(DatabaseNotification $alert): string
    {
        return isset($alert->data['item_type'], $alert->data['item_id'])
            ? $alert->data['item_type'] . ':' . $alert->data['item_id']
            : 'notification:' . $alert->id;
    }

    private function cacheKey(User $user): string
    {
        return 'stock-alert-banner:dismissed:' . $user->getKey();
    }

    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.');
    }
}
