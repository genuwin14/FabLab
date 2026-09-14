# Ink Consumption

Walang fixed na ink amount per order o per element ang system. Sinusukat
niya yung mismong artwork ng customer at kinukwenta kung gaano karami ng
bawat printer ink (Cyan, Magenta, Yellow, Black) ang gagamitin ng print.

```
ink per item (ml) = coverage × printable area (cm²) × rate (ml/cm²)
ink per line (ml) = ink per item × quantity ordered
```

## How it works

1. **The studio exports the artwork.** Pag na-save ang design, nire-render
   ng design studio yung shapes, text, at images ng customer nang mag-isa
   sa transparent na canvas, walang damit at walang finish. Yung "flat
   print" na ito ang pinapadala sa server kasama ng share ng canvas na
   printable panels.

2. **The server measures coverage.** Bawat pixel ng flat print, kino-convert
   mula RGB papuntang CMYK at tinitimbang ayon sa opacity nito. Transparent
   na pixel ay wala, white ay walang ink. Yung totals, dini-divide sa
   printable pixels, kaya lumalabas kung ilang fraction ng printable area
   ang natatakpan ng bawat ink. Nase-save ang result sa design, at
   itinatabi yung flat print para makita ng reviewer kung ano yung sinukat.

3. **Coverage becomes square centimetres.** Bawat product may printable
   area sa cm², inilalagay ng admin sa product form. Ini-scale ito ng size
   factor ng in-order na size (Customization Pricing). Medium ay 1.0.

4. **Square centimetres become millilitres.** Bawat ink channel may rate na
   ml per cm² ng solid coverage, nase-set sa Customization Pricing →
   Sublimation ink, kasama ng raw-material bottle na pinagkukunan nito.
   Default ay 0.0025 ml/cm². Dapat i-calibrate nang isang beses: timbangin
   ang bottle, mag-print ng solid 10 × 10 cm square, timbangin ulit,
   i-divide sa 100.

5. **The order screen shows the working.** Bawat ink bottle sa order,
   pinapakita yung coverage, area, rate, at quantity, halimbawa:

   ```
   Shirt: 4.2% Magenta coverage of 900 cm² (measured from the print, size M) × 0.0025 ml/cm² × 3 items
   ```

   Pwedeng i-correct ng reviewer ang kahit anong figure bago mag-approve.

6. **Stock moves like any other material.** Sa approval, nire-reserve yung
   millilitres mula sa bottle at nire-record sa Usage Log. Pag nilipat sa
   production ang order, nagiging consumed. Pag na-cancel, binabaliktad.
   Hindi papayagan ang approval kung magiging negative ang bottle.

## Notes

- Yung mga design na na-save bago pa nagkaroon ng flat-print export ay
  **estimated** mula sa recipe nila, at nilalabel ito ng order screen.
- Kung walang printable area ang product, o walang ink channel na naka-link
  sa bottle, babalik ang order sa per-element na bill of materials.
- Kung measured ang ink, nilalaktawan yung bill-of-materials ink lines para
  sa item na yun, kaya hindi kailanman doble ang bawas sa bottle.
- Yung ink na nagamit sa head cleaning at test prints, mano-manong
  nire-record sa Usage Log.

## Code

| Piece | File |
|---|---|
| Flat-print export | `backend/public/js/customizer/rendering.js` |
| Measuring on save | `backend/app/Http/Controllers/Concerns/SavesCustomDesign.php` |
| Coverage and millilitres | `backend/app/Services/InkEstimator.php` |
| Channels and rates | `backend/app/Models/InkChannel.php` |
| Reserve, consume, reverse | `backend/app/Services/OrderStockService.php` |
