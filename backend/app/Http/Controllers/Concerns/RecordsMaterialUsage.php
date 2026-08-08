<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\StockMovementReason;
use App\Models\RawMaterial;
use App\Models\RawMaterialMovement;
use App\Services\RawMaterialStockService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * The Record Usage form and the Usage Log tab, shared by the admin and staff
 * raw material screens.
 *
 * Both roles record usage the same way — the only difference is that a stock
 * correction is admin-only, and each redirects to its own index route.
 */
trait RecordsMaterialUsage
{
    /** 'admin' or 'staff'. */
    abstract protected function usageRoutePrefix(): string;

    /**
     * Reconciling against a physical count rewrites the shelf figure with no
     * paper trail behind it, so it stays with admin.
     */
    protected function allowsStockCorrection(): bool
    {
        return $this->usageRoutePrefix() === 'admin';
    }

    /**
     * Record one movement against a material.
     */
    public function storeUsage(Request $request, $id, RawMaterialStockService $stock)
    {
        $selectable = StockMovementReason::selectable($this->allowsStockCorrection());

        $request->validate([
            'reason' => ['required', Rule::in(array_map(fn ($r) => $r->value, $selectable))],
            'quantity' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'reason.in' => 'Pick a valid reason for the movement.',
        ]);

        $material = RawMaterial::findOrFail($id);
        $reason = StockMovementReason::from($request->input('reason'));
        $quantity = (float) $request->input('quantity');

        $context = [
            'user_id' => Auth::id(),
            'note' => $request->filled('note') ? trim($request->input('note')) : null,
        ];

        try {
            $movement = $reason === StockMovementReason::Correction
                ? $stock->correct($material, $quantity, $context)
                : $stock->record($material, $reason, $quantity, $context);
        } catch (RuntimeException $e) {
            return $this->backToUsageLog()->with('error', $e->getMessage());
        }

        return $this->backToUsageLog()->with('success', $this->usageConfirmation($material, $movement));
    }

    /**
     * Undo a movement someone recorded by mistake.
     */
    public function reverseUsage($movementId, RawMaterialStockService $stock)
    {
        $movement = RawMaterialMovement::with(['rawMaterial', 'reversal'])->findOrFail($movementId);

        try {
            $stock->reverse($movement, ['user_id' => Auth::id()]);
        } catch (RuntimeException $e) {
            return $this->backToUsageLog()->with('error', $e->getMessage());
        }

        $material = $movement->rawMaterial->refresh();

        return $this->backToUsageLog()->with('success', sprintf(
            'Reversed the %s entry on %s. Stock is now %s %s.',
            strtolower($movement->reason->shortLabel()),
            $material->name,
            $this->usageNumber((float) $material->stock_quantity),
            $material->unit
        ));
    }

    /**
     * The ledger behind the Usage Log tab. Paginated under its own page name
     * so walking the log doesn't move the materials table underneath it.
     *
     * @return LengthAwarePaginator<RawMaterialMovement>
     */
    protected function usageLog(Request $request): LengthAwarePaginator
    {
        $perPage = (int) $request->query('log_per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $query = RawMaterialMovement::with(['rawMaterial', 'user', 'reversal'])
            ->latest('movement_id');

        if ($request->filled('log_material')) {
            $query->where('raw_material_id', $request->query('log_material'));
        }

        if ($request->filled('log_reason')) {
            $query->where('reason', $request->query('log_reason'));
        }

        return $query->paginate($perPage, ['*'], 'log_page')->withQueryString();
    }

    private function backToUsageLog()
    {
        return redirect()->route($this->usageRoutePrefix() . '.raw-materials.index', ['tab' => 'log']);
    }

    /**
     * Say what actually happened, since the three outcomes differ: stock drops,
     * stock is only tagged, or stock is reconciled in either direction.
     */
    private function usageConfirmation(RawMaterial $material, RawMaterialMovement $movement): string
    {
        $material = $material->refresh();
        $quantity = $this->usageNumber((float) $movement->quantity);
        $stockNow = $this->usageNumber((float) $material->stock_quantity);

        if ($movement->reason === StockMovementReason::Correction) {
            $direction = (float) $movement->stock_delta > 0 ? 'up' : 'down';

            return sprintf(
                'Corrected %s %s by %s %s. Stock is now %s %s.',
                $material->name,
                $direction,
                $quantity,
                $material->unit,
                $stockNow,
                $material->unit
            );
        }

        if (! $movement->reason->reducesStock()) {
            return sprintf(
                'Tagged %s %s of %s as on display. Stock is unchanged at %s %s.',
                $quantity,
                $material->unit,
                $material->name,
                $stockNow,
                $material->unit
            );
        }

        return sprintf(
            'Recorded %s %s of %s as %s. Stock is now %s %s.',
            $quantity,
            $material->unit,
            $material->name,
            strtolower($movement->reason->shortLabel()),
            $stockNow,
            $material->unit
        );
    }

    private function usageNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
