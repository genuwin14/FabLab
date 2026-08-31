<?php

namespace App\Models\Concerns;

use App\Models\RawMaterial;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A finish that draws on a raw material when an item is made in it.
 *
 * Shared by Color and Texture, which are the same idea from opposite ends. A
 * texture already carries its own stock, so a material linked here is drawn
 * *in addition* to it — the texture row is the printed sheet, the material is
 * what printed it. A colour has no stock at all, so this link is the only way
 * an ordered item in that colour takes anything off the shelf; before it
 * existed, a red shirt collected the colour's surcharge and no ink ever moved.
 *
 * The link is optional on both. Leave it unset and the finish consumes
 * nothing, which is exactly how both behaved before.
 */
trait ConsumesRawMaterial
{
    /**
     * The rules the two finish screens validate this pair with.
     *
     * Both nullable, and only meaningful together — normaliseMaterial()
     * settles a half-filled pair rather than failing a save over it.
     */
    public const MATERIAL_VALIDATION_RULES = [
        'raw_material_id' => 'nullable|integer|exists:raw_materials,raw_material_id',
        'material_quantity' => 'nullable|numeric|min:0|max:99999999.9999',
    ];

    public const MATERIAL_VALIDATION_MESSAGES = [
        'raw_material_id.exists' => 'That raw material no longer exists.',
        'material_quantity.numeric' => 'The material quantity must be a number.',
        'material_quantity.min' => "The material quantity can't be negative.",
    ];

    /**
     * What finishing one item in this colour or texture takes off the shelf.
     */
    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id', 'raw_material_id');
    }

    /**
     * Settle the material pair before it is saved.
     *
     * Clearing the material clears the quantity with it, and a quantity of
     * zero clears the material: either way the finish ends up honestly
     * described as consuming nothing, rather than holding a link that draws
     * nothing or a quantity that draws from nowhere.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normaliseMaterial(array $data): array
    {
        $materialId = $data['raw_material_id'] ?? null;
        $quantity = round((float) ($data['material_quantity'] ?? 0), 4);

        $data['raw_material_id'] = ($materialId && $quantity > 0) ? $materialId : null;
        $data['material_quantity'] = $data['raw_material_id'] ? $quantity : 0;

        return $data;
    }
}
