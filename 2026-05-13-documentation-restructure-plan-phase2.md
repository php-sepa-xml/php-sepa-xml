# Phase 2 Plan — Documentation Restructure

**Date:** 2026-05-13
**Branch:** `docs/restructure-phase-1` (continuing in the same branch — Phase 2 commits stack on top of Phase 1)
**Audience for new content:** App devs **with** SEPA / ISO 20022 background. Terse, dense, library-mechanics-focused. Cross-link to ISO refs rather than re-explain.
**Reference depth:** Constructor + 5–10 key methods per class. No exhaustive setter/getter dump.
**Scope:** Write the 16 new content pages identified in [`2026-05-11-documentation-restructure-design.md`](2026-05-11-documentation-restructure-design.md) §3.
**Out of scope:** Cross-page polish (frontmatter, "at a glance" boxes, callout convention, ATX heading conversion) — that is Phase 3.

---

## Files to write (16)

### Top-level (3)
1. `doc/getting-started.md`
2. `doc/gotchas.md`
3. `doc/migrations.md`

### Guides (2)
4. `doc/guides/choosing-facade-vs-direct.md`
5. `doc/guides/output-and-validation.md`

### Reference, non-class (2)
6. `doc/reference/pain-version-matrix.md`
7. `doc/reference/exceptions.md`

### Reference, classes (9)
8. `doc/reference/classes/group-header.md`
9. `doc/reference/classes/payment-information.md`
10. `doc/reference/classes/customer-credit-transfer-file.md`
11. `doc/reference/classes/customer-direct-debit-file.md`
12. `doc/reference/classes/customer-credit-transfer-information.md`
13. `doc/reference/classes/customer-direct-debit-transfer-information.md`
14. `doc/reference/classes/facade.md` *(covers `CustomerCreditFacade`, `CustomerDirectDebitFacade`, `TransferFileFacadeFactory`)*
15. `doc/reference/classes/dom-builder.md` *(covers `BaseDomBuilder`, the two concrete subclasses, `DomBuilderFactory`)*
16. `doc/reference/classes/sanitizer.md`

---

## Working order

Foundational first (refs that other pages link to), then class reference, then narrative guides, then nav wiring. Four commits.

### Commit 1 — Foundational reference

Tasks:

1.1. Write `doc/reference/pain-version-matrix.md`.
- Two tables: pain.001.* and pain.008.*.
- Columns: version → ISO20022 message name → notes (recommended? variant? minimum supported?).
- Source the version list from `src/Util/MessageFormat.php::getSupportedMessageFormats()` — read the file, list the actual constants/array.
- Note: pain.001.001.09 is "minimum recommended" for credit transfer; pain.008.001.08 for direct debit (per project README).

1.2. Write `doc/reference/exceptions.md`.
- One section per exception in `src/Exception/`:
  - `Exception` (base)
  - `InvalidArgumentException`
  - `InvalidPaymentMethodException`
  - `InvalidTransferFileConfiguration`
  - `InvalidTransferTypeException`
- For each: class name, parent, when it's thrown (grep `throw new X` in `src/` to find call sites), example trigger.

1.3. Write `doc/gotchas.md`.
- Curated catalogue, not exhaustive. Seed with:
  - Amounts in **integer cents**, not floats/euros (BaseTransferInformation::__construct first param `int $amount`).
  - SequenceType is set on `PaymentInformation`, not per-transfer (constants S_FIRST/S_RECURRING/S_ONEOFF/S_FINAL).
  - `CustomerDirectDebitTransferFile::validate()` throws on missing mandate fields — list which.
  - BIC is optional in newer pain versions; use `setOmitAgentElementIfBicMissing(true)` on the DomBuilder (see [bank-profiles](guides/bank-profiles.md)).
  - Group header control sum can be omitted for some banks (`setOmitGroupHeaderControlSum`).
  - Character set: SEPA-approved subset; non-conforming chars are sanitized by `Util\Sanitizer` (see [custom-sanitization](guides/custom-sanitization.md)).
  - `mandateSignDate` accepts string `'d.m.Y'` in the facade array form, `DateTimeInterface` in direct construction — easy to mix up.
  - DD non-EEA addresses require `setCountry/setPostCode/setTownName/setStreetName/setBuildingNumber/setFloorNumber` (see [addresses](guides/addresses.md)).
- Each entry: heading, 1–3 sentences, link to deeper guide if one exists.
- Phase 3 will convert these into proper ⚠️ callouts and auto-index from per-page callouts.

1.4. Verify links.
- Run `npx markdown-link-check doc/reference/pain-version-matrix.md doc/reference/exceptions.md doc/gotchas.md` if available; otherwise spot-check with `grep -h '\](.*\.md' <files>` and confirm targets exist.

1.5. Commit.
- Message: `docs: add reference foundations (pain matrix, exceptions, gotchas)`.

### Commit 2 — Class reference pages

For every class page, follow this template:

```markdown
# <ClassName>

**Namespace:** `Digitick\Sepa\<Subpath>`
**File:** `src/<path>.php`

<one-paragraph purpose statement>

## Constructor

```php
public function __construct(...)
```

<params + what they map to in the output XML>

## Key methods

### `methodName(...)`
<one-liner; note required vs optional, side effects, when to call>

## Related

- [Guide: ...](../../guides/...)
- [Reference: ...](../...)
```

Tasks (one per page; same template, vary content):

2.1. `group-header.md` — constructor (id, party name, isTest); setControlSumCents, setNumberOfTransactions (note: usually auto-populated by DomBuilder), setInitiatingPartyId + scheme, setCreationDateTimeFormat (for vendor compatibility).

2.2. `payment-information.md` — constructor; the four S_* constants; addTransfer, setSequenceType, setLocalInstrumentCode, setCreditorId, setBatchBooking, setDueDate, setCategoryPurposeCode, setMandateSignDate. Note: paymentMethod defaults differ between CT and DD subclasses.

2.3. `customer-credit-transfer-file.md` — constructor (GroupHeader); addPaymentInformation; validate() — list what it enforces (e.g. `paymentMethod=TRF`).

2.4. `customer-direct-debit-file.md` — same shape; validate() enforces `paymentMethod=DD`, mandate fields, sequenceType set.

2.5. `customer-credit-transfer-information.md` — constructor (amount cents, IBAN, name, endToEndId); inherited setters from BaseTransferInformation worth highlighting: setBic, setCreditorReference, setCreditorReferenceType, setPurposeCode, setRemittanceInformation, address setters; getCreditorName.

2.6. `customer-direct-debit-transfer-information.md` — constructor; DD-specific: setMandateId, setMandateSignDate, setFinalCollectionDate, amendment setters (setAmendedDebtorAccount, setOriginalDebtorIban, setOriginalMandateId), hasAmendments. Cross-link [amendments](../../guides/amendments.md).

2.7. `facade.md` — covers three classes:
- `TransferFileFacadeFactory::createCustomerCredit($id, $name, $painFormat = 'pain.001.001.03')` and `::createDirectDebit(...)` — static factory methods.
- `CustomerCreditFacade::addPaymentInfo(string, array)` — list the supported array keys.
- `CustomerCreditFacade::addTransfer(string, array)` — list supported array keys.
- `CustomerDirectDebitFacade` equivalents.
- Inherited from `BaseCustomerTransferFileFacade`: `asXML()`, `asDOC()`, `setOmitGroupHeaderControlSum`, `setOmitAgentElementIfBicMissing`, `getPaymentInfo($name)`.

2.8. `dom-builder.md` — covers four classes:
- `DomBuilderFactory::createDomBuilder($transferFile, $painFormat)` — entry point.
- `BaseDomBuilder::__construct($painFormat, $withSchemaLocation = true)`.
- `asXml()`, `asDoc()`, `setOmitGroupHeaderControlSum`, `setOmitAgentElementIfBicMissing`.
- Mention the two concrete subclasses (`CustomerCreditTransferDomBuilder`, `CustomerDirectDebitTransferDomBuilder`) and that they're picked by the factory based on the file type.
- Cross-link [output-and-validation](../../guides/output-and-validation.md).

2.9. `sanitizer.md` — covers `Digitick\Sepa\Util\Sanitizer`. List the public static methods (read the file to confirm signatures). Cross-link [custom-sanitization](../../guides/custom-sanitization.md).

2.10. Verify links.
- `grep -rh '\](.*\.md' doc/reference/classes/` and spot-check; or markdown-link-check.

2.11. Commit.
- Message: `docs: add class reference pages (constructor + key methods)`.

### Commit 3 — Narrative guides + getting started + migrations skeleton

3.1. Write `doc/getting-started.md`.
- 30-second install (`composer require digitick/sepa-xml`).
- Smallest possible end-to-end credit transfer (facade form, ~15 lines).
- Smallest possible end-to-end direct debit (facade form, ~15 lines).
- "Next steps" section linking to: choosing-facade-vs-direct, credit-transfer guide, direct-debit guide, gotchas.

3.2. Write `doc/guides/choosing-facade-vs-direct.md`.
- Decision matrix table: criterion → facade / direct.
- Criteria to include: rapid prototyping, fine-grained control of XML output, custom GroupHeader, amendments, address setters, type safety, multiple PaymentInformation blocks per file.
- Code sketch for each side (~10 lines, not the full sample).
- Cross-link to guides for both.

3.3. Write `doc/guides/output-and-validation.md`.
- `$facade->asXML()` vs `$facade->asDOC()` vs `$domBuilder->asXml()` / `asDoc()`.
- When to use which (file write, in-memory mutation, signing/encryption).
- `$transferFile->validate()` — what it checks and when it throws (cross-link [exceptions](../reference/exceptions.md)).
- XSD validation against `validation_schema/` (the repo's XSDs) — short snippet showing how to validate the resulting DOM against the schema for a given pain version.
- The `withSchemaLocation` constructor flag on `BaseDomBuilder`.

3.4. Write `doc/migrations.md`.
- Skeleton only — sections for major version upgrades. Read `CHANGELOG.md` (if any) or `git log --oneline v*..` for tag boundaries; otherwise leave a `## 3.x → 4.x` placeholder noting nothing breaking known.
- Include subsection on PHP version requirements per release if discoverable from `composer.json` history.
- If genuinely nothing to migrate, write one paragraph saying so and explaining the page exists as a future home.

3.5. Verify links.

3.6. Commit.
- Message: `docs: add getting-started, narrative guides, and migrations skeleton`.

### Commit 4 — Navigation wiring

4.1. Update `doc/README.md` to link the new pages.
- Add "Getting started" as the first link under a new top-level section.
- Add "Choosing facade vs. direct" and "Output & validation" to the Guides list.
- Add `doc/migrations.md` and `doc/gotchas.md` as their own top-level sections (or under a "Reference" supersection — pick whatever reads cleanest).
- Expand the Reference section to list pain-version-matrix, exceptions, and a "Class reference" subsection linking each class page.

4.2. Verify the doc index renders sensibly.
- Read `doc/README.md` end-to-end; check it under 200 lines and scannable.
- Walk one link from each top-level section to confirm the target exists.

4.3. Run the full test suite as a sanity check (zero code changed, but proves no untracked test files broke).
- `vendor/bin/phpunit`

4.4. Commit.
- Message: `docs: wire new Phase 2 pages into doc/README.md`.

---

## Cross-cutting conventions for Phase 2

(Lighter than Phase 3 — these are the bare minimum to keep the new pages consistent.)

- **Headings:** ATX-style (`# Title`) for every new page from the start. We're not converting the Phase 1 files yet — that's a Phase 3 task — but new pages avoid the old Setext style so we don't accumulate more debt.
- **Code fences:** triple-backtick, language tag (` ```php `, ` ```xml `, ` ```bash `).
- **Class references in prose:** backtick-quote and use the **short class name** in body text (e.g. `PaymentInformation`); give the full FQCN once at the top of reference pages.
- **Method signatures:** include return type and visibility, e.g. ` ```public function addTransfer(TransferInformationInterface $transfer): void``` `.
- **Cross-links:** relative paths (`../guides/credit-transfer.md`), not absolute.
- **Tone:** terse. Assume the reader knows what an IBAN, BIC, mandate, and pain.008 are. No domain primers.

---

## Verification after each commit

- `find doc -name '*.md' | xargs wc -l` — sanity-check no page exploded past ~250 lines (a sign of scope creep). If one does, consider splitting in Phase 3.
- Spot-check 2–3 random cross-links per commit.
- After commit 4: full `vendor/bin/phpunit` run.

## Out of scope for Phase 2 (deferred to Phase 3)

- YAML frontmatter (`---\nname:\ndescription:\n---`) on each page.
- "At a glance" boxes summarising each guide.
- Callout convention (⚠️ Gotcha / ℹ️ Version note / 🏦 Bank profile) and converting plain-prose pitfalls into callouts.
- Per-page footer blocks (Related / Reference / External resources).
- Converting Phase 1 pages' Setext headings to ATX.
- Auto-building `gotchas.md` index from per-page callouts (currently we hand-write the entries; Phase 3 swaps to aggregating).
