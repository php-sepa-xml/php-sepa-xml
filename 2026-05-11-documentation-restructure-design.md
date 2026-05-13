---
title: Documentation Restructure — Design
summary: Enhanced documentation structure for php-sepa-xml — audience-first hierarchy, hand-written reference, edge-cases inline with a cross-linked index.
date: 2026-05-11
status: approved-for-planning
---

# Documentation Restructure — Design

## 1. Goal

Replace today's three-file `doc/` directory with an audience-first documentation tree that:

- Gets a new PHP developer from `composer require` to a working SEPA XML file in under 10 minutes.
- Documents the main interaction styles (Facade vs. direct objects) for both flows (Credit Transfer / Direct Debit).
- Surfaces edge-cases (bank-specific quirks, version gating, sanitizer global state, `asXML()` idempotency, missing-BIC handling, etc.) **inline** in the guide where they apply, and indexes them in a single `gotchas.md` for quick lookup.
- Provides hand-written per-class reference and a single-source-of-truth `pain.*` version-capability matrix.
- Is structured as plain Markdown today but maps 1:1 to a static-site generator (MkDocs Material / Docusaurus) the day one is added — no file moves required.

## 2. Constraints / decisions (locked during brainstorming)

| Decision | Choice |
|---|---|
| Delivery format | Markdown now, site-ready later |
| Primary audience | App developers integrating SEPA |
| Edge-case strategy | Inline in each guide, cross-linked from `gotchas.md` |
| Extra content | API reference (per class), version matrix, migration notes |
| Reference scope | Public app-facing classes only; skip internal interfaces / `BaseDomBuilder` |
| `bank-profiles.md` vs `gotchas.md` | Recipe vs. index entry — both exist, no overlap |

Out of scope:

- Auto-generated phpDocumentor / Doctum / Sami reference.
- Translations (English only).
- Interactive playground / live XML preview.
- Deep contributor architecture doc (class diagrams, visitor walkthrough).
- Documenting bundled XSDs; `doc/ISO20022/` stays as-is.

## 3. Target file tree

```
doc/
├── README.md                        # Doc index + reading order
├── getting-started.md               # 10-minute happy path (SCT + Facade)
│
├── guides/
│   ├── credit-transfer.md
│   ├── direct-debit.md
│   ├── choosing-facade-vs-direct.md
│   ├── group-header.md
│   ├── addresses.md
│   ├── amendments.md
│   ├── custom-sanitization.md
│   ├── output-and-validation.md
│   └── bank-profiles.md
│
├── reference/
│   ├── classes/
│   │   ├── group-header.md
│   │   ├── payment-information.md
│   │   ├── customer-credit-transfer-file.md
│   │   ├── customer-direct-debit-file.md
│   │   ├── customer-credit-transfer-information.md
│   │   ├── customer-direct-debit-transfer-information.md
│   │   ├── facade.md
│   │   ├── dom-builder.md
│   │   └── sanitizer.md
│   ├── pain-version-matrix.md
│   ├── iso20022-naming.md
│   └── exceptions.md
│
├── gotchas.md
├── migrations.md
├── contributing.md
│
├── assets/
│   ├── structure.dia
│   └── structure.png
│
└── ISO20022/                        # Unchanged
    ├── acmt/
    ├── camt/
    └── pain/
```

Shape decisions:

- `getting-started.md` is **single-flow** (Credit Transfer + Facade only) — the point is "get something running fast," not "survey both flows."
- `guides/` holds 9 task-shaped pages, each ~150–400 lines. No page tries to be a complete reference.
- `reference/classes/` is **hand-written**, one file per public class, with each setter/getter mapped to the XML element it produces.
- `gotchas.md` is an **index, not a content page** — entries deep-link into the guides where the callout lives.

## 4. Per-page outlines

### 4.1 Top-level

**`doc/README.md`**
"What's in here" map; reading-order suggestions ("first time" → getting-started; "integrating SEPA Direct Debit" → guides/direct-debit; "my bank rejects the file" → bank-profiles + gotchas). Links to project root README, version matrix, gotchas.

**`doc/getting-started.md`**
5-step happy path: `composer require` → create Facade → addPaymentInfo → addTransfer → `asXML()` → write file. Working end-to-end snippet, realistic values, targets `pain.001.001.09`. "What next?" footer.

### 4.2 `guides/`

**`credit-transfer.md`** — Facade-first then direct-objects examples. Sub-sections: `creditorReference` / `purposeCode`, postal address, multiple PmtInf per file, multiple transfers per PmtInf. Inline callouts: `asXML()` non-idempotent; `batchBooking` semantics; `setSequenceType` is on PmtInf, not transfer.

**`direct-debit.md`** — Same shape. Sub-sections: mandate fields, `seqType` (`S_ONEOFF` / `S_FIRST` / `S_RECURRING` / `S_FINAL`), `localInstrumentCode` (`CORE` / `B2B` / `COR1`), `getUUID()`, `dueDate` defaults. Inline callouts: sequence type lives on PmtInf; mandate-date format; UETR availability version-gated.

**`choosing-facade-vs-direct.md`** — Decision matrix: when Facade is enough vs. when you need direct objects (custom `GroupHeader`, custom `InitiatingPartyId`, raw `DOMDocument` manipulation, multiple builders sharing state).

**`group-header.md`** — `MsgId`, `CreDtTm`, `InitgPty/Nm`, `InitgPty/Id`. `TransferFileFacadeFactory::createDirectDebitWithGroupHeader` pattern (and SCT equivalent if any). Inline callouts: nullable get / non-null set asymmetry on `setInitiatingPartyIdentificationScheme()`; auto-generated `MsgId` vs custom.

**`addresses.md`** — All seven postal-address fields with XML mappings. EEA rule (CH / AD / MC / SM / VA / GB require address). Inline callouts: `setFloorNumber` version-gated; structured vs. unstructured address differences across pain versions.

**`amendments.md`** — All DD amendment fields (`originalMandateId`, `originalDebtorIban`, `amendedDebtorAccount`, etc.) with worked examples. Inline callouts: amendments apply only on `S_FIRST` / `S_ONEOFF` in some bank profiles.

**`custom-sanitization.md`** — Default behaviour + the three Sanitizer methods (`setSanitizer`, `disableSanitizer`, `resetSanitizer`). XML entity escaping is separate. Inline callouts: `Sanitizer` is process-global mutable state; public properties on `PaymentInformation` / `GroupHeader` bypass sanitization.

**`output-and-validation.md`** — `asXml()` returns string; `asDoc()` returns `DOMDocument`. Facade naming inconsistency (`asXML()` / `asDOC()` capitalised). XSD-validation recipe using bundled `doc/ISO20022/pain/...`. Inline callouts: `asXML()` non-idempotent (double-counts `NbOfTxs` / `CtrlSum`).

**`bank-profiles.md`** — Per-profile ~20-line recipes:
- German DK (pain.001.001.03): `setOmitGroupHeaderControlSum(true)`.
- Spanish banks: custom `GroupHeader` with `setInitiatingPartyId`.
- NL Rabo (pain.008.001.02 DD).
- AT Raiffeisen / Volksbank (pain.008.001.08 DD).
- Generic "bank rejects NOTPROVIDED BIC": `setOmitAgentElementIfBicMissing(true)`.

### 4.3 `reference/classes/`

Each file follows the same template:

1. Purpose & position in the data model (one paragraph).
2. Constructor signature.
3. Setters / getters table (see § 5.5).
4. "See also" links.

Coverage targets (drawn from `src/`):

- `payment-information.md` — `setCreditorId`, `setSequenceType`, `setOriginName`, `setLocalInstrumentCode`, `setCategoryPurposeCode`, `setBatchBooking`, …
- `customer-credit-transfer-information.md` — `setBic`, `setCreditorReference`, `setCreditorReferenceType`, `setPurposeCode`, address setters.
- `customer-direct-debit-transfer-information.md` — `setMandateId`, `setMandateSignDate`, `setFinalCollectionDate`, amendment setters, `getUUID()`.
- `dom-builder.md` — `setOmitGroupHeaderControlSum`, `setOmitAgentElementIfBicMissing`, `asXml`/`asDoc`, `DomBuilderFactory::createDomBuilder($file, $painFormat)`.
- `facade.md` — `CustomerCreditFacade`, `CustomerDirectDebitFacade`, `TransferFileFacadeFactory` static methods, passthrough flag setters.
- `sanitizer.md` — three static methods + warning about global state.
- `group-header.md` — constructor, `setInitiatingPartyId`, `setInitiatingPartyIdentificationScheme`.

### 4.4 Reference top-level

**`pain-version-matrix.md`** — Rows = supported pain version. Columns = feature gates (`UETR`, `FloorNumber`, `BICFI`, structured `ReqdExctnDt`, structured creditor/debitor address). "Recommended minimum" markers (pain.001.001.09 SCT, pain.008.001.08 SDD). Variant rows for STP (pain.001.002.03), EUSTP (pain.001.003.03), pain.008.002.02, pain.008.003.02.

**`iso20022-naming.md`** — `MessageType.SubType.Variant.Version` decoder (lifted from project README). Links to ISO20022 catalogue + xmldation wiki.

**`exceptions.md`** — One row per exception in `src/Exception/` (`Exception`, `InvalidArgumentException`, `InvalidPaymentMethodException`, `InvalidTransferFileConfiguration`, `InvalidTransferTypeException`): when it fires, how to recover.

### 4.5 Cross-cutting

**`gotchas.md`** — Curated one-liners with deep links. Initial seed:

- `asXML()` is not idempotent → `output-and-validation.md`
- `Sanitizer` is process-global mutable state → `custom-sanitization.md`
- `setSequenceType` lives on `PaymentInformation`, not the transfer → `direct-debit.md`
- Public properties bypass sanitization → `custom-sanitization.md`
- `setInitiatingPartyIdentificationScheme` nullable asymmetry → `group-header.md`
- `pain.001.001.03` DK profile requires omit-CtrlSum → `bank-profiles.md`
- Non-EEA debtor accounts require address → `addresses.md`
- `setFloorNumber` only emits on supported versions → `addresses.md` + version matrix
- Naming inconsistency `asXml`/`asXML` between DomBuilder and Facade → `output-and-validation.md`
- Passing the same `PaymentInformation` to a credit file and a debit file mutates `validPaymentMethods` → `payment-information.md`

**`migrations.md`** — Section per past notable change (sourced from `CHANGELOG.md` + `IMPROVEMENTS.md`): what changed, how to update calling code. "Coming in v3 (planned)" section flagging breaking changes from IMPROVEMENTS.md: `getDebitorName` → `getDebtorName`, `getRemittenceElement` → `getRemittanceElement`, possible Sanitizer DI, possible PHP 8.1 enums.

**`contributing.md`** — Composer scripts (`composer run phpunit`, `composer run phpstan`, `composer run rector`). PR expectations (test coverage required for new fields). Links to `.github/` issue templates and `SECURITY.md`.

## 5. Cross-page conventions

### 5.1 Page header

Minimal YAML frontmatter; valid as Markdown today and as a static-site source later:

```markdown
---
title: Credit Transfer
summary: Build pain.001 Customer Credit Transfer files via Facade or direct objects.
---

# Credit Transfer
```

### 5.2 "At a glance" box (top of every guide)

```markdown
> **At a glance**
> - **Use this when:** building pain.001 Customer Credit Transfer files.
> - **Prerequisites:** debtor IBAN + BIC (or use the omit-agent flag).
> - **Minimum recommended version:** pain.001.001.09.
> - **Related:** [Choosing Facade vs direct](choosing-facade-vs-direct.md), [Bank profiles](bank-profiles.md).
```

### 5.3 Callout convention

Three types only:

```markdown
> ⚠️ **Gotcha:** `asXML()` is not idempotent. Calling it twice double-counts
> `NbOfTxs` and `CtrlSum`. See [output-and-validation](output-and-validation.md).

> ℹ️ **Version note:** `setFloorNumber()` only emits XML on pain.001.001.09+.
> See the [version matrix](../reference/pain-version-matrix.md).

> 🏦 **Bank profile:** German DK pain.001.001.03 requires
> `setOmitGroupHeaderControlSum(true)`. See [bank-profiles](bank-profiles.md#german-dk).
```

`gotchas.md` is initially maintained by hand from `⚠️ **Gotcha:**` callouts; a future `scripts/build-gotchas.php` can grep them automatically.

### 5.4 Code-block convention

- Every example runnable as-is — no `...` placeholders hiding required setup. Split into preamble + delta if too long.
- First line of every snippet states the pain version targeted: `// pain.001.001.09`.
- Imports shown at the top of the first snippet on a page; subsequent snippets may omit.
- Realistic field values (IBANs from existing tests, real country codes/currencies) — no `'foo'` / `'bar'`.

### 5.5 XML mapping table (reference pages)

| Method | Parameters | XML element | Version |
|---|---|---|---|
| `setMandateId(string $id)` | `$id` ≤ 35 chars | `MndtRltdInf/MndtId` | all |
| `setUETR(string $uuid)` | UUIDv4 string | `PmtId/UETR` | pain.001.001.09+ |

The "Version" column links into the version matrix when it isn't `all`. The matrix is the single source of truth for feature gating.

### 5.6 Linking conventions

- Internal links are **relative paths**.
- Every link to a section uses an explicit `#anchor`.
- External links live in a single "External resources" footer per page, not inline mid-paragraph.

### 5.7 Per-page footer

```markdown
## Related guides
- [Direct Debit](direct-debit.md)

## Reference
- [`PaymentInformation`](../reference/classes/payment-information.md)
- [Version matrix](../reference/pain-version-matrix.md)

## External resources
- [ISO20022 pain.001 catalogue entry](https://www.iso20022.org/...)
```

### 5.8 Naming & casing

- File names: `kebab-case.md`. Today's `credit_transfer.md` / `direct_debit.md` get renamed.
- One H1 per file matching frontmatter `title`. H2 for sections, H3 for sub-sections; avoid going below H3.
- Anchor IDs follow GitHub's auto-generation.

### 5.9 Versioning & maintenance

- The version matrix is the **only** place that enumerates per-version capability.
- "Minimum recommended version" appears in four locations only (project README, getting-started, at-a-glance boxes, version matrix). One update touches all four.
- Each guide page footer carries `<!-- updated: YYYY-MM-DD -->` for a future staleness audit.

## 6. Migration of existing content

### 6.1 File-by-file disposition of today's `doc/`

| Today | Becomes | What changes |
|---|---|---|
| `doc/credit_transfer.md` | `doc/guides/credit-transfer.md` | Renamed (kebab-case). "Additional Features" block (omit flags) moves to `bank-profiles.md` + cross-link. Variable-name explanation replaced with realistic values. |
| `doc/direct_debit.md` | `doc/guides/direct-debit.md` | Renamed. Split: "Add an amendment" → `amendments.md`; address section → `addresses.md` (merged with SCT address content); "Additional Features" → `bank-profiles.md`; "Custom Header" example → `group-header.md`. |
| `doc/string_sanitization.md` | `doc/guides/custom-sanitization.md` | Renamed; expanded with global-state warning and public-properties-bypass gotcha; **namespace bug fixed** (`SepaXml\Util\Sanitizer` → `Digitick\Sepa\Util\Sanitizer`). |
| `doc/dev_setup.md` | `doc/contributing.md` | Renamed; kept slim. |
| `doc/structure.dia` / `doc/structure.png` | `doc/assets/structure.{dia,png}` | Moved to `assets/`. Referenced only from `contributing.md`. |
| `doc/ISO20022/` (XSDs) | unchanged | Stays. Referenced from `output-and-validation.md`. |

### 6.2 Project-root files

| Today | Becomes | What changes |
|---|---|---|
| `README.md` "Documentation" section (lines 75–93) | Two-line pointer to `doc/README.md` | "Full documentation lives in [`doc/`](doc/README.md). Start with [Getting Started](doc/getting-started.md)." |
| `README.md` ISO20022 naming section (lines 81–89) | `doc/reference/iso20022-naming.md` | Moved verbatim, expanded with examples. Project README keeps a one-line pointer. |
| `README.md` supported-versions list (lines 16–42) | Stays in project README **and** seeds `doc/reference/pain-version-matrix.md` | README = marketing answer ("does this lib support X?"); matrix = engineering answer ("does X support UETR?"). Cross-link both ways. |
| `README.md` "Institutions confirmed" list (lines 50–60) | Stays in project README; **also** seeds `doc/guides/bank-profiles.md` | Each entry gets a profile recipe. New profiles added to both. |
| `IMPROVEMENTS.md` | Stays at project root | Items #1, #3, #4, #7, #8, #16 surface as inline callouts (asXML idempotency, Sanitizer global state, sanitize bypass, omit-agent flag, validPaymentMethods mutation). v3-deprecation items seed `migrations.md`. The file itself stays as an internal roadmap. |
| `TODO.md`, `CHANGELOG.md`, `SECURITY.md` | Stay at project root | `CHANGELOG.md` linked from `migrations.md`; `SECURITY.md` linked from `contributing.md`. |

### 6.3 New content (no current source)

- `doc/README.md`
- `doc/getting-started.md`
- `doc/guides/choosing-facade-vs-direct.md`
- `doc/guides/output-and-validation.md`
- `doc/reference/classes/*` (9 pages)
- `doc/reference/pain-version-matrix.md`
- `doc/reference/exceptions.md`
- `doc/gotchas.md`
- `doc/migrations.md`

### 6.4 Bugs to fix during the migration

These small correctness issues travel with the migration commit, not as separate doc-bug-fix PRs:

1. Wrong namespace in `string_sanitization.md` (`SepaXml\Util\Sanitizer` → `Digitick\Sepa\Util\Sanitizer`).
2. Duplicate setter calls in `credit_transfer.md` and `direct_debit.md` ("Additional Features" examples call `setOmitAgentElementIfBicMissing(true)` twice — one was clearly meant to be `setOmitGroupHeaderControlSum`).
3. Stray `.` in `credit_transfer.md` at end of `file_put_contents($filePath, $domBuilder->asXml());.`.

### 6.5 Phasing

The work splits into three reviewable commit groups:

1. **Restructure** — create new tree, move + rename existing files (§ 6.1, § 6.2), update internal links, fix the three bugs in § 6.4, update project README pointer. **No new content yet.** Reviewers verify nothing was lost.
2. **Write new pages** — § 6.3. The bulk of the writing; will likely subdivide into smaller commits per page or group.
3. **Polish & cross-link** — apply § 5 conventions across all pages (at-a-glance boxes, callout convention, footers, frontmatter), build `gotchas.md` from the callouts, validate every relative link.

### 6.6 Things explicitly not touched

- Source code in `src/` — no API changes; this is docs-only.
- Bundled XSDs in `doc/ISO20022/`.
- `composer.json`, CI, `.github/`.
- IMPROVEMENTS.md items themselves — we **document** them as gotchas, we do not **fix** them here.

## 7. Acceptance criteria

The restructure is done when:

- Every file listed in § 3 exists and is non-empty.
- No content from the original three doc files is lost (verifiable by checking § 6.1 mappings).
- The three correctness bugs in § 6.4 are fixed in the new locations.
- Project root `README.md` "Documentation" section is replaced by a two-line pointer (§ 6.2).
- `gotchas.md` contains at least the 10 entries seeded in § 4.5.
- Every internal link resolves (no 404s on a Markdown link checker).
- The version matrix in `reference/pain-version-matrix.md` covers every pain version listed in the project README (§ 6.2).
- A new dev can follow `getting-started.md` end-to-end without consulting any other page.
