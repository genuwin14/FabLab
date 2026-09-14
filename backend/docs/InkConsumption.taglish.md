# Paano Kinukwenta ang Ink Consumption

Step by step kung paano nakukuha ng system yung millilitres ng ink na
ibabawas sa inventory, bakit ganun ang ginawa, at yung mga sagot sa mga
madalas itanong tungkol dito.

## Yung one-sentence answer

Walang fixed na ink amount per order o per element ang system.
**Sinusukat niya yung mismong artwork ng customer**: kinukwenta kung ilang
percent ng printable area ng product ang natatakpan ng bawat isa sa apat
na ink ng printer, tapos mini-multiply sa printable area ng product sa
napiling size, tapos mini-multiply ulit sa calibrated na rate na
millilitres per square centimetre.

```
ink per item (ml) = coverage  ×  printable area (cm²)  ×  rate (ml/cm²)
ink per line (ml) = ink per item × quantity na in-order
```

Ginagawa ito per ink channel: Cyan, Magenta, Yellow, at Black (CMYK), yung
apat na cartridge ng sublimation printer.

## Yung pipeline, step by step

### 1. Nag-e-export ang studio ng "flat print" pag na-save ang design

Yung 3D design studio, dinodrawing niya yung design ng customer sa isang
1024 × 1024 pixel na canvas na tinatawag na atlas. Bawat printable panel ng
product (sa shirt: front, back, sleeves) may sariling rectangle sa canvas
na yun.

Pag nag-save ang customer o nag-add to cart, gumagawa ang studio ng
pangalawang kopya ng canvas na **artwork lang ang laman**: yung shapes,
text, at uploaded images na nilagay ng customer. Wala yung damit, wala
yung kulay nito, wala yung texture, transparent yung background. Yun yung
flat print. Pinapadala ito sa server kasama ng:

- kung ilang percent ng canvas ang printable (total area ng mga panel bilang
  fraction ng buong atlas), at
- yung rectangle ng bawat panel, para maipakita sa reviewer sa order screen
  kung ano exactly ang nakaprint sa front, sa back, etc.

Bakit hindi kasama yung finish: yung kulay o texture ng blank ay dyed o may
sariling printed sheet, at hiwalay ang costing nun. Yung dinrawing lang ng
customer ang dumadaan sa printer.

### 2. Sinusukat ng server yung coverage per ink

Binubuksan ng server (`InkEstimator::measure`) yung flat print, nire-resample
sa 256 × 256 pixels para mabilis, tapos dinadaanan lahat ng pixel:

- Pag fully transparent ang pixel, skip. Walang naiprint dun.
- Yung ibang pixel, kino-convert yung RGB color sa CMYK gamit yung standard
  formula (K = 1 − max(R,G,B); C = (1 − R − K)/(1 − K); ganun din sa M at Y).
- Yung value ng bawat channel, mini-multiply sa opacity ng pixel tapos
  dinadagdag sa running total ng channel na yun.

Yung totals, dini-divide sa bilang ng **printable** pixels (canvas size ×
printable share na galing sa studio), hindi sa buong canvas. Sa atlas ng
shirt, karamihan ay seams at gaps na hindi naman naiinkan, kaya kailangan
sa mga panel i-measure para tugma yung fraction sa printable area ng
product.

Ang result: apat na fraction na 0 hanggang 1, isa per channel. Nase-save
ito sa design bilang `ink_coverage`, at yung flat print mismo naka-save sa
disk para makita ng reviewer kung ano yung sinukat.

Dalawang bagay na dapat malaman dito:

- **Walang ink ang white.** Sa CMYK, white ay 0,0,0,0. Tama yun para sa
  sublimation, kasi walang white ink; yung white ay yung substrate mismo na
  nakikita.
- **Walang cyan ang red na logo.** Pure red ay C 0, M 1, Y 1, K 0. Kaya
  Magenta at Yellow bottle lang ang babawasan. Hindi malalaman yun ng
  per-element na bill of materials kahit kailan.

### 3. Yung printable area, galing sa product at sa size

Bawat product may `print_area_cm2`, inilalagay ng admin sa product form.
Yun yung actual na sukat ng printable panels ng product sa square
centimetres. Ito yung physical anchor: fraction lang yung coverage, at ito
yung nagko-convert nun sa centimetres.

Naka-record sa recipe yung size na pinili ng customer. Yung
`print_area_factor` ng size (Customization Pricing screen) ang nag-scale sa
area. Medium ay 1.0, yung sariling figure ng product. Lahat ng factor ay
naka-1.0 sa ngayon; pwedeng i-set ng admin, halimbawa, XL sa 1.2 kung 20%
mas malaki talaga ang print sa XL.

Kung walang print area na nilagay sa product, hindi ma-measure ang ink para
dun, at babalik ang order sa ordinary na bill of materials (tingnan step 6).

### 4. Yung rate ang nagko-convert ng square centimetres sa millilitres

Bawat ink channel may `ml_per_cm2`: ilang millilitres ang nauubos ng isang
square centimetre na **solid** na coverage ng ink na yun. Nase-set ito sa
Customization Pricing screen sa ilalim ng "Sublimation ink", isang value
per channel, katabi ng raw-material bottle na naka-link sa channel na yun.

Default ay 0.0025 ml/cm². Sanity check: yung A4 sheet ay mga 624 cm², kaya
yung solid A4 fill ng isang kulay ay mga 1.56 ml, na nasa range ng actual
na inilalabas ng desktop sublimation printer.

**Starting point lang yung default, hindi measurement.** Isang beses lang
i-calibrate, kaya gamit ang kitchen scale:

1. Timbangin yung bottle (o cartridge).
2. Mag-print ng solid square na alam ang sukat, halimbawa 10 × 10 cm =
   100 cm², isang kulay lang.
3. Timbangin ulit yung bottle. Yung sublimation ink, malapit sa 1 g/ml.
4. Rate = grams na nagamit ÷ 100. Ilagay sa channel na yun.

Pag na-calibrate na, lahat ng future order naka-anchor na sa sariling
printer ng shop.

### 5. Per item, per line, per order

Para sa bawat channel na naka-link sa bottle:

```
quantity = round(coverage × area × rate, 4)   // ml, isang item
```

Naka-four decimal places ang stock para mismong ma-survive ng maliliit na
figure; yung konting cyan sa mostly-red na logo pwedeng 0.0012 ml at hindi
dapat maging zero.

Yung per-item figure, mini-multiply sa quantity na in-order, at yung mga
figure para sa parehong bottle sa iba't ibang line ng order ay
pinagsasama-sama bago galawin ang stock. Pinapakita ng order screen yung
computation sa tabi ng bawat ink bottle, ganito ang porma:

```
Shirt: 4.2% Magenta coverage of 900 cm² (measured from the print, size M) × 0.0025 ml/cm² × 3 items
```

para makita ng reviewer yung coverage, area, rate at quantity, hindi lang
yung total. Kung naka-link ang channel pero walang ganung kulay sa artwork,
lalabas pa rin yung line na zero at sasabihin nito yun.

### 6. Saan ito pumapasok sa inventory

Same stock service ang humahawak sa ink gaya ng lahat ng material
(`OrderStockService`):

- **Approval = reserve.** Pag in-approve ng admin ang order, binabawas yung
  millilitres sa stock ng bottle at nilalagay sa raw material movements
  ledger na may reason na "Reserved for an approved order". Bumababa yung
  stock para hindi ma-promise sa ibang order yung same ink, pero hindi pa
  ito counted as used.
- **Production = consume.** Pag nilipat ng staff sa processing ang order,
  nire-record ulit yung reservation bilang "Consumed in production". Hindi
  gumagalaw ulit yung stock; nire-reclassify lang yung movement para tama
  yung Consumed column ng materials report.
- **Cancellation = reverse.** Kung alin man sa dalawa ang naabot ng order,
  binabaliktad, may "Reversal" movement na nakaturo sa movement na
  binabawi nito.

Hindi papayagan ang approval kung magiging negative ang bottle dahil sa
reserve; ipapakita yung shortage at kailangang mag-restock muna ang admin.

Kung measured ang ink, nilalaktawan yung kahit anong ink line na
ibabawas sana ng per-element na bills of materials para sa item na yun,
kaya hindi kailanman doble ang bawas sa isang bottle para sa isang print.
Kung hindi ma-measure ang ink (walang print area ang product, o walang
channel na naka-link sa bottle), babalik ang system sa bills of materials
gaya ng dati, kaya walang hindi nababawas.

### 7. Pwedeng i-correct ng reviewer

Sa review screen, bago mag-approve, pwedeng i-override ng admin yung
millilitres ng kahit anong material na kukunin ng order. Yung override,
pwede lang mag-adjust ng line na nasa order na; hindi pwedeng magdagdag ng
material. Ito yung human check: nakapakita yung flat print per panel sa
tabi ng mga figure, at kung alam ng reviewer na iba ang gagawin ng printer
(mas heavy na pass, may test print), ilalagay niya yung actual na gagamitin
ng shop.

## Measured vs estimated

Lahat ng design na na-save mula nung nadagdag yung flat-print export ay
**measured**. Yung design na na-save bago nun, recipe lang ang meron (yung
list ng elements at positions nila), kaya **ini-estimate** ng system yung
coverage mula dun:

- ang shape ay flat fill ng kulay niya sa geometric area niya (circle na
  radius 50 px × scale, bar na 200 × 20 px × scale);
- ang text ay naka-48 px × scale, bawat glyph mga 0.6 em ang lapad, at ang
  bold face ay mga one-third ng box niya ang naiinkan;
- ang uploaded image ay sinusukat pixel by pixel gaya ng print kung nasa
  recipe yung data nito, kung hindi ay ina-assume na mid-density
  full-colour image.

Nilalabel ng order screen yung source: "measured from the print" o
"estimated from the design". Mas magaspang yung estimate pero alam pa rin
niyang walang cyan ang red text at mas malaki ang ink ng mas malaking
shape. Fallback lang ito para sa mga lumang design; hindi ito ginagamit
ng mga bagong save.

## Bakit ganito ang pagkakagawa

**Bakit hindi fixed amount per element?** Yun yung ginawa ng lumang
version: fixed na millilitre figure per line ng text, per shape, per
image. Bill of materials yun na nanghuhula sa isang picture. Same ink ang
sinisingil sa isang tuldok at sa full-panel na photo, sinisingil ng cyan
ang red text, at walang paraan para i-verify. Dahil nagbabago na ang presyo
depende sa laki ng design, dapat ganun din ang material cost, at measuring
lang ang paraan para maging totoo yun.

**Bakit CMYK at hindi RGB?** Kasi yun ang kinokonsumo ng printer. Per
bottle ang stock, at cyan, magenta, yellow at black ang mga bottle. Dapat
same units yung figure ng system at yung nasa shelf.

**Bakit fraction ng panels at hindi ng canvas?** Kasi yung mini-multiply
dito, yung print area ng product, ang mga panel ang dini-describe nun. Kung
sa buong atlas i-measure, kulang ang coverage sa lahat ng damit.

**Bakit configurable na rate imbes na printer profile?** Yung actual na
ink laydown, depende sa printer, sa driver settings, sa papel, at sa
density profile. Wala nun ang malalaman ng software. Isang number lang per
channel na kina-calibrate ng shop sa pamamagitan ng pagtimbang ay simple,
transparent, at tama para sa machine nila, at nasa admin screen ito hindi
sa code.

**Bakit itinatabi yung flat print?** Para auditable yung number. Nakikita
ng reviewer yung exact na image na pinanggalingan ng figure, naka-crop per
panel, katabi ng mga percentage.

**Bakit reserve sa approval at consume sa production?** Para hindi
ma-double-promise ang stock sa pagitan ng approval at production, habang
yung consumption report ay material lang na actual na nagamit ang
binibilang. Ito yung standard na two-step para sa lahat ng material, at
sinusunod ito ng ink.

## Mga limitasyon, aminin nang diretso

- Yung RGB to CMYK conversion ay yung plain formula, hindi ICC colour
  profile. Yung tunay na printer driver may profile na nagbabago ng split
  (halimbawa, dinadagdagan ng black sa ilalim ng dark colours para
  makatipid sa CMY). Yung rate calibration ang sumisipsip ng average effect
  nun, at yung reviewer override ang bahala sa individual cases.
- Sinusukat ang coverage sa 256 × 256 pixels, one-sixteenth ng resolution
  ng studio. Yung resampling, ina-average ang magkakatabing pixel hindi
  tinatanggal, kaya napapanatili yung fraction ng area; yung hairline lang
  na mas manipis sa apat na studio pixel ang mabu-blur sa paligid niya, at
  nabibilang pa rin ito sa opacity nito.
- Yung ink na nagamit sa nozzle check, head cleaning at test print ay hindi
  naka-tie sa order. Nire-record ito ng staff bilang manual stock movement
  (reason: Correction o Damaged).
- Naka-1.0 ang size factors at kailangang i-set ng admin kung mas malaki
  talaga ang print ng mas malalaking size ng shop.
- Kailangang i-calibrate nang isang beses yung default rate. Hangga't hindi
  pa, proportionally tama ang figures (mas malaki o mas dense na design ay
  laging mas malaki ang ink) pero hindi pa absolutely tama.

## Mga madalas itanong

**"Paano niyo nakuha yung ink consumption?"**
Sinusukat mula sa artwork. Pag na-save ang design, ini-export ng studio
yung artwork lang bilang image. Kino-convert ng server ang bawat pixel sa
apat na printer ink, kinukwenta kung ilang percent ng printable area ang
natatakpan ng bawat ink, at mini-multiply yun sa printable area ng product
sa cm² at sa rate na ml per cm² na kina-calibrate ng shop sa pagtimbang ng
bottle bago at pagkatapos ng test print.

**"Saan galing yung mga number? Hard-coded ba?"**
Tatlong input, lahat admin-managed: printable area ng product (product
form), size factor (Customization Pricing), at ml/cm² rate per ink
(Customization Pricing, Sublimation ink). Yung coverage, kinukwenta mula sa
image ng customer. Walang tina-type per order.

**"Gaano ka-accurate?"**
Exact to the pixel yung relative accuracy: doble ang artwork, doble ang
ink, at walang cyan ang red logo. Yung absolute accuracy, depende sa
calibrated rate; pag na-calibrate na ng shop gamit ang test print at
timbangan, tugma na sa printer nila. Pwede ring i-correct ng reviewer ang
kahit anong line bago mag-approve, at nakapakita yung flat print sa tabi
ng mga figure.

**"Paano kung walang print area ang product, o hindi naka-link ang inks?"**
Babalik ang system sa bill of materials para sa item na yun, yung same
per-element quantities na ginamit ng lumang version. Walang hindi
nababawas.

**"Pwede bang ma-double-deduct?"**
Hindi. Kung measured ang ink, nilalaktawan yung kahit anong ink line mula
sa bills of materials para sa item na yun. Bawat movement naka-journal na
may reason at, para sa reversal, may link sa movement na binabawi nito.

**"Saan ko makikita na gumagana?"**
Buksan ang pending order na may customized item. Pinapakita ng review
screen ang artwork ng bawat panel, at sa ilalim ng Materials, bawat ink
bottle nagpapakita ng coverage percentage, area, rate, quantity, at kung
measured o estimated. Pagkatapos ng approval, sa Usage Log ng raw material
makikita yung reserved millilitres laban sa order number na yun.

## Saan nakalagay yung code

| Piece | File |
|---|---|
| Flat-print export at printable share | `backend/public/js/customizer/rendering.js` (`renderDesignOnlyOnCanvas`, `printableFraction`) |
| Pag-measure sa save | `backend/app/Http/Controllers/Concerns/SavesCustomDesign.php` (`recordPrint`) |
| Coverage, estimate, millilitres | `backend/app/Services/InkEstimator.php` |
| Channels, rates, bottle links | `backend/app/Models/InkChannel.php` |
| Print area per size | `Product::printAreaFor`, `CustomizationRate::areaFactorForSize` |
| Reserve, consume, reverse, fallback | `backend/app/Services/OrderStockService.php` |
| Reviewer override | `backend/app/Http/Controllers/Admin/OrderController.php` (`review`) |
| Admin screen para sa rates | Customization Pricing → Sublimation ink |
