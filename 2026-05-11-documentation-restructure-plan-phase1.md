# Documentation Restructure — Phase 1 (Restructure) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move and split today's three `doc/*.md` files plus `dev_setup.md` into the new audience-first tree without losing any content, fix 3 known correctness bugs in the source material, and update the project README so its "Documentation" pointer survives the move. **No new prose is written in this phase** — that is Phase 2.

**Architecture:** Pure file reorganization plus minimal content surgery. Use `git mv` for every move so history is preserved. Use `git format-patch`-style discipline: one logical change per commit, every intermediate state passes the link-check. Phase 2 (writing new pages) and Phase 3 (applying cross-page conventions) follow as separate plans.

**Tech Stack:** Bash, `git`, Markdown. Verification uses `npx markdown-link-check` (no install — `npx` fetches on demand) and `grep`.

---

## Scope check

This plan covers **only Phase 1** of the spec (§ 6.5 in `2026-05-11-documentation-restructure-design.md`). Phases 2 (write new pages) and 3 (polish & cross-link) are out of scope here and need their own plans:

- Phase 2 will write `doc/getting-started.md`, `doc/guides/choosing-facade-vs-direct.md`, `doc/guides/output-and-validation.md`, the 9 `doc/reference/classes/*.md` files, `doc/reference/pain-version-matrix.md`, `doc/reference/exceptions.md`, `doc/gotchas.md`, and `doc/migrations.md` from scratch.
- Phase 3 will apply the § 5 conventions (frontmatter, at-a-glance boxes, callout convention, footers) across every page and validate cross-links.

Phase 1 produces a reviewable deliverable on its own: every existing doc lives at its new path, the 3 bugs are fixed, the project README still points users to a working entry point, and an empty-but-correct skeleton exists for Phases 2/3 to fill in.

## File structure (Phase 1)

After this plan runs, `doc/` looks like:

```
doc/
├── README.md                        # NEW — minimal nav stub (Phase 2 expands it)
├── guides/
│   ├── credit-transfer.md           # moved from doc/credit_transfer.md
│   ├── direct-debit.md              # moved from doc/direct_debit.md
│   ├── custom-sanitization.md       # moved from doc/string_sanitization.md
│   ├── group-header.md              # NEW — receives "Custom Header" section
│   ├── addresses.md                 # NEW — receives address content from direct-debit
│   ├── amendments.md                # NEW — receives "Add an amendment" section
│   └── bank-profiles.md             # NEW — receives "Additional Features" from both flows
├── reference/
│   ├── classes/                     # empty dir (Phase 2 fills)
│   └── iso20022-naming.md           # NEW — moved from project README
├── assets/
│   ├── structure.dia                # moved from doc/structure.dia
│   └── structure.png                # moved from doc/structure.png
├── contributing.md                  # moved from doc/dev_setup.md
└── ISO20022/                        # untouched
```

Project root `README.md` "Documentation" section (lines 75–93 + 81–89) is replaced by a two-line pointer.

Directories that will be empty after Phase 1 (`doc/reference/classes/`) get a `.gitkeep` so the structure is committed.

## Pre-flight

- [ ] **Step P.1: Confirm clean working tree on a fresh branch**

Run:
```bash
git status --short
git switch -c docs/restructure-phase-1
```
Expected: `git status --short` shows only the untracked `TODO.md`, `IMPROVEMENTS.md`, and the two design docs (`2026-05-11-documentation-restructure-design.md`, `2026-05-11-documentation-restructure-plan-phase1.md`), plus any `cli/Development/*.php` scratch files. If there are tracked modifications, stash or commit before proceeding.

- [ ] **Step P.2: Sanity-check the source files exist at the paths the plan assumes**

Run:
```bash
wc -l doc/credit_transfer.md doc/direct_debit.md doc/string_sanitization.md doc/dev_setup.md
ls doc/structure.dia doc/structure.png
```
Expected: 107, 216, 30, 18 lines respectively, and both `structure.*` files present. If counts differ, the upstream files have changed since this plan was written — re-read them before proceeding so the section-line-number references in later tasks still match.

---

## Task 1: Create the new directory skeleton

**Files:**
- Create: `doc/guides/.gitkeep`
- Create: `doc/reference/classes/.gitkeep`
- Create: `doc/assets/.gitkeep`

- [ ] **Step 1.1: Create the directories**

Run:
```bash
mkdir -p doc/guides doc/reference/classes doc/assets
touch doc/guides/.gitkeep doc/reference/classes/.gitkeep doc/assets/.gitkeep
```

- [ ] **Step 1.2: Verify**

Run:
```bash
ls -la doc/guides doc/reference/classes doc/assets
```
Expected: each directory exists and contains a `.gitkeep`.

- [ ] **Step 1.3: Commit**

```bash
git add doc/guides/.gitkeep doc/reference/classes/.gitkeep doc/assets/.gitkeep
git commit -m "docs: scaffold new doc/ directory tree

Empty skeleton for the audience-first restructure described in
2026-05-11-documentation-restructure-design.md § 3. Subsequent
commits move existing files into it; Phase 2 fills it with new
content."
```

---

## Task 2: Move existing files with `git mv` (no content changes)

**Files:**
- Rename: `doc/credit_transfer.md` → `doc/guides/credit-transfer.md`
- Rename: `doc/direct_debit.md` → `doc/guides/direct-debit.md`
- Rename: `doc/string_sanitization.md` → `doc/guides/custom-sanitization.md`
- Rename: `doc/dev_setup.md` → `doc/contributing.md`
- Rename: `doc/structure.dia` → `doc/assets/structure.dia`
- Rename: `doc/structure.png` → `doc/assets/structure.png`
- Delete: `doc/guides/.gitkeep`, `doc/assets/.gitkeep` (no longer empty)

Doing all moves in one commit makes the diff legible (a single block of renames). Content fixes happen in later commits so the rename is reviewable as a pure move.

- [ ] **Step 2.1: Move every file with `git mv`**

Run:
```bash
git mv doc/credit_transfer.md      doc/guides/credit-transfer.md
git mv doc/direct_debit.md         doc/guides/direct-debit.md
git mv doc/string_sanitization.md  doc/guides/custom-sanitization.md
git mv doc/dev_setup.md            doc/contributing.md
git mv doc/structure.dia           doc/assets/structure.dia
git mv doc/structure.png           doc/assets/structure.png
```

- [ ] **Step 2.2: Remove now-unneeded `.gitkeep`s**

`doc/guides/` and `doc/assets/` now have real files. `doc/reference/classes/` is still empty, so its `.gitkeep` stays.

Run:
```bash
git rm doc/guides/.gitkeep doc/assets/.gitkeep
```

- [ ] **Step 2.3: Verify renames preserved history**

Run:
```bash
git log --follow --oneline doc/guides/credit-transfer.md | head -5
git log --follow --oneline doc/guides/direct-debit.md | head -5
```
Expected: at least one prior commit shows for each (the original creation/edit history under the old name). If only the current uncommitted move shows, `git mv` did not link the rename — investigate before committing.

- [ ] **Step 2.4: Commit the pure moves**

```bash
git add -A doc/
git commit -m "docs: move existing pages into new doc/ hierarchy

Pure file moves, no content changes. Renames also apply
kebab-case to filenames (credit_transfer.md -> credit-transfer.md
etc.) per spec § 5.8. Content splits and bug fixes follow in
separate commits so this diff stays a pure rename.

- doc/credit_transfer.md      -> doc/guides/credit-transfer.md
- doc/direct_debit.md         -> doc/guides/direct-debit.md
- doc/string_sanitization.md  -> doc/guides/custom-sanitization.md
- doc/dev_setup.md            -> doc/contributing.md
- doc/structure.{dia,png}     -> doc/assets/structure.{dia,png}"
```

---

## Task 3: Fix the 3 known correctness bugs in the moved files

These are listed in spec § 6.4. Each is a small, mechanical fix in its now-new location.

**Files:**
- Modify: `doc/guides/custom-sanitization.md` (namespace bug)
- Modify: `doc/guides/credit-transfer.md` (duplicate setter + stray `.`)
- Modify: `doc/guides/direct-debit.md` (duplicate setter)

### Bug 3.1 — wrong namespace in custom-sanitization.md

The original `string_sanitization.md` uses `SepaXml\Util\Sanitizer` in every example, but the actual class lives at `Digitick\Sepa\Util\Sanitizer` (see `src/Util/Sanitizer.php` and the project's PSR-4 mapping).

- [ ] **Step 3.1.1: Verify the actual namespace in source**

Run:
```bash
grep -n "namespace" src/Util/Sanitizer.php
```
Expected: `namespace Digitick\Sepa\Util;`

- [ ] **Step 3.1.2: Apply the namespace fix**

In `doc/guides/custom-sanitization.md`, replace every occurrence of:

```
use SepaXml\Util\Sanitizer;
```

with:

```
use Digitick\Sepa\Util\Sanitizer;
```

There are 3 occurrences (one per example block).

- [ ] **Step 3.1.3: Verify no `SepaXml` references remain in the doc tree**

Run:
```bash
grep -rn "SepaXml" doc/
```
Expected: no output.

### Bug 3.2 — duplicate `setOmitAgentElementIfBicMissing(true)` calls

Both `credit_transfer.md:106-107` and `direct_debit.md:215-216` (old line numbers, now under the new paths) call `setOmitAgentElementIfBicMissing(true)` twice in succession. The first call was clearly intended to be `setOmitGroupHeaderControlSum(true)` — the surrounding text is "To use set the flags on the facade instance before adding transfers", referring to both flags introduced earlier in the section.

- [ ] **Step 3.2.1: Locate the duplicates**

Run:
```bash
grep -n "setOmitAgentElementIfBicMissing(true)" doc/guides/credit-transfer.md doc/guides/direct-debit.md
```
Expected: two adjacent line numbers in each file (4 hits total).

- [ ] **Step 3.2.2: Fix `doc/guides/credit-transfer.md`**

In the "Additional Features" code block, replace the **first** of the two adjacent lines:
```
$customerCredit->setOmitAgentElementIfBicMissing(true);
$customerCredit->setOmitAgentElementIfBicMissing(true);
```
with:
```
$customerCredit->setOmitGroupHeaderControlSum(true);
$customerCredit->setOmitAgentElementIfBicMissing(true);
```

- [ ] **Step 3.2.3: Fix `doc/guides/direct-debit.md`**

Same fix in the corresponding "Additional Features" code block:
```
$directDebit->setOmitAgentElementIfBicMissing(true);
$directDebit->setOmitAgentElementIfBicMissing(true);
```
becomes:
```
$directDebit->setOmitGroupHeaderControlSum(true);
$directDebit->setOmitAgentElementIfBicMissing(true);
```

- [ ] **Step 3.2.4: Verify the fixes**

Run:
```bash
grep -A1 "setOmitGroupHeaderControlSum(true)" doc/guides/credit-transfer.md doc/guides/direct-debit.md
```
Expected: two hits, each followed by `setOmitAgentElementIfBicMissing(true);`.

### Bug 3.3 — stray `.` in credit-transfer.md

The original line was:
```
file_put_contents($filePath, $domBuilder->asXml());.
```

- [ ] **Step 3.3.1: Fix the stray period**

In `doc/guides/credit-transfer.md`, replace:
```
file_put_contents($filePath, $domBuilder->asXml());.
```
with:
```
file_put_contents($filePath, $domBuilder->asXml());
```

- [ ] **Step 3.3.2: Verify**

Run:
```bash
grep -n "asXml());\." doc/guides/credit-transfer.md
```
Expected: no output.

### Commit 3.x

- [ ] **Step 3.4: Commit all three bug fixes together**

```bash
git add doc/guides/custom-sanitization.md doc/guides/credit-transfer.md doc/guides/direct-debit.md
git commit -m "docs: fix three known correctness bugs carried over from old docs

Listed in spec § 6.4. Fixed together because each is one or two
character changes and they would otherwise produce a noisy
commit log on their own:

- custom-sanitization.md: wrong namespace SepaXml\\Util\\Sanitizer
  -> Digitick\\Sepa\\Util\\Sanitizer (the actual class location).
- credit-transfer.md and direct-debit.md: 'Additional Features'
  examples called setOmitAgentElementIfBicMissing(true) twice
  back-to-back; the first call was meant to be
  setOmitGroupHeaderControlSum(true).
- credit-transfer.md: stray '.' after
  file_put_contents(...->asXml());"
```

---

## Task 4: Split content out of `doc/guides/direct-debit.md`

Today's `direct_debit.md` is 216 lines holding five logically distinct topics. Three of them belong in new dedicated pages per spec § 6.1.

**Files:**
- Modify: `doc/guides/direct-debit.md` (remove sections)
- Create: `doc/guides/amendments.md` (receives lines 152–168 of the original)
- Create: `doc/guides/addresses.md` (receives lines 170–192 of the original)
- Create: `doc/guides/group-header.md` (receives lines 111–150 of the original)
- Modify: `doc/guides/bank-profiles.md` (created in Task 5 below; the "Additional Features" block from direct-debit moves there in Task 5)

For each new file, do `git mv` is NOT possible (we're moving a *section*, not a file). Use plain create-and-write, and rely on the commit message to document the provenance so future `git log --grep` can find it.

### 4.1 Extract amendments section → `doc/guides/amendments.md`

The section is delimited by:
- Start: `Add an amendment to a transfer` (was line 152 in the original)
- End: just before `Add address information to transaction` (was line 170 in the original)

- [ ] **Step 4.1.1: Create `doc/guides/amendments.md`**

Write the file with this content (copied verbatim from the section being removed, then given a top-level H1 instead of underline-style):

````markdown
# Amendments

Add an amendment to a transfer by passing the amendment fields when calling
`addTransfer` on the named `PaymentInformation` object.

```php
$directDebit->addTransfer('firstPayment', array(
    'amount'                  => 500,
    'debtorIban'              => 'FI1350001540000056',
    'debtorBic'               => 'OKOYFIHH',
    'debtorName'              => 'Their Company',
    'remittanceInformation'   => 'Purpose of this credit transfer',
    'endToEndId'              => 'Invoice-No X' // optional, if you want to provide additional structured info
    // Amendments start here
    'originalMandateId'       => '1234567890',
    'originalDebtorIban'      => 'AT711100015440033700',
    'amendedDebtorAccount'    => true
));
```
````

- [ ] **Step 4.1.2: Remove the section from `doc/guides/direct-debit.md`**

Delete lines 152–168 of the file (the heading `Add an amendment to a transfer`, its underline `--`, the intro line, and the code block).

To do this with `sed`:
```bash
sed -i '152,168d' doc/guides/direct-debit.md
```
(Re-verify line numbers with `grep -n "Add an amendment" doc/guides/direct-debit.md` first — if the file has been edited since Task 3, line numbers may have shifted by one or two.)

- [ ] **Step 4.1.3: Verify**

Run:
```bash
grep -n "Add an amendment\|originalMandateId" doc/guides/direct-debit.md
```
Expected: no output (both moved out).

Run:
```bash
grep -n "originalMandateId" doc/guides/amendments.md
```
Expected: one match in the new file.

### 4.2 Extract address section → `doc/guides/addresses.md`

Original section heading: `Add address information to transaction` (was line 170).

- [ ] **Step 4.2.1: Create `doc/guides/addresses.md`**

Write:

````markdown
# Addresses

If the debtor account belongs to a bank that is not a member of the European
Economic Area (EEA), the address data of the account holder must be added to
the transaction. For sure one must do this for the following countries:
Switzerland, Andorra, Monaco, San Marino, Vatican City and the United Kingdom.
Though it is generally a good practice to add this data anyway.

## Direct Debit example

```php
$directDebit->addTransfer('firstPayment', [
    'amount'            => 1499,
    'debtorIban'        => 'CH6089144731137988786',
    'debtorBic'         => 'CRESCHZZXXX',
    'debtorName'        => 'John Doe',
    // ...
    // and the relevant address data
    'debtorCountry'     => 'CH',
    'postCode'          => '8245',
    'townName'          => 'Feuerthalen',
    'streetName'        => 'Example Street',
    'buildingNumber'    => '12',
    'floorNumber'       => '13'
]);
```

## Credit Transfer example

For Credit Transfer the equivalent address setters live on
`CustomerCreditTransferInformation` and are called individually:

```php
$transfer->setCountry('BG');
$transfer->setPostCode('1000');
$transfer->setTownName('Nowhere');
$transfer->setStreetName('Some Street');
$transfer->setBuildingNumber(12);
$transfer->setFloorNumber(13);
```
````

(The Credit Transfer block above is lifted from the existing `doc/guides/credit-transfer.md` "Direct usage" example so both flows are covered here.)

- [ ] **Step 4.2.2: Remove the section from `doc/guides/direct-debit.md`**

Delete the `Add address information to transaction` heading and everything until the start of `Additional Features` (which gets moved in Task 5).

Run after re-verifying line numbers:
```bash
grep -n "Add address information\|Additional Features" doc/guides/direct-debit.md
```
Use the printed line numbers to delete the range:
```bash
sed -i '<addr_start>,<addr_end>d' doc/guides/direct-debit.md
```
where `<addr_start>` is the line of `Add address information to transaction` and `<addr_end>` is one line before `Additional Features`.

- [ ] **Step 4.2.3: Verify**

```bash
grep -n "debtorCountry\|Feuerthalen" doc/guides/direct-debit.md
```
Expected: no output.

```bash
grep -n "debtorCountry\|Feuerthalen" doc/guides/addresses.md
```
Expected: one match each.

### 4.3 Extract custom-header section → `doc/guides/group-header.md`

Original section heading: `Sample Usage DirectDebit with Factory and Custom Header` (was line 112).

- [ ] **Step 4.3.1: Create `doc/guides/group-header.md`**

Write:

````markdown
# Group Header

The `GroupHeader` controls the file-level metadata: `MsgId`, `CreDtTm`,
`InitgPty/Nm`, and `InitgPty/Id`. The Facade auto-creates a `GroupHeader`
from the constructor arguments, but you can pass your own when you need a
custom `InitiatingPartyId` (common with Spanish banks) or a deterministic
`MsgId`.

## Custom GroupHeader with the Facade

```php
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\GroupHeader;

// Set the custom header (Spanish banks example) information
$header = new GroupHeader(date('Y-m-d-H-i-s'), 'Me');
$header->setInitiatingPartyId('DE21WVM1234567890');

$directDebit = TransferFileFacadeFactory::createDirectDebitWithGroupHeader($header, 'pain.008.001.09');

$directDebit->addPaymentInfo('firstPayment', array(
    'id'                    => 'firstPayment',
    'dueDate'               => new DateTime('now + 7 days'), // optional. Otherwise default period is used
    'creditorName'          => 'My Company',
    'creditorAccountIBAN'   => 'FI1350001540000056',
    'creditorAgentBIC'      => 'PSSTFRPPMON',
    'seqType'               => PaymentInformation::S_ONEOFF,
    'creditorId'            => 'DE21WVM1234567890',
    'localInstrumentCode'   => 'CORE' // default. optional.
));

$directDebit->addTransfer('firstPayment', array(
    'amount'                => 500,
    'debtorIban'            => 'FI1350001540000056',
    'debtorBic'             => 'OKOYFIHH',
    'debtorName'            => 'Their Company',
    'debtorMandate'         => 'AB12345',
    'debtorMandateSignDate' => '13.10.2012',
    'remittanceInformation' => 'Order 123456',
    'endToEndId'            => 'MyUniqueClutchId',
));

// Retrieve the resulting XML
$directDebit->asXML();
```
````

- [ ] **Step 4.3.2: Remove the section from `doc/guides/direct-debit.md`**

Delete from `Sample Usage DirectDebit with Factory and Custom Header` up to (but not including) `Add an amendment to a transfer` — except that Task 4.1 already deleted "Add an amendment". So actually delete from `Sample Usage DirectDebit with Factory and Custom Header` up to (but not including) `Add address information to transaction` if 4.2 hasn't run yet, or up to whatever section currently follows it.

This task order matters. **Run 4.1, 4.2, then 4.3.** After 4.1 and 4.2 are done, the section *after* "Custom Header" in `direct-debit.md` is `Additional Features` (still present, removed in Task 5). So:

Re-verify line numbers:
```bash
grep -n "Sample Usage DirectDebit with Factory and Custom Header\|Additional Features" doc/guides/direct-debit.md
```

Delete the range:
```bash
sed -i '<header_start>,<additional_features_minus_1>d' doc/guides/direct-debit.md
```

- [ ] **Step 4.3.3: Update the TOC at the top of `doc/guides/direct-debit.md`**

The top-of-file TOC (originally lines 4–8) lists the now-removed sections. Replace with:

```markdown
* [Sample usage of DirectDebit File](#sample-usage-of-directdebit-file)
* [Sample Usage DirectDebit with Factory](#sample-usage-of-directdebit-with-facade-factory)
```

(The "Custom Header", "amendment", and "address information" entries are removed; "Additional Features" will also be removed in Task 5.)

- [ ] **Step 4.3.4: Verify**

```bash
grep -n "createDirectDebitWithGroupHeader" doc/guides/direct-debit.md
```
Expected: no output.

```bash
grep -n "createDirectDebitWithGroupHeader" doc/guides/group-header.md
```
Expected: one match.

### Commit 4.x

- [ ] **Step 4.4: Commit the direct-debit splits**

```bash
git add doc/guides/direct-debit.md doc/guides/amendments.md doc/guides/addresses.md doc/guides/group-header.md
git commit -m "docs: split direct-debit topics into dedicated guides

Per spec § 6.1, doc/guides/direct-debit.md was carrying four
distinct topics. Split into:

- doc/guides/amendments.md   (was 'Add an amendment to a transfer')
- doc/guides/addresses.md    (was 'Add address information to transaction',
                              plus the equivalent Credit Transfer setters
                              lifted from credit-transfer.md so both flows
                              are covered in one place)
- doc/guides/group-header.md (was 'Sample Usage DirectDebit with Factory
                              and Custom Header')

direct-debit.md now covers only the two core sample-usage flows. The
'Additional Features' section is still present and is moved out in the
next commit (bank-profiles.md)."
```

---

## Task 5: Create `doc/guides/bank-profiles.md` from both "Additional Features" sections

**Files:**
- Create: `doc/guides/bank-profiles.md`
- Modify: `doc/guides/credit-transfer.md` (remove "Additional Features")
- Modify: `doc/guides/direct-debit.md` (remove "Additional Features")

The two "Additional Features" sections in the moved files document the same two flags (`setOmitGroupHeaderControlSum`, `setOmitAgentElementIfBicMissing`). Spec § 4.2 says `bank-profiles.md` is recipe-shaped. Phase 1 produces a minimal version covering just the two flags; Phase 2 expands with the per-bank recipes (German DK, Spanish, NL Rabo, AT Raiffeisen, AT Volksbank).

- [ ] **Step 5.1: Create `doc/guides/bank-profiles.md`**

Write:

````markdown
# Bank Profiles

Country- and bank-specific recipes for producing files that pass validation
at institutions whose XML conventions diverge from the bare ISO 20022
defaults. Phase 1 of the documentation restructure covers only the two
generic flags. Per-bank recipes (German DK, Spanish initiating-party,
NL Rabo, AT Raiffeisen, AT Volksbank) are added in Phase 2.

## Generic flags

The library exposes two opt-in flags via `BaseDomBuilder` and as passthrough
methods on `BaseCustomerTransferFileFacade`. Both default to `false`, so
existing callers are unaffected.

- `setOmitGroupHeaderControlSum(bool)` — suppresses `<CtrlSum>` inside
  `<GrpHdr>`. Required by the German DK pain.001.001.03 profile, which
  forbids CtrlSum at group-header level.
- `setOmitAgentElementIfBicMissing(bool)` — omits the whole
  `<CdtrAgt>` / `<DbtrAgt>` wrapper when the corresponding BIC is missing,
  instead of emitting `<Othr><Id>NOTPROVIDED</Id></Othr>`. Applied at all
  four agent-element call sites (SCT and SDD, payment and transfer levels).

Set the flags on the facade instance before adding transfers:

```php
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

$customerCredit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me');

$customerCredit->setOmitGroupHeaderControlSum(true);
$customerCredit->setOmitAgentElementIfBicMissing(true);
```

The same two methods are available on the Direct Debit facade.
````

- [ ] **Step 5.2: Remove "Additional Features" from `doc/guides/credit-transfer.md`**

Locate the section:
```bash
grep -n "^Additional Features" doc/guides/credit-transfer.md
```

Delete from that heading line through end of file (it is the last section):
```bash
# Replace <line> with the line number of "Additional Features"
sed -i '<line>,$d' doc/guides/credit-transfer.md
```

- [ ] **Step 5.3: Remove "Additional Features" from `doc/guides/direct-debit.md`**

Same approach:
```bash
grep -n "^Additional Features" doc/guides/direct-debit.md
sed -i '<line>,$d' doc/guides/direct-debit.md
```

- [ ] **Step 5.4: Verify**

```bash
grep -c "^Additional Features" doc/guides/credit-transfer.md doc/guides/direct-debit.md
```
Expected: each file reports `0`.

```bash
grep -n "setOmitGroupHeaderControlSum\|setOmitAgentElementIfBicMissing" doc/guides/bank-profiles.md
```
Expected: multiple hits in `bank-profiles.md`.

```bash
grep -rn "setOmitGroupHeaderControlSum\|setOmitAgentElementIfBicMissing" doc/guides/credit-transfer.md doc/guides/direct-debit.md
```
Expected: no output.

- [ ] **Step 5.5: Commit**

```bash
git add doc/guides/bank-profiles.md doc/guides/credit-transfer.md doc/guides/direct-debit.md
git commit -m "docs: consolidate omit-flag docs into bank-profiles.md

Per spec § 4.2 and § 6.1, the two omit-flag setters
(setOmitGroupHeaderControlSum, setOmitAgentElementIfBicMissing)
were documented as identical 'Additional Features' sections at
the bottom of both credit-transfer.md and direct-debit.md. Pull
both into doc/guides/bank-profiles.md so there is one source of
truth and credit-transfer.md / direct-debit.md stay focused on
the happy path.

Phase 2 will add the per-bank recipes (German DK, Spanish
initiating-party, NL Rabo, AT Raiffeisen, AT Volksbank)."
```

---

## Task 6: Move ISO 20022 naming reference out of the project README

The project README currently carries an "ISO20022 Message Names" sub-section (lines 81–89). Spec § 6.2 says this moves to `doc/reference/iso20022-naming.md`.

**Files:**
- Create: `doc/reference/iso20022-naming.md`
- Delete: `doc/reference/classes/.gitkeep` is **not** affected — `doc/reference/` is no longer empty after this task, but `doc/reference/classes/` still is.
- Modify: `README.md` (remove section, leave a one-line pointer)

- [ ] **Step 6.1: Create `doc/reference/iso20022-naming.md`**

Write:

````markdown
# ISO 20022 Message Names

ISO 20022 messages follow a four-part naming convention:

```
MessageType.MessageSubType.MessageVariant.MessageVersion
```

## Example: `pain.001.001.12`

- **MessageType:** `pain` — Payments Initiation
- **MessageSubType:** `001` — CustomerCreditTransferInitiation
- **MessageVariant:** `001`
- **MessageVersion:** `12`

## External references

- [ISO 20022 official site](https://www.iso20022.org/)
- [ISO 20022 message catalogue](https://www.iso20022.org/full_catalogue.page)
- [ISO 20022 in xmldation's wiki](https://wiki.xmldation.com/General_Information/ISO_20022)
````

- [ ] **Step 6.2: Remove the section from project `README.md`**

Today the project README contains:

```markdown
### ISO20022 Message Names
ISO20022 messages follow a specific naming convention which can be denoted to the following pattern:
`MessageType.MessageSubType.MessageVariant.MessageVersion`

For e.g. pain.001.001.12 should be decoded as:
- MessageType: 'PAIN' - Payments Initiation
- MessageSubType: '001' - CustomerCreditTransferInitiation
- MessageVariant: '001'
- MessageVersion: '12'
```

Replace the entire `### ISO20022 Message Names` block (heading + body, 9 lines) with:

```markdown
### ISO 20022 Message Names
See [reference/iso20022-naming.md](doc/reference/iso20022-naming.md).
```

- [ ] **Step 6.3: Verify**

```bash
grep -n "MessageType\|MessageSubType" README.md
```
Expected: no output.

```bash
grep -n "MessageSubType" doc/reference/iso20022-naming.md
```
Expected: one match.

- [ ] **Step 6.4: Commit**

```bash
git add doc/reference/iso20022-naming.md README.md
git commit -m "docs: move ISO 20022 naming guide out of project README

Per spec § 6.2. The README keeps a one-line pointer; the
expanded reference lives at doc/reference/iso20022-naming.md
where Phase 2 / 3 will add cross-links to the version matrix."
```

---

## Task 7: Replace the project README "Documentation" pointer

Spec § 6.2 calls for replacing today's 3-bullet documentation list (lines 75–80) with a two-line pointer at the new entry point.

**Files:**
- Modify: `README.md`
- Create: `doc/README.md` (the entry point the pointer references)

`doc/README.md` is the *index*; Phase 2 expands it. Phase 1 just needs it to exist with a working set of links so the project README pointer doesn't 404.

- [ ] **Step 7.1: Create `doc/README.md`**

Write:

````markdown
# php-sepa-xml Documentation

> Phase 1 restructure landed. Phase 2 (new content) and Phase 3
> (cross-page polish + frontmatter / at-a-glance boxes) are
> upcoming.

## Guides

- [Credit Transfer](guides/credit-transfer.md)
- [Direct Debit](guides/direct-debit.md)
- [Custom sanitization](guides/custom-sanitization.md)
- [Group Header](guides/group-header.md)
- [Addresses](guides/addresses.md)
- [Amendments](guides/amendments.md)
- [Bank profiles](guides/bank-profiles.md)

## Reference

- [ISO 20022 message names](reference/iso20022-naming.md)

## Contributing

- [Contributing](contributing.md)
````

- [ ] **Step 7.2: Replace the project README "Documentation" section**

Today the project README contains (lines 75–80):

```markdown
## Documentation
Check out our docs at:
* [handling Direct Debits](doc/direct_debit.md)
* [handling Credit Transfers](doc/credit_transfer.md)
* [handling string sanitization](doc/string_sanitization.md)
```

Replace with:

```markdown
## Documentation
Full documentation lives in [`doc/`](doc/README.md). Start with the
[guides directory](doc/guides/) or jump straight to
[Credit Transfer](doc/guides/credit-transfer.md) or
[Direct Debit](doc/guides/direct-debit.md).
```

- [ ] **Step 7.3: Update the "Development" pointer at the bottom of the README**

Today line 93 reads:
```markdown
Want to contribute? Please check out our [Dev docs](doc/dev_setup.md)
```

Replace with:
```markdown
Want to contribute? Please check out our [Contributing guide](doc/contributing.md)
```

- [ ] **Step 7.4: Verify there are no broken pointers from the project README**

```bash
grep -nE "doc/(direct_debit|credit_transfer|string_sanitization|dev_setup)\.md" README.md
```
Expected: no output.

- [ ] **Step 7.5: Commit**

```bash
git add README.md doc/README.md
git commit -m "docs: point project README at new doc/ entry point

Per spec § 6.2. Replaces the inline 3-bullet 'Documentation'
list with a two-line pointer to doc/README.md, which serves as
the new entry point. dev_setup.md pointer is also updated to
the new contributing.md path.

doc/README.md is a Phase 1 minimal index; Phase 2 will expand
it with reading-order suggestions per spec § 4.1."
```

---

## Task 8: Validate every internal Markdown link in `doc/` and `README.md`

After all the moves and splits, every `[...](...)` link must still resolve. The acceptance check uses `markdown-link-check` from npm; `npx` fetches it on demand so no install commit is needed.

- [ ] **Step 8.1: Run the link checker**

Run:
```bash
npx --yes markdown-link-check -q README.md
find doc -name '*.md' -print0 | xargs -0 -I{} npx --yes markdown-link-check -q {}
```

Expected: every file reports `0 dead links found`. External links (iso20022.org, xmldation, etc.) are allowed to time out — flag only failures for *relative* paths or anchors. If a relative link fails:

1. `git grep -n "<broken-target>" doc/ README.md` to find every caller.
2. Decide whether the link should point at the new path, an anchor, or be removed.
3. Fix and re-run the checker.

- [ ] **Step 8.2: Spot-check by visually browsing**

Run:
```bash
git log --stat -10
```
Expected: 7 commits (Tasks 1–7), each affecting only the files listed in its task description.

Open `README.md`, `doc/README.md`, `doc/guides/credit-transfer.md`, `doc/guides/direct-debit.md` and confirm each set of intra-doc links resolves (GitHub renders them, or use a Markdown previewer).

- [ ] **Step 8.3: Commit any link fixes (if any were needed)**

If Step 8.1 forced fixes:
```bash
git add doc/ README.md
git commit -m "docs: fix internal links broken by Phase 1 restructure

Discovered by markdown-link-check after the renames in commits
<sha>..<sha>."
```

If no fixes were needed, skip this commit.

---

## Task 9: Final inventory check vs. spec acceptance criteria

Spec § 7 lists eight acceptance criteria. This task verifies each one *that is in Phase 1 scope*. Items requiring Phase 2/3 content (`gotchas.md` seed entries, version matrix coverage, getting-started page) are explicitly deferred.

- [ ] **Step 9.1: File-existence check**

Run:
```bash
ls doc/README.md \
   doc/guides/credit-transfer.md \
   doc/guides/direct-debit.md \
   doc/guides/custom-sanitization.md \
   doc/guides/group-header.md \
   doc/guides/addresses.md \
   doc/guides/amendments.md \
   doc/guides/bank-profiles.md \
   doc/reference/iso20022-naming.md \
   doc/contributing.md \
   doc/assets/structure.dia \
   doc/assets/structure.png
```
Expected: every path lists without error.

- [ ] **Step 9.2: No-content-loss check**

Run:
```bash
git show HEAD~7:doc/credit_transfer.md | wc -l
git show HEAD~7:doc/direct_debit.md | wc -l
git show HEAD~7:doc/string_sanitization.md | wc -l
```
Expected: 107, 216, 30 (matches the pre-flight counts). Then spot-check that distinctive strings from each original survived somewhere in `doc/`:

```bash
git grep -l "createDirectDebitWithGroupHeader" doc/   # should hit group-header.md
git grep -l "originalMandateId" doc/                  # should hit amendments.md
git grep -l "debtorCountry" doc/                      # should hit addresses.md
git grep -l "Disable the sanitizer globally" doc/     # should hit custom-sanitization.md
git grep -lF 'Digitick\Sepa\Util\Sanitizer' doc/      # should hit custom-sanitization.md (fixed namespace)
git grep -l "setOmitGroupHeaderControlSum" doc/       # should hit bank-profiles.md only
git grep -l "MessageSubType" doc/                     # should hit iso20022-naming.md only
```

- [ ] **Step 9.3: Bug-fix verification**

Run:
```bash
git grep -nF 'SepaXml\Util' doc/                                            # bug 3.1
git grep -A1 -F 'setOmitAgentElementIfBicMissing(true);' doc/ | grep -B1 -F 'setOmitAgentElementIfBicMissing(true);'  # bug 3.2 (should NOT find back-to-back)
git grep -nF 'asXml());.' doc/                                              # bug 3.3
```
Expected: no output for all three.

- [ ] **Step 9.4: README pointer check**

Run:
```bash
grep -nE "doc/README\.md|doc/guides/|doc/contributing\.md" README.md
```
Expected: at least 3 matches (the rewritten Documentation section + the rewritten Development pointer).

- [ ] **Step 9.5: Final summary commit (no-op if no fixes were needed in 9.x)**

If any of 9.1–9.4 forced fixes, commit them. Otherwise the branch is ready for PR.

---

## Hand-off to next phases

After this plan completes:

- The branch `docs/restructure-phase-1` contains ~7 commits ready for PR.
- Phase 2 (writing new content) is the next plan to author. Inputs: this branch merged, the design spec, and the institutional list in the project README.
- Phase 3 (cross-page conventions per spec § 5) is the final plan.

## Notes for the executing agent

- **Use `git mv`, not `mv`**, for every Task 2 rename so git tracks history.
- **Re-verify line numbers** with `grep -n` before every `sed -i '<a>,<b>d'`. Earlier tasks may have shifted them.
- **Do not write any new prose** beyond what is shown verbatim in this plan. Phase 1 is a restructure, not a content drop.
- **Markdown fence balancing** in Tasks 4.1, 4.2, 4.3, 5.1, 6.1, 7.1 — those files contain `php` fenced blocks inside Markdown files. When copy-pasting from this plan, count opening and closing ``` ``` ``` carefully.
- **Commit messages** in this plan are deliberately long and include spec section references. Keep them — they're the audit trail for the no-content-loss check in Task 9.2.
