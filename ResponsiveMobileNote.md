# Responsive / Mobile Notes

Conventions for making admin (and staff/customer) Blade pages responsive on
mobile. Apply these patterns page-by-page so the whole system behaves
consistently. Examples live in:

- `backend/resources/views/admin/dashboard/dashboard.blade.php`
- `backend/resources/views/admin/product/products.blade.php`

---

## 1. Canonical breakpoint

Use **`991.98px` (Bootstrap `lg`)** as the single "mobile" breakpoint.
It matches when the fixed 280px sidebar switches to the offcanvas drawer and
when content columns stack, so everything flips together.

```css
@media (max-width: 991.98px) { /* mobile / stacked */ }
```

Optional finer tier (rarely needed): `767.98px` for phone-only tweaks.

> **Check the scaffolding first.** Some pages (e.g. Stock Monitoring) were
> missing the `#adminSidebarOffcanvas` block, so the navbar hamburger had
> nothing to open on mobile. Every page must include, right after the
> `.sidebar-spacer`:
>
> ```html
> <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="adminSidebarOffcanvas"
>     aria-labelledby="adminSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
>     <div class="offcanvas-body p-0 overflow-hidden">@include('admin.partials.sidebar')</div>
> </div>
> ```

---

## 2. Page & card spacing

| Was | Use |
|---|---|
| `<main ... p-4>` | `p-3 p-md-4` |
| `row g-4` | `row g-3 g-md-4` |
| `card-body p-4` | `card-body p-3 p-md-4` |
| `card-header ... p-4 pb-0` | `p-3 p-md-4 pb-0` |

Fixed `p-4` wastes ~half the usable width on phones.

---

## 3. Typography scaling

Big KPI / figure numbers — make them fluid instead of a fixed `h3`:

```css
.stat-value {
    font-size: clamp(1.2rem, 1rem + 1.4vw, 1.75rem);
    line-height: 1.2;
    overflow-wrap: anywhere;
}
```

Inside the mobile media query, scale card text down (use `!important` to beat
inline `font-size` styles):

```css
.stat-card .card-body h6 { font-size: 0.6rem !important; }
.stat-card .card-body > p { font-size: 0.68rem !important; }
.stat-card .card-body .fs-4 { font-size: 1.05rem !important; }
.mini-stat .text-uppercase { font-size: 0.58rem !important; }
.mini-stat .fs-5 { font-size: 0.95rem !important; }
.card-header h5 { font-size: 1rem !important; }
.card-header p  { font-size: 0.72rem !important; }
```

**Status / analytics stat cards** (the clickable count cards on Orders,
Purchase Orders, Dashboard): hide the icon tile on mobile so the cramped
2-up card shows just label + count. Swap the icon box's `d-flex` for
`d-none d-lg-flex` in the markup (no CSS needed). Leave the columns alone —
they're already `col-6 col-md-4 col-xl…`.

---

## 4. Filter / action toolbars

Never use `d-flex flex-nowrap` for a toolbar — it crushes on mobile.
Rebuild as a Bootstrap grid that stacks:

```html
<form class="row g-2 align-items-center">
    <!-- search: dropdown on mobile, inline on desktop -->
    <div class="col-auto col-lg dropdown search-dd">
        <button type="button" class="btn btn-light rounded-2 d-lg-none search-toggle"
            data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Search">
            <i class="bi bi-search text-primary"></i>
        </button>
        <div class="dropdown-menu search-menu border-0 shadow p-2 p-lg-0">
            <!-- the real search input-group lives here -->
        </div>
    </div>

    <!-- all selects collapse behind ONE filter icon on mobile -->
    <div class="col-auto dropdown filter-dd">
        <button type="button" class="btn btn-light rounded-2 d-lg-none filter-toggle"
            data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Filters">
            <i class="bi bi-funnel text-primary"></i>
        </button>
        <div class="dropdown-menu filter-menu border-0 shadow p-2 p-lg-0">
            <div class="d-flex flex-column flex-lg-row gap-2">
                <!-- the <select> w-100 elements live here -->
            </div>
        </div>
    </div>

    <div class="col-auto d-flex gap-2">
        <a class="btn btn-light rounded-2 flex-shrink-0" title="Reset"> <i ...></i> </a>
        <button class="btn btn-primary ... px-3" title="Export">
            <i class="bi bi-download small"></i>
            <span class="small fw-bold d-none d-lg-inline">Export</span>
        </button>
    </div>
</form>
```

**Every control is `col-auto`** so on mobile the whole toolbar is a single
line of icon buttons: `[🔍] [funnel] [↻] [⬇] [＋]`. Only the search is
`col-auto col-lg` so it grows to fill the row on desktop.

Rules:

- **Action buttons become icon-only on mobile**: wrap the label in
  `<span class="... d-none d-lg-inline">` and add a `title="..."` for the
  tooltip/accessibility. If the icon has a right margin (`me-1`/`me-2`),
  change it to `me-lg-1`/`me-lg-2` so the icon stays centred when the label
  is hidden. This applies to any action row (toolbars **and** detail-page
  header rows like Back / Mark as… / Cancel / Print).
- **Search collapses into the icon on mobile** via a Bootstrap dropdown that
  is forced to render *inline & static* on desktop with CSS — one input, no
  duplicate `name="search"` fields:

The same technique applies to the **filter dropdown** holding the selects:

```css
@media (min-width: 992px) {
    .search-dd .dropdown-menu.search-menu,
    .filter-dd .dropdown-menu.filter-menu {
        position: static !important; display: block !important;
        float: none; width: 100%; margin: 0;
        padding: 0 !important; border: 0 !important;
        box-shadow: none !important; background: transparent;
    }
    .filter-dd .filter-menu .form-select { width: auto !important; }
}
@media (max-width: 991.98px) {
    .search-dd .dropdown-menu.search-menu { width: min(82vw, 360px); }
    .filter-dd .dropdown-menu.filter-menu { width: min(82vw, 320px); }
}
```

- Use `data-bs-auto-close="outside"` so typing/selecting doesn't close the
  dropdown.
- A `filter-dd` can hold more than selects (e.g. a date-range
  input-group + a select, as on the Reports pages). When it does, widen the
  mobile menu — `width: min(92vw, 420px)` instead of `min(82vw, 320px)` —
  so the two date pickers aren't crushed.
- Selects: drop `w-auto`, use `w-100`; wrap them in
  `d-flex flex-column flex-lg-row gap-2` inside the filter menu (stacked in the
  mobile popup, side-by-side inline on desktop).

---

## 5. Tables

Keep the existing `.table-responsive` wrapper (horizontal swipe scroll).
On mobile, **maximize** the scroll area instead of cramping columns.

> The table-card hook class is per-page but the rules are identical. The
> products page uses `.products-table-card`; every other page uses the
> generic **`.data-table-card`** (categories, raw-materials, …). Pick one
> consistent name per page and apply the block below to it. For a free-text
> column (e.g. a description) add a cell class that re-enables wrapping with
> a `max-width` so it doesn't stretch the row into one giant line.

```css
@media (max-width: 991.98px) {
    .products-table-card {           /* class on the table's <div class="card ..."> */
        margin-left: -0.75rem;       /* cancel the container-fluid gutter */
        margin-right: -0.75rem;
        border-radius: 0 !important; /* edge-to-edge looks intentional */
    }
    .products-table-card .table { min-width: 860px; }   /* readable columns */
    /* stop cells wrapping/shrinking — swipe instead */
    .products-table-card .table th,
    .products-table-card .table td { white-space: nowrap; }
    /* keep the first/primary column (e.g. Product Info) from collapsing */
    .products-table-card .table th:first-child,
    .products-table-card .table td:first-child { min-width: 240px; }
}
```

`white-space: nowrap` is the key fix for columns that "shrink" — without it
the browser wraps text to fit the viewport instead of letting the table
overflow into the horizontal scroll.

### 5a. Pagination footer

Put the pager **inside `.table-responsive`, after `</table>`** — don't give
it its own scrollbar. It then rides the table's single horizontal scroll, so
there's exactly one scrollbar and the bar moves with the table:

```html
<div class="table-responsive">
    <table class="table …">…</table>

    <!-- inside .table-responsive, after the table -->
    <div class="pagination-bar border-top d-flex justify-content-between align-items-center gap-2 p-3">
        <div class="d-flex align-items-center gap-2 flex-shrink-0"> … rows-per-page + "Showing…" (text-nowrap) … </div>
        <nav class="flex-shrink-0"> {{ $items->links() }} </nav>
    </div>
</div>
```

```css
@media (max-width: 991.98px) {
    .pagination-bar {
        flex-wrap: nowrap;
        min-width: 860px;            /* SAME value as .table min-width */
    }
    .pagination-bar .pagination {    /* shrink Bootstrap's pager on mobile */
        --bs-pagination-padding-x: 0.5rem;
        --bs-pagination-padding-y: 0.25rem;
        --bs-pagination-font-size: 0.8rem;
        margin-bottom: 0;
    }
}
```

Keep `.pagination-bar`'s `min-width` identical to the table's so the bar
spans the full scroll width and stays aligned under the table. Add
`text-nowrap` to the "Showing…" span and `flex-shrink-0` to both groups so
the bar fills the width instead of wrapping.

### 5b. Grid (card) pages — no table

Some pages render a **card grid** instead of a table (e.g. Textures). The
§5 / §5a table rules don't apply. Instead:

- Responsive columns + gutters: `class="row g-3 g-md-4"` with
  `col-12 col-md-4 col-xl-3` — **single column on phone** (one card per
  row), 3-up tablet, 4-up desktop. Don't leave `col-md-3` alone (it makes
  tablets a cramped 4-up).
- Pagination stays its **own card** (there's no table to share a scroll
  with). Keep `d-flex flex-wrap`, add `pagination-bar` + `flex-shrink-0`
  groups, shrink the pager — **no `min-width`** (that's table-only) — and
  on mobile show **only the Per-Page control**: hide the
  "Showing … of …" count span with `d-none d-lg-inline`:

```css
@media (max-width: 991.98px) {
    .pagination-bar .pagination {
        --bs-pagination-padding-x: 0.5rem;
        --bs-pagination-padding-y: 0.25rem;
        --bs-pagination-font-size: 0.8rem;
        margin-bottom: 0;
    }
}
```

The outer `.d-flex.vh-100` has `overflow:hidden`, so a small negative-margin
miscalc is clipped — it won't create a page-level horizontal scrollbar.
Pagination footers should already use `d-flex flex-wrap` — keep that.

### 5c. Table inside grid cards (header + footer)

Some pages (e.g. Stock Monitoring) put a table in a card that also has a
header/footer and sits in a `row` of cards (`col-xl-6`). This is **not** a
table-only card, so **don't** apply the §5 edge-to-edge negative margins.
Instead:

- Stack the card header on mobile:
  `pt-3 pt-md-4 px-3 px-md-4 d-flex flex-column flex-md-row
  justify-content-between align-items-start align-items-md-center gap-2`,
  and `flex-shrink-0` on its action button.
- Give the inner table a hook class + the usual anti-squish rules, but
  **no edge-to-edge**:

```css
@media (max-width: 991.98px) {
    .inv-table { min-width: 560px; }              /* tune per column count */
    .inv-table th, .inv-table td { white-space: nowrap; }
    .inv-table th:first-child,
    .inv-table td:first-child { min-width: 200px; }
}
```

- `col-xl-6` already stacks 1-up below xl — leave it; gutter `row g-4` →
  `g-3 g-md-4`. No paginator on such pages → nothing for §5a.

---

## 6. Modals

### 6a. Shrink type *and* spacing on mobile

```css
@media (max-width: 991.98px) {
    .modal-title { font-size: 1rem; }
    .modal-body  { font-size: 0.85rem; }
    .modal .form-label, .modal .form-control, .modal .form-select,
    .modal .input-group-text, .modal .btn,
    .modal small, .modal .small { font-size: 0.8rem; }

    /* spacing — not just fonts */
    .my-modal .modal-dialog { margin: 0.5rem; }          /* more usable width */
    .my-modal .modal-body .p-4 { padding: 1rem !important; }
    .my-modal-header, .my-modal-footer { padding: 14px 16px; }
    .my-modal .row.g-3 { --bs-gutter-y: 0.5rem; }
    .my-modal .ratio { max-width: 200px; margin-inline: auto; } /* cap big image previews */
}
```

### 6b. Fix the `aria-hidden` console warning (do this once, globally)

> Blocked aria-hidden on an element because its descendant retained focus…
> Ancestor with aria-hidden: `<div.modal …>`

Cause: Bootstrap 5.3 sets `aria-hidden="true"` on the modal while a control
inside it (commonly a `data-bs-dismiss` Cancel button) still has focus.

Fix lives in **`layout/app.blade.php`** (one listener; the event bubbles to
`document`, so it covers every modal in the app — don't duplicate per page):

```js
document.addEventListener('hide.bs.modal', function (event) {
    var modal = event.target;
    if (modal && modal.contains(document.activeElement)) {
        document.activeElement.blur();
    }
});
```

### 6c. Footers with multiple buttons & fixed-height panels

Font-shrinking (§6a) isn't enough for richer modals — also fix the *layout*:

- **Footer with 2+ action buttons** (e.g. Close / Reject / Approve) won't
  fit one phone row. Stack them full-width:

```css
@media (max-width: 991.98px) {
    .x-modal-footer {
        flex-direction: column;
        align-items: stretch;
    }
    .x-modal-footer > .btn,
    .x-modal-footer .btn-group-wrapper { width: 100%; flex-direction: column; }
    .x-modal-footer .btn { width: 100%; }
}
```

- **Fixed-height inner panels** (3D viewer, code/recipe box, side-by-side
  `col-md-8 / col-md-4`) stack on mobile into an enormous modal. They
  usually have an inline `style="height:…"`, so override needs a hook
  class/id + `!important`, and shrink to phone heights:

```css
@media (max-width: 991.98px) {
    #viewer3d { height: 280px !important; }
    .recipe-box { height: 220px !important; }
}
```

- Also drop `.modal-body.p-4` to `padding: 1rem !important;` and turn any
  inner `row g-4` into `g-3 g-md-4`.

- **Tables inside a modal** are usually wrapped in
  `table-responsive border rounded-3 overflow-hidden`. The `.overflow-hidden`
  utility is `!important` and **kills** `table-responsive`'s `overflow-x:auto`,
  so on a narrow modal the table clips/squishes instead of scrolling. Add a
  hook class to the wrapper + table and restore it on mobile:

```css
@media (max-width: 991.98px) {
    .modal-table-scroll { overflow-x: auto !important; } /* beat overflow-hidden */
    .modal-table { min-width: 520px; }                   /* tune per columns */
    .modal-table th, .modal-table td { white-space: nowrap; }
}
```

---

## 7. Charts (ApexCharts)

- Don't set the container height with an inline `style="min-height:..."` —
  inline styles beat media queries. Use a class/`#id` rule instead.
- Add a `responsive` breakpoint to the chart options: shorter height, legend
  moved to `bottom`, fewer `xaxis.tickAmount`, smaller `10px` label fonts.

```js
responsive: [{
    breakpoint: 768,
    options: {
        chart: { height: 240 },
        legend: { position: 'bottom', horizontalAlign: 'center', fontSize: '11px' },
        xaxis: { tickAmount: 4, labels: { style: { fontSize: '10px' } } }
    }
}]
```

---

## 8. Lists

Long lists (e.g. "Top Products"): on mobile cap height and scroll inside the
card so they don't push the rest of the page down. Tighten row padding and
add a thin custom scrollbar.

---

## Per-page checklist

- [ ] `<main>` padding → `p-3 p-md-4`
- [ ] Row gutters → `g-3 g-md-4`
- [ ] Card bodies/headers → `p-3 p-md-4`
- [ ] Toolbar → `row g-2`; search dropdown; icon-only buttons w/ `title`
- [ ] Large numbers → `.stat-value` clamp
- [ ] Card text scaled in the `991.98px` media query
- [ ] Table card edge-to-edge + `min-width`
- [ ] Pagination placed inside `.table-responsive` (shares table scroll) with matching `min-width`
- [ ] Modal fonts reduced
- [ ] Charts: no inline height + `responsive` config
- [ ] Long lists: scroll-capped
