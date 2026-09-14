# How Ink Consumption Is Computed

This explains, step by step, how the system arrives at the millilitres of
ink an order draws from inventory, why it works that way, and answers the
questions most often asked about it.

## The one-sentence answer

The system does not use a fixed ink amount per order or per element. It
**measures the customer's artwork**: it works out what fraction of the
product's printable area each of the printer's four inks covers, multiplies
that by the printable area of the product at the ordered size, and multiplies
again by a calibrated rate in millilitres per square centimetre.

```
ink per item (ml) = coverage  ×  printable area (cm²)  ×  rate (ml/cm²)
ink per line (ml) = ink per item × quantity ordered
```

This is done once per ink channel: Cyan, Magenta, Yellow and Black (CMYK),
which are the four cartridges of a sublimation printer.

## The pipeline, step by step

### 1. The studio exports a "flat print" when the design is saved

The 3D design studio draws the customer's design on a 1024 × 1024 pixel
canvas called the atlas. Every printable panel of the product (for a shirt:
front, back, sleeves) occupies a rectangle of that canvas.

When the customer saves the design or adds it to the cart, the studio renders
a second copy of the canvas that contains **only the artwork**: the shapes,
text and uploaded images the customer placed. The garment, its colour and
its texture are left out, on a transparent background. That image is the
flat print. It is sent to the server together with:

- the share of the canvas that is printable (the panels' total area as a
  fraction of the atlas), and
- the rectangle of each panel, so the order screen can later show the
  reviewer exactly what prints on the front, the back, and so on.

Why leave the finish out: the colour or texture of the blank is dyed or
comes as its own printed sheet and is costed separately. Only what the
customer drew goes through the printer.

### 2. The server measures coverage per ink

The server (`InkEstimator::measure`) opens the flat print, resamples it to
256 × 256 pixels for speed, and walks every pixel:

- A fully transparent pixel is skipped. Nothing prints there.
- Every other pixel's RGB colour is converted to CMYK with the standard
  formula (K = 1 − max(R,G,B); C = (1 − R − K)/(1 − K); likewise M and Y).
- Each channel's value is weighted by the pixel's opacity and added to that
  channel's running total.

The totals are then divided by the number of **printable** pixels (the
canvas size × the printable share sent by the studio), not by the whole
canvas. A shirt's atlas is mostly seams and gaps that never see ink, so
measuring against the panels is what makes the fraction comparable to the
product's printable area.

The result is four fractions between 0 and 1, one per channel. These are
stored on the design as `ink_coverage`, and the flat print itself is kept on
disk so the reviewer can see what was measured.

Two properties of this worth knowing:

- **White takes no ink.** In CMYK white is 0,0,0,0. That is correct for
  sublimation, which has no white ink; white is the substrate showing
  through.
- **A red logo takes no cyan.** Pure red converts to C 0, M 1, Y 1, K 0.
  The system therefore draws from the Magenta and Yellow bottles only.
  A per-element bill of materials could never know this.

### 3. The printable area comes from the product and the size

Each product carries `print_area_cm2`, entered by the admin on the product
form. It is the real, measured area of the product's printable panels in
square centimetres. This is the physical anchor: coverage is a fraction, and
this figure turns it into centimetres.

The recipe records the size the customer chose. The size's
`print_area_factor` (Customization Pricing screen) scales the area. Medium
is 1.0, the product's own figure. All factors currently ship as 1.0; the
admin can set, for example, XL to 1.2 if the XL print is 20% larger.

If a product has no print area entered, ink cannot be measured for it and
the order falls back to the ordinary bill of materials (see step 6).

### 4. The rate turns square centimetres into millilitres

Each ink channel has `ml_per_cm2`: how many millilitres one square
centimetre of **solid** coverage of that ink consumes. It is set on the
Customization Pricing screen under "Sublimation ink", one value per channel,
alongside the raw-material bottle that channel is linked to.

The default is 0.0025 ml/cm². Sanity check: an A4 sheet is about 624 cm²,
so a solid A4 fill of one colour is about 1.56 ml, which is in the range a
desktop sublimation printer actually lays down.

**The default is a starting point, not a measurement.** Calibrating it is a
one-time procedure the shop can do with a kitchen scale:

1. Weigh the bottle (or cartridge).
2. Print a solid square of known size, say 10 × 10 cm = 100 cm², in one
   colour.
3. Weigh the bottle again. Sublimation ink is close to 1 g/ml.
4. Rate = grams used ÷ 100. Enter it for that channel.

Once calibrated, every future order's figure is anchored to the shop's own
printer.

### 5. Per item, per line, per order

For each channel that is linked to a bottle:

```
quantity = round(coverage × area × rate, 4)   // ml, one item
```

Stock is kept to four decimal places specifically so these small figures
survive; a trace of cyan in a mostly-red logo can be 0.0012 ml and must not
round to zero.

The per-item figure is multiplied by the quantity ordered, and figures for
the same bottle across different lines in the order are added together
before anything touches stock. The order screen shows the working next to
each ink bottle, in this form:

```
Shirt: 4.2% Magenta coverage of 900 cm² (measured from the print, size M) × 0.0025 ml/cm² × 3 items
```

so the reviewer can see coverage, area, rate and quantity, not just a total.
If a channel is linked but the artwork has none of that colour, the line
still appears with zero and says so.

### 6. Where it lands in inventory

Ink is handled by the same stock service as every other material
(`OrderStockService`):

- **Approval reserves.** When the admin approves the order the millilitres
  are taken off the bottle's stock and journalled in the raw material
  movements ledger with the reason "Reserved for an approved order". Stock
  goes down so a second order cannot be promised the same ink, but it is
  not yet counted as used.
- **Production consumes.** When staff move the order to processing the
  reservation is re-recorded as "Consumed in production". The stock does
  not move twice; the movement is reclassified so the materials report's
  Consumed column is right.
- **Cancellation reverses** whichever of the two the order reached, with a
  "Reversal" movement pointing at the one it undoes.

Approval is refused if the reserved amount would push a bottle negative;
the shortage is shown and the admin restocks first.

Where ink is measured, any ink line that the per-element bills of materials
would have drawn for that item is skipped, so a bottle is never drawn twice
for one print. Where ink cannot be measured (no print area on the product,
or no channel linked to a bottle), the system falls back to those bills of
materials exactly as before, so nothing is ever left undeducted.

### 7. The reviewer can correct it

On the review screen, before approving, the admin can override the
millilitres of any material the order draws. The override can only adjust a
line the order already has; it cannot add a material. This is the human
check: the flat print is shown per panel beside the figures, and if the
reviewer knows the printer will behave differently (a heavier pass, a test
print) they enter what the shop will actually use.

## Measured vs estimated

Every design saved since the flat-print export was added is **measured**. A
design saved before that has only its recipe (the list of elements and their
positions), so the system **estimates** coverage from it:

- a shape is a flat fill of its colour over its geometric area (a circle of
  radius 50 px × scale, a bar of 200 × 20 px × scale);
- text is set at 48 px × scale, each glyph about 0.6 em wide, a bold face
  inking about a third of its box;
- an uploaded image is measured pixel by pixel like a print if its data is
  in the recipe, otherwise assumed to be a mid-density full-colour image.

The order screen labels the source: "measured from the print" or
"estimated from the design". The estimate is coarser but still knows that
red text takes no cyan and that a bigger shape takes more ink. It exists
only as a fallback for legacy designs; new saves never use it.

## Why it was built this way

**Why not a fixed amount per element?** That is what the earlier version
did: a fixed millilitre figure per line of text, per shape, per image. It is
a bill of materials guessing at a picture. It charges the same ink for a
single dot and for a full-panel photo, charges cyan for red text, and cannot
be verified against anything. Since the price already varies with the size
of the design, the material cost has to vary the same way, and measuring is
the only way to make it do so honestly.

**Why CMYK and not RGB?** Because that is what the printer consumes. Stock
is kept per bottle, and the bottles are cyan, magenta, yellow and black. The
figure the system produces has to be in the same units as the thing on the
shelf.

**Why fraction of the panels and not of the canvas?** Because the number it
is multiplied by, the product's print area, describes the panels. Measuring
against the whole atlas would understate coverage on every garment.

**Why a configurable rate instead of a printer profile?** Real ink laydown
depends on the printer, its driver settings, the paper and the density
profile. None of that is knowable from software. A single number per
channel that the shop calibrates by weighing is simple, transparent and
correct for their machine, and it lives on an admin screen rather than in
code.

**Why keep the flat print?** So the number is auditable. The reviewer sees
the exact image the figure came from, cropped to each panel, next to the
percentages.

**Why reserve on approval and consume on production?** So stock cannot be
double-promised between approval and production, while the consumption
report only counts material that was actually used. This is the standard
two-step used for every material, and ink follows it.

## Known limits, stated honestly

- The RGB to CMYK conversion is the plain formula, not an ICC colour
  profile. Real printer drivers use profiles that shift the split (for
  example, adding black under dark colours to save CMY). The rate
  calibration absorbs the average effect, and the reviewer override covers
  individual cases.
- Coverage is measured at 256 × 256 pixels, a sixteenth of the studio's
  resolution. Resampling averages neighbouring pixels rather than dropping
  them, so a fraction of the area is preserved; only a hairline thinner than
  four studio pixels would blur into its surroundings, and it still counts
  through its opacity.
- Ink used for nozzle checks, head cleaning and test prints is not tied to
  an order. Staff record it as a manual stock movement (reason: Correction
  or Damaged).
- The size factors ship as 1.0 and must be set by the admin if the shop's
  larger sizes genuinely print larger.
- The default rate must be calibrated once. Until it is, the figures are
  proportionally right (a bigger or denser design always draws more) but not
  absolutely right.

## Common questions

**"How did you get the ink consumption?"**
It is measured from the artwork. When a design is saved the studio exports
the artwork alone as an image. The server converts each pixel to the four
printer inks, computes what fraction of the printable area each ink covers,
and multiplies that by the product's printable area in cm² and a rate in
ml per cm² that the shop calibrates by weighing a bottle before and after a
test print.

**"Where do the numbers come from? Are they hard-coded?"**
Three inputs, all admin-managed: the product's printable area (product
form), the size factor (Customization Pricing), and the ml/cm² rate per ink
(Customization Pricing, Sublimation ink). The coverage is computed from the
customer's image. Nothing per order is typed in.

**"How accurate is it?"**
Relative accuracy is exact to the pixel: twice the artwork is twice the ink,
and a red logo draws no cyan. Absolute accuracy depends on the calibrated
rate; once the shop calibrates it with a test print and a scale, the figure
matches their printer. The reviewer can also correct any line before
approving, and the flat print is shown beside the figures.

**"What if the product has no print area, or the inks are not linked?"**
The system falls back to the bill of materials for that item, the same
per-element quantities the old version used. Nothing goes undeducted.

**"Can it double-deduct?"**
No. Where ink is measured, any ink line from the bills of materials for that
item is skipped. Every movement is journalled with a reason and, for
reversals, a link to the movement it undoes.

**"Where can I see it working?"**
Open a pending order with a customized item. The review screen shows each
panel's artwork, and under Materials each ink bottle shows its coverage
percentage, the area, the rate, the quantity, and whether it was measured
or estimated. After approval, the raw material's Usage Log shows the reserved
millilitres against that order number.

## Where the code lives

| Piece | File |
|---|---|
| Flat-print export and printable share | `backend/public/js/customizer/rendering.js` (`renderDesignOnlyOnCanvas`, `printableFraction`) |
| Measuring on save | `backend/app/Http/Controllers/Concerns/SavesCustomDesign.php` (`recordPrint`) |
| Coverage, estimate, millilitres | `backend/app/Services/InkEstimator.php` |
| Channels, rates, bottle links | `backend/app/Models/InkChannel.php` |
| Print area per size | `Product::printAreaFor`, `CustomizationRate::areaFactorForSize` |
| Reserve, consume, reverse, fallback | `backend/app/Services/OrderStockService.php` |
| Reviewer override | `backend/app/Http/Controllers/Admin/OrderController.php` (`review`) |
| Admin screen for rates | Customization Pricing → Sublimation ink |
