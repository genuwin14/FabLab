# Ink Consumption

The system does not use a fixed ink amount per order or per element. It
measures the customer's artwork and works out how much of each printer ink
(Cyan, Magenta, Yellow, Black) the print will use.

```
ink per item (ml) = coverage × printable area (cm²) × rate (ml/cm²)
ink per line (ml) = ink per item × quantity ordered
```

## How it works

1. **The studio exports the artwork.** When a design is saved, the design
   studio renders the customer's shapes, text and images alone on a
   transparent canvas, without the garment or its finish. This "flat print"
   is sent to the server along with the printable panels' share of the
   canvas.

2. **The server measures coverage.** Every pixel of the flat print is
   converted from RGB to CMYK and weighted by its opacity. Transparent pixels
   count as nothing, white counts as no ink. The totals are divided by the
   printable pixels, giving the fraction of the printable area each ink
   covers. The result is stored on the design, and the flat print is kept so
   the reviewer can see what was measured.

3. **Coverage becomes square centimetres.** Each product has a printable
   area in cm², entered by the admin on the product form. It is scaled by
   the size factor of the ordered size (Customization Pricing). Medium is
   1.0.

4. **Square centimetres become millilitres.** Each ink channel has a rate in
   ml per cm² of solid coverage, set under Customization Pricing →
   Sublimation ink, together with the raw-material bottle it draws from. The
   default is 0.0025 ml/cm². It should be calibrated once: weigh the bottle,
   print a solid 10 × 10 cm square, weigh again, divide by 100.

5. **The order screen shows the working.** Each ink bottle on the order
   shows its coverage, area, rate and quantity, for example:

   ```
   Shirt: 4.2% Magenta coverage of 900 cm² (measured from the print, size M) × 0.0025 ml/cm² × 3 items
   ```

   The reviewer can correct any figure before approving.

6. **Stock moves like any other material.** Approval reserves the
   millilitres from the bottle and records it in the Usage Log. Moving the
   order to production marks it consumed. Cancelling reverses it. Approval
   is refused if a bottle would go negative.

## Notes

- Designs saved before the flat-print export existed are **estimated** from
  their recipe instead, and the order screen labels them so.
- If a product has no printable area, or no ink channel is linked to a
  bottle, the order falls back to the per-element bill of materials.
- Where ink is measured, the bill-of-materials ink lines for that item are
  skipped, so a bottle is never drawn twice.
- Ink used for head cleaning and test prints is recorded by hand in the
  Usage Log.

## Code

| Piece | File |
|---|---|
| Flat-print export | `backend/public/js/customizer/rendering.js` |
| Measuring on save | `backend/app/Http/Controllers/Concerns/SavesCustomDesign.php` |
| Coverage and millilitres | `backend/app/Services/InkEstimator.php` |
| Channels and rates | `backend/app/Models/InkChannel.php` |
| Reserve, consume, reverse | `backend/app/Services/OrderStockService.php` |
