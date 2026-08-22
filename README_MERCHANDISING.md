# Merchandising Trace — README

`mestiaque/merchandising-trace` is an independent Laravel package (namespace
`ME\MerchandisingTrace`) implementing a full garments-ERP Merchandising
module: Inquiry → Style Development → Sample → BOM → Costing → Sales
Contract → T&A (Time & Action) → Material Booking → Production Handover
Bridge → Shipment/Documentation/Buyer Communication → Dashboards & Reports.

It was built from scratch against the spec at `work/merchent.md` (16
phases), with **no code carried over** from any earlier `mestiaque/merchandising`
package. It has its own git history and its own composer identity.

## 1. Install

Add as a path (or VCS) repository in the host app's `composer.json` and
require it:

```bash
composer require mestiaque/merchandising-trace:dev-main
```

The service provider (`ME\MerchandisingTrace\MerchandisingServiceProvider`)
auto-registers via Laravel package discovery. It:

- loads its own routes (`admin/merchandising-trace/*`), views, translations,
  migrations;
- merges its sidebar entries into the host's existing menu config and its
  permission definitions into the host's permission config — no host files
  are edited;
- registers two scheduled console commands from inside its own
  `boot()` (`$this->app->booted(...)`), again without touching the host's
  `Kernel`.

Run migrations:

```bash
php artisan migrate
```

Seed the masters and default T&A template (safe to re-run — every seeder is
idempotent):

```php
(new \ME\MerchandisingTrace\Database\Seeders\SampleTypeSeeder())->run();
(new \ME\MerchandisingTrace\Database\Seeders\TrimsItemSeeder())->run();
(new \ME\MerchandisingTrace\Database\Seeders\DefaultTnaTemplateSeeder())->run();
(new \ME\MerchandisingTrace\Database\Seeders\DefaultDocumentTemplateSeeder())->run();
```

## 2. Permissions

Every module is gated by `spatie/laravel-permission` permission strings of
the form `<module_key>.<action_key>`, e.g. `merch_sales_contract.edit`,
`merch_tna.override_pcd`, `merch_production_handover.edit`. The full list
lives in `src/Config/permission.php` and is merged under
`config('permission')['modules']['MERCHANDISING_TRACE']` at boot.

Two permissions matter beyond ordinary CRUD:

| Permission | Effect |
|---|---|
| `merch_scope.view_all` | Bypasses row-level merchandiser scoping (see §4) — grant to managers, not line merchandisers. |
| `merch_tna.override_pcd` | Required (plus a mandatory reason) to push a PO to production when its PCD check has failed. |

## 3. Module walkthrough

1. **Masters** (`merch_buyer`, `merch_season`, ... `merch_department`) — simple
   code/name CRUD screens built on a shared `simple-master-index.blade.php`
   partial to avoid ~10x duplicated Blade boilerplate.
2. **Inquiry → Style** — `InquiryController::convertToStyle()` clones every
   relevant field onto a new `Style` row with zero re-entry. The inquiry
   list flags overdue rows (`Inquiry::isOverdue()`: still `open` past
   `order_confirmation_due_date`) with a badge.
2a. **Style Development** — beyond the basic-fields modal, every style has
   a tabbed detail page (`StyleController::show()`): **Images** (upload/
   remove, typed front/back/detail/embellishment/artwork), **Measurement
   Chart** (POM rows × size columns with tolerances), **Parts &
   Embellishment** (mandatory per spec — pick a part from production-
   trace's own `trc_parts` master, qty/garment, embellishment type,
   placement, critical flag), **Operations/SMV** (optional per-operation
   SMV breakdown, summed on the tab). The Parts & Embellishment rows are
   what the Production Handover Bridge (§7) later syncs into production's
   own `style_parts`.
3. **Sample** — full status workflow (`requested → in_progress → submitted →
   approved/rejected → resubmit`), revision chain via `parent_sample_id`.
   Submitting/approving a sample fires `SampleTnaSyncService` (§6).
4. **BOM** — versioned per style; `BomItem::netConsumption()` =
   `consumption × (1 + wastage% / 100)`.
5. **Costing** — versioned `CostSheet` with `calcCm()` / `calcTotalCost()` /
   `calcOfferPrice()` / `calcMarginPercent()`, all computed on demand from
   current inputs (never trusted from a stale stored total). PDF export in
   buyer format via `CostSheetController::pdf()`.
6. **Sales Contract / PO** — `SalesContractPo` carries the **effective-value
   rule** (§8 below) and a two-revision-slot pattern
   (`po_qty_revised_1/2`, `pcd_revised_1/2`, `shipment_revised_1/2`), each
   change logged to `mer_sales_contract_po_revisions` with a mandatory
   reason. Confirming a contract (`SalesContractController::confirm()`)
   generates one T&A plan per PO row **and** clones the document checklist
   (§9) in the same transaction. The PO grid supports Excel import
   (`SalesContractPoImportService`: Style No / Color Code / PO No / PO Qty
   required, Wash Type / PCD / Shipment / Unit Price / Ship Mode optional;
   unknown style/color rows are skipped with a reason, not fatal).
7. **T&A (the core module)** — see §5.
8. **Material Booking** — fabric/trims/accessory/packing bookings with a
   PI/LC/X-mill date trail and a consignment schedule (1st–4th) for fabric;
   every receipt/consignment/date fires `MaterialBookingTnaSyncService`
   (§6).
9. **Production Handover Bridge** — see §7.
10. **Shipment Plan / Documentation / Buyer Communication** — see §9.
11. **Dashboards & Reports** — see §10.

## 4. Row-level merchandiser scoping (§6 global rule 8)

`ME\MerchandisingTrace\Support\Scopes\ScopedToMerchandiser` is a global
Eloquent scope: a plain merchandiser only ever sees rows where
`merchandiser_id = auth()->id()`; a user holding `merch_scope.view_all`
bypasses it entirely. It's wired via `booted()` on the three
merchandiser-owned aggregate roots — `Inquiry`, `SalesContract`, `Sample`
— so it also silently narrows anything joined off them (e.g. a
`SalesContractPo::whereHas('salesContract', ...)` query). It no-ops outside
an authenticated request (console commands, scheduled jobs, tinker), so
background sync/report jobs still see everything.

**Deliberately not scoped:** `TnaPlan`, despite carrying its own
`merchandiser_id` (copied from the owning `SalesContract` at generation
time), does **not** carry this global scope. `TnaPlan` is accessed as a
relation (`$po->tnaPlan`) from many places outside the "browse my own
records" UI flows — `PreFlightChecklistService`, `ProductionHandoverService`,
`ReportService`, `DashboardService`, `PcdGateService` — and Eloquent
relations inherit a related model's global scopes. Scoping `TnaPlan`
directly risks silently breaking any of those for a role (e.g. spec's
own "Planning: reads T&A, receives production handover") that legitimately
needs cross-merchandiser T&A visibility without also being handed the
broader `merch_scope.view_all` bypass. This was tried and reverted after
weighing the risk against the remaining verification budget — the T&A
plans list instead offers an opt-in `?my_orders=1` filter
(`TnaPlanController::index()`), matching the spec's own filter list
("my orders only").

## 5. T&A template configuration

`TnaTemplate` + `TnaTemplateTask` define a reusable task set;
`TnaTemplateTask::resolveFor()` picks the right template for a PO (falls
back to the `is_default` template). `DefaultTnaTemplateSeeder` seeds the
45-task default template reproducing the six Excel groups (Sample, Wash,
Pilot, Fabric, Sewing Trims, Finishing Trims status).

Each template task carries:

- `offset_days` + `anchor_field` — `TnaPlanGenerationService::generateFor()`
  back-calculates `plan_date = anchor_date + offset_days` per PO (anchor is
  the PO's effective PCD unless the template overrides it);
- `is_mandatory` / `blocks_pcd` — feeds `PcdGateService::evaluate()`, which
  fails the gate on the **earliest incomplete blocking task** and stamps
  the plan's `responsible_dept_id` / `responsible_person_id` from that task
  — never typed by a user;
- `auto_source` / `auto_source_ref` — wires a task to whichever sync
  service owns it (`sample`, `material_booking`, or `none` for manual
  cells). Auto-synced cells are read-only in the T&A grid and rejected by
  `TnaPlanController::updateTask()` if edited via the API too.

Colour coding (`TnaTask::boardColor()`): green = done/approved, amber = due
soon, red = overdue, grey = not started, blue = N/A.

**Known simplification:** the T&A grid is built as grouped, colour-coded
tables with inline per-task forms rather than the frozen-column spreadsheet
described in §8.3 of the spec. Functionally equivalent, not visually
identical.

**Excel round-trip (test #15):** `TnaGridExportService` flattens every
matched plan into one row per PO / one column per task name (`PO No.` as
the row key); `TnaGridImportService` reads the same layout back and
bulk-updates `actual_date`/`value_text`/`value_number` — but any column
matching an `is_auto` task is silently skipped no matter what the file
contains, per §6 Rule 3. Buttons live on the T&A plans index page.

## 6. Auto-sync sources

| Source | Service | Trigger |
|---|---|---|
| Sample | `SampleTnaSyncService::syncFromSample()` | `SampleController::submit()/approve()/update()` |
| Material Booking (item receipt) | `MaterialBookingTnaSyncService::syncFromReceipt()` | `receiveItem()` |
| Material Booking (header dates) | `MaterialBookingTnaSyncService::syncFromBooking()` | `updateDates()` (PI/LC/X-mill) |
| Material Booking (consignment) | `MaterialBookingTnaSyncService::syncFromConsignment()` | `receiveConsignment()` |

All three match on `(auto_source, auto_source_ref)` scoped to the task's
style, so a receipt against one PO's booking only ever completes tasks for
that same style's plans.

## 7. Production Handover Bridge behaviour

`ProductionHandoverService::push()` is the merch→production bridge. It
writes into **production-trace's own tables**
(`trc_production_plans` / `trc_plan_styles` / `trc_plan_lines` /
`trc_plan_line_sizes`) through lightweight proxy models under
`Models/Bridge/*` that point at those tables by name only — **not a single
file inside production-trace is modified or depended on**. This is
deliberate: the standing constraint for this build was that production-trace
must never be touched.

Pre-flight checklist (`PreFlightChecklistService::evaluate()`) — every
check reads data that already lives in another module, nothing is typed
just for this screen:

| Check | Source |
|---|---|
| Order confirmed | `SalesContract.status = confirmed` |
| Cost sheet approved | `CostSheet.status = approved` for the style |
| BOM approved | `Bom.status = approved` for the style |
| PP sample approved | `Sample.status = approved` + `sampleType.code = PP1` |
| PCD result = pass | `TnaPlan.pcd_result` |
| Fabric in-house | a fabric `MaterialBooking` fully received |
| Sewing trims in-house | every mandatory "Sewing trims Status" task done/approved/na |
| Size breakdown complete | `SalesContractPo::sizeQtyMatchesEffectiveQty()` |
| Style parts defined | `trc_style_parts` has a row for the style |

A failing checklist blocks handover unless an override reason is supplied
(`merch_tna.override_pcd`), logged verbatim as a JSON snapshot on
`mer_production_handovers` alongside `pcd_status` (`pass` / `fail` /
`overridden`).

**One genuinely manual step, and why:** `trc_production_plans` requires
`product_id` / `size_group_id` against production-trace's *own* masters
(`trc_products` / `trc_size_groups`), which have no natural 1:1 mapping to
this package's `mer_product_types` / `mer_sizes` — they're different
domains (a merchandising "product type" like *Shirt* is not the same
concept as a production "Product" master). Rather than guess a mapping,
the handover screen asks once per style (cached on
`mer_styles.trc_product_id` / `trc_size_group_id`); every later handover
for that style is then fully automatic.

**Reverse feed:** `ProductionProgressSyncService` (scheduled hourly) mirrors
`trc_plan_line_sizes` rollups into `mer_po_production_progress`, a
merchandising-owned read-model, so dashboards never query
production-trace's tables directly.

**Guards implemented:** re-handover of an already-handed-over PO is
rejected outright *unless* the effective qty has increased, in which case
`ProductionHandoverService::pushDelta()` bumps the existing plan line's
`total_order_qty` and any grown size rows in place — never a duplicate
line (test #8, both halves). Rollback is only allowed while the plan
line's status is still `pending` (no cutting started).

Embellishment-flag propagation (test #9, §M11 step 5) is implemented:
`ProductionHandoverService::syncStyleParts()` writes
`requires_embroidery`/`requires_print` into `trc_style_parts` from each
part's `embellishment_type` (set on the style's **Parts & Embellishment**
tab, §M03) OR the PO-level flags (`emb_applique_ih`, `print_emb`,
`heat_seal_ih`), per the exact §14 field map.

## 8. The effective-value rule (§6 global rule 1)

Everywhere qty/PCD/shipment is used, resolve the **latest non-null
revision** — never read the base column directly:

```php
$po->effectiveQty();      // po_qty_revised_2 ?? po_qty_revised_1 ?? po_qty
$po->effectivePcd();      // pcd_revised_2 ?? pcd_revised_1 ?? pcd_date
$po->effectiveShipment();  // shipment_revised_2 ?? shipment_revised_1 ?? shipment_date
```

Every revision writes a row to `mer_sales_contract_po_revisions` with a
mandatory reason (`SalesContractPoController::revise()`).

## 9. Shipment / Documentation / Communication

- **Shipment Plan** (`ShipmentPlanService::forPo()`) — "planned" is the
  PO's own effective shipment date/qty plus `mer_shipment_bookings`
  (forwarder/booking/vessel + short-ship reason, merchandising-owned);
  "actual" is read live from production-trace's `trc_shipments` via the
  read-only `Bridge\TrcShipment` proxy, never duplicated locally. A
  shipment-date slip already goes through the existing PO revision
  mechanism (§8) — no separate flow was needed.
- **Documentation** — `DocumentChecklistService::generateFor()` clones a
  buyer's (or the global default) `DocumentTemplate` into
  `mer_order_documents` rows the moment a contract is confirmed.
  `SalesContract::close()` is blocked while any mandatory document isn't
  `uploaded`/`approved`.
- **Buyer Communication** — `mer_communication_logs`, a searchable
  thread-style log per style/PO.

## 10. Dashboards & Reports

`DashboardService::merchandiser($userId)` / `::management()` implement the
spec's two dashboards in full, except **"capacity vs booked qty by
month/factory"**, which is skipped — no factory capacity-planning data
source exists anywhere else in this build to draw it from.

`ReportService::run($key, $filters)` implements all 14 §M15 reports behind
one shape (`['headers' => [...], 'rows' => [...]]`), rendered/exported
through a single generic path (`ReportController`): an HTML table, a
Maatwebsite-Excel export (`GenericArrayExport`), and a dompdf print view.
Both export packages are already present in the host app's own
`composer.json` — used directly here rather than re-declared as this
package's own dependency, to avoid triggering another `composer update`
against the host (see "Troubleshooting" — that's exactly what caused a git
data-loss incident on production-trace earlier in this build).

Report #13 ("Order Profitability") is honest about scope: no
actual-cost-tracking module exists anywhere in this spec's build, so it
reports **planned** cost-sheet figures only and labels the actual-cost
column `"not tracked"` rather than fabricating a number.

**Known gap:** report #1 ("T&A status report — the Excel replica") is a
reasonable flattened reconstruction (order/status columns + one dynamic
column per template task), not a literal 81-column replica. Its actual
import/export round-trip lives on the T&A plans screen itself (§5, test
#15) — see `TnaGridExportService`/`TnaGridImportService` above.

**Remaining gap (not closed this session):** §M01's "Excel import/export"
AC for the 14 simple masters (buyers, seasons, colors, ...) was not
built — the masters CRUD is create/edit/delete only, no bulk file-based
maintenance. Style Development's Measurement Chart tab (§M03) has a
working manual UI (added this session) but no dedicated Excel import/
export of its own either, unlike the Sales Contract PO grid and T&A grid,
which do.

## 11. Tests

`tests/Feature/MerchandisingTraceTest.php` lives in the **host application**
(`erp-suhana/tests/Feature/`), not inside this package, mirroring the
existing `SflInventoryStockEngineTest` pattern already established there:
`DatabaseTransactions` (not `RefreshDatabase`) against the real MySQL dev
database, so every test is wrapped in a transaction and rolled back —
nothing is ever persisted, and `migrate:fresh` is never run against shared
data. This package's schema is MySQL-specific (`DocumentNumberService`
uses `SUBSTRING_INDEX`), matching the deployment target, so the host's
default `:memory:` sqlite test config is overridden per-test before
`parent::setUp()` boots the app.

Run it from the host app:

```bash
php artisan test --filter=MerchandisingTraceTest
```

Covers §7 test cases #2–#7 (contract confirm → T&A generation,
sample/material auto-sync, PCD fail-reason/dept), #10 (sub-T&A cumulative
math), #11 (costing formulas — exact values), #13 (effective-value
resolution, all 3 revision states), and #14 (merchandiser row scoping,
verified live against the real scope wired into `SalesContract`).

**Not covered** (see gaps noted above): #1 (inquiry→style — simple enough
to have been manually verified, not worth a permission-gated HTTP test),
#8's delta-on-qty-increase half, #9 (embellishment→style_parts sync), #12
(this build's closest equivalent is `BomItem::netConsumption()`, which the
costing test's sibling logic already exercises indirectly), #15 (Excel
round-trip).

## 12. Troubleshooting

- **"Class ... not found" after adding a new model/service** — run
  `composer dump-autoload` in the host app.
- **Never run `composer update` casually with this package installed as a
  path repository.** Earlier in this build, a version-constraint mismatch
  (`dev-master` required vs an actual `main` branch) caused Composer to
  force a symlinked path package to resync, which **deleted its `.git`
  directory** and reverted all uncommitted work. Verify every symlinked
  path repo's branch name matches its `composer.json` constraint before
  running `composer update`.
- **Stale `migrations` table rows referencing tables that don't exist** —
  happened twice during this build (unrelated to this package's own code).
  Fixed by deleting the offending `migrations` rows and re-running
  `php artisan migrate --force`.
- **`DefaultTnaTemplateSeeder` / `DefaultDocumentTemplateSeeder` seem to
  "do nothing" on a second run** — both have an early-return guard
  (`if ($template->tasks()->exists()) return;`) so they're safe to call
  repeatedly, but that also means changing the seed data requires clearing
  the existing template rows first.
- **A style can't be handed over to production** — check
  `mer_styles.trc_product_id` / `trc_size_group_id` are set (§7's one
  manual mapping step) before anything else in the checklist.

## 13. ERD

```mermaid
erDiagram
    Buyer ||--o{ Style : "buyer_id"
    Buyer ||--o{ SalesContract : "buyer_id"
    Season ||--o{ Style : "season_id"
    Inquiry ||--o{ InquiryItem : "inquiry_id"
    Inquiry ||--o| Style : "inquiry_id (convertToStyle)"

    Style ||--o{ Sample : "style_id"
    Style ||--o{ Bom : "style_id"
    Style ||--o{ CostSheet : "style_id"
    Style ||--o{ SalesContractPo : "style_id"
    Style ||--o{ MaterialBooking : "style_id"

    Bom ||--o{ BomItem : "bom_id"

    SalesContract ||--o{ SalesContractPo : "sales_contract_id"
    SalesContract ||--o{ OrderDocument : "sales_contract_id"
    SalesContractPo ||--o{ SalesContractPoSize : "sales_contract_po_id"
    SalesContractPo ||--o{ SalesContractPoRevision : "sales_contract_po_id"
    SalesContractPo ||--o| TnaPlan : "sales_contract_po_id"
    SalesContractPo ||--o{ ShipmentBooking : "sales_contract_po_id"
    SalesContractPo ||--o{ TnaSubPlan : "sales_contract_po_id"

    TnaTemplate ||--o{ TnaTemplateTask : "tna_template_id"
    TnaPlan ||--o{ TnaTask : "tna_plan_id"
    TnaTask ||--o{ TnaTaskLog : "tna_task_id"
    TnaSubPlan ||--o{ TnaSubDailyLog : "tna_sub_plan_id"

    MaterialBooking ||--o{ MaterialBookingItem : "booking_id"
    MaterialBooking ||--o{ MaterialConsignment : "booking_id"
    MaterialBooking ||--o{ MaterialReceipt : "booking_id"

    SalesContractPo ||--o| ProductionHandover : "sales_contract_po_id"
    SalesContractPo ||--o| PoProductionProgress : "sales_contract_po_id"

    %% ---- Bridge relations (cross-package, soft references, no DB FK) ----
    SalesContractPo }o..o| TrcPlanLine : "production_plan_line_id (BRIDGE)"
    TrcProductionPlan ||--o{ TrcPlanStyle : "production_plan_id"
    TrcPlanStyle ||--o{ TrcPlanLine : "plan_style_id"
    TrcPlanLine ||--o{ TrcPlanLineSize : "plan_line_id"
    TrcPlanLine ||--o{ TrcShipment : "plan_line_id"
    Style }o..o{ TrcStylePart : "style_id (BRIDGE, read-only)"
    ProductionHandover }o..o| TrcPlanLine : "plan_line_id (BRIDGE)"
```

Dotted/`BRIDGE`-labelled edges cross the package boundary: they're plain
`unsignedBigInteger` soft references (no DB-level foreign key), resolved at
the application layer by `Models/Bridge/*` proxy models that point at
production-trace's tables by name only. Every other edge is a normal FK
within this package's own `mer_*` schema.
