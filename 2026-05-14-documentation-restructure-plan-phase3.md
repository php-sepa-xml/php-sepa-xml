# Phase 3 Plan — Documentation Restructure

**Date:** 2026-05-14
**Branch:** `docs/restructure-phase-1` (continuing in the same branch — Phase 3 commits stack on top of Phase 2)
**Target rendering:** GitHub Flavored Markdown only. No site generator yet — syntax stays compatible with any future MkDocs / Docusaurus port via a thin transform.
**Frontmatter scope:** Minimal — `title` + `description`.
**Gotchas index:** Hand-maintained. Per-page callouts cross-link to `doc/gotchas.md`; that file is the canonical list.
**Scope:** Cross-page polish — apply conventions consistently across the 26 pages that exist after Phases 1–2.
**Out of scope:** New content. Auto-building tooling. Site generator setup.

---

## Conventions locked in for Phase 3

### Frontmatter (every page)

YAML at top of file, exactly two fields:

```markdown
---
title: <Human Title>
description: <one-line summary used by future SSG / search index>
---
```

Keep `title` matching the first H1 in the body. `description` is the same content you'd put in an OpenGraph card — one sentence, ~140 chars max.

### Headings

ATX-style only (`# H1`, `## H2`, `### H3`). Phase 1 carried Setext-style (`Title\n=====`) headings in two files — those get converted.

### Callouts (GitHub-Flavored blockquote convention)

Three callout types. All use GFM blockquote with an emoji + bold label on the first line. Renders cleanly on github.com and degrades to plain blockquote in any tool that doesn't recognise the convention.

**⚠️ Gotcha** — non-obvious failure mode the reader needs to know about.

```markdown
> ⚠️ **Gotcha**
>
> Amounts are integer cents, not floats. `500` is 5.00 EUR. See
> [Gotchas](../gotchas.md) for the full list.
```

**ℹ️ Version note** — something specific to a pain version or library version.

```markdown
> ℹ️ **Version note**
>
> The `UETR` field is only emitted from library v2.3.0 onwards.
```

**🏦 Bank profile** — bank-specific quirk the user can opt into.

```markdown
> 🏦 **Bank profile**
>
> German DK requires `setOmitGroupHeaderControlSum(true)` for
> pain.001.001.03. See [Bank profiles](../guides/bank-profiles.md).
```

Each callout that surfaces a sharp edge MUST link to `doc/gotchas.md` (Gotcha) or the relevant guide. If a callout describes a Gotcha-worthy item not yet in `doc/gotchas.md`, add the entry there — gotchas.md is the canonical index.

### "At a glance" box (guides only)

First content block on each guide page (after frontmatter and H1, before any other prose). Markdown blockquote with three sub-points: **Use this when**, **Key types**, **Output**.

```markdown
> **At a glance**
>
> - **Use this when:** building a SEPA Credit Transfer file.
> - **Key types:** `CustomerCreditFacade`, `CustomerCreditTransferFile`, `CustomerCreditTransferInformation`.
> - **Output:** `pain.001.*` XML via `asXML()`.
```

Skip "At a glance" on reference pages and top-level pages (getting-started, gotchas, migrations) — the H1 + first paragraph already serve that role there.

### Per-page footer

Every page ends with a `## Related` section (already present on most pages from Phase 2). Standardise the structure to one or two of:

- `## Related` — cross-links inside `doc/`
- `## External resources` — links to ISO, bank docs, etc. (only where actually relevant)

No "Reference" sub-heading separate from "Related" — collapse those into one list.

---

## File inventory (26 pages)

**Top-level (5):**
- `doc/README.md` (index — no frontmatter; it's the index)
- `doc/getting-started.md`
- `doc/gotchas.md`
- `doc/migrations.md`
- `doc/contributing.md`

**Guides (9):**
- `doc/guides/credit-transfer.md` *(Setext → ATX)*
- `doc/guides/direct-debit.md` *(Setext → ATX)*
- `doc/guides/custom-sanitization.md`
- `doc/guides/group-header.md`
- `doc/guides/addresses.md`
- `doc/guides/amendments.md`
- `doc/guides/bank-profiles.md`
- `doc/guides/choosing-facade-vs-direct.md`
- `doc/guides/output-and-validation.md`

**Reference (3 non-class + 9 class = 12):**
- `doc/reference/pain-version-matrix.md`
- `doc/reference/iso20022-naming.md`
- `doc/reference/exceptions.md`
- `doc/reference/classes/group-header.md`
- `doc/reference/classes/payment-information.md`
- `doc/reference/classes/customer-credit-transfer-file.md`
- `doc/reference/classes/customer-direct-debit-file.md`
- `doc/reference/classes/customer-credit-transfer-information.md`
- `doc/reference/classes/customer-direct-debit-transfer-information.md`
- `doc/reference/classes/facade.md`
- `doc/reference/classes/dom-builder.md`
- `doc/reference/classes/sanitizer.md`

**Frontmatter targets:** 25 (skip `doc/README.md`).
**ATX conversion targets:** 2 (`credit-transfer.md`, `direct-debit.md`).
**At-a-glance targets:** 9 (every guide).
**Callout sweep targets:** all 25 content pages, but most edits will cluster in the guides and the migrations / gotchas pages.

---

## Working order

Three commits. The first is mechanical (frontmatter + ATX), the second is the substantive callout sweep, the third is structural polish.

### Commit 1 — Frontmatter on every page + Setext → ATX conversion

Tasks:

1.1. Convert `doc/guides/credit-transfer.md` from Setext to ATX.
- Find: `^(.+)\n=+$` → `# $1`
- Find: `^(.+)\n-+$` → `## $1`
- Spot-check no `---` HR rules get rewritten as H2 (none on those pages, but verify).
- Re-check the anchor links in the TOC at the top still resolve (GitHub auto-generates anchors from ATX headings the same way).

1.2. Convert `doc/guides/direct-debit.md` from Setext to ATX. Same recipe.

1.3. Add frontmatter to each of the 25 pages (skip `doc/README.md`). Use this exact field set:
```markdown
---
title: <matches the body H1, no trailing markdown>
description: <one sentence ~140 chars>
---
```

For each page, the `title` is the existing H1 (verbatim, minus any markdown). The `description` should:
- Open with a verb where natural ("Generate", "Configure", "Resolve") for guides.
- Be a noun-phrase for reference ("Public surface of …", "Catalogue of …").
- Stay under ~140 chars.

1.4. Verify all 25 files start with a valid frontmatter block.
- Command: `for f in $(find doc -name '*.md' -not -name 'README.md'); do head -1 "$f" | grep -q '^---$' || echo "MISSING: $f"; done`
- Expect zero output.

1.5. Verify the two converted guides still render anchors correctly.
- `grep -E '^# |^## ' doc/guides/credit-transfer.md doc/guides/direct-debit.md` — no Setext `===` / `---` after the heading lines.

1.6. Commit.
- Message: `docs: add frontmatter to all pages and convert Setext to ATX`

### Commit 2 — Callout convention sweep

Walk through each page and convert plain-prose warnings, version notes, and bank-specific tips into the `⚠️` / `ℹ️` / `🏦` callout convention. Tasks per page-class:

2.1. **Guides — credit-transfer.md, direct-debit.md.**
- Inline notes about amount-in-cents → `⚠️ Gotcha` linking to `../gotchas.md`.
- Mentions of "this only works on pain.X" → `ℹ️ Version note`.
- Any bank-specific phrasing ("ING tested on pain.001.001.03", "RABO direct-debit") → `🏦 Bank profile` linking to `bank-profiles.md`.

2.2. **Guides — bank-profiles.md.**
- Each existing bank entry becomes a `🏦 Bank profile` callout block, or stays as a section heading with callouts inside. Keep the structure scannable — don't over-callout.

2.3. **Guides — custom-sanitization.md, group-header.md, addresses.md, amendments.md.**
- Inline gotchas (e.g. "must call all five address setters") → `⚠️ Gotcha`.
- Version-conditioned features → `ℹ️ Version note`.

2.4. **Guides — choosing-facade-vs-direct.md, output-and-validation.md.**
- The single-shot facade callout in `output-and-validation.md` becomes `⚠️ Gotcha`.
- Version notes on `setOmitGroupHeaderControlSum` / `setOmitAgentElementIfBicMissing` → `ℹ️ Version note` *if* they're version-tied (added in 3.x); otherwise drop the version framing.

2.5. **Top-level — getting-started.md.**
- The "Three things to know before you ship" list — convert each item into a `⚠️ Gotcha` callout. Reads better as standalone visual elements than as a numbered list.

2.6. **Top-level — gotchas.md.**
- Re-shape each existing entry as a `⚠️ Gotcha` callout (this page IS the canonical Gotchas list, so the convention is doubly important here).
- Add any new gotchas surfaced during the per-page sweep (each callout in another page that points back to gotchas.md must have a corresponding entry here).

2.7. **Top-level — migrations.md.**
- Breaking-change items in 2→3 and 1→2 sections become `⚠️ Gotcha` callouts inline. The "**Migration:**" lead-in stays as bold text.

2.8. **Reference — exceptions.md, pain-version-matrix.md, iso20022-naming.md.**
- Mostly tables — minimal callout work. `🏦 Bank profile` callouts where the matrix notes "older banks lock to pain.008.001.02".

2.9. **Reference classes — all 9.**
- Each class page's "DD-specific" / "mandate" / "amendment" notes → `⚠️ Gotcha` where they describe a failure mode. Otherwise leave the prose alone.
- `facade.md` already calls out single-shot semantics in prose — promote to `⚠️ Gotcha`.

2.10. After each page edit, verify gotchas.md still has a matching entry for every `⚠️ Gotcha` callout you added (or add one).
- Command: `grep -c '⚠️ \*\*Gotcha\*\*' doc/gotchas.md` should equal or exceed the unique gotchas referenced elsewhere.

2.11. Commit.
- Message: `docs: apply callout convention across all pages`

### Commit 3 — "At a glance" boxes + Related footer cleanup

3.1. Add "At a glance" block to each of the 9 guides, immediately after the H1 (before any other content). Use the three-bullet template (`Use this when`, `Key types`, `Output`). Hand-write each one; do not template.

3.2. Audit `## Related` sections across all 25 content pages:
- Every page has exactly one `## Related` section at the bottom.
- Entries are bullets, each `- [Title](path) — short description`.
- No `## Reference` or `## See also` sub-headings — merge into `Related`.
- Add `## External resources` *only* if there are genuine external links to add (ISO, bank wiki, etc.). Don't pad.

3.3. Walk the doc tree top-down and read each page front-to-back as if you'd never seen it. Note any of:
- Frontmatter missing or wrong shape
- H1 doesn't match `title`
- "At a glance" missing on a guide
- A `⚠️` / `ℹ️` / `🏦` callout that doesn't render cleanly
- Cross-link to a page that no longer exists

Fix in the same commit if quick; defer to a follow-up commit if substantive.

3.4. Final verification:
- `find doc -name '*.md' | xargs head -3 | grep -B1 -A1 '^title:' | head -60` — sanity-check 25 frontmatter blocks.
- `vendor/bin/phpunit` — sanity full-suite run (zero code changed, but proves nothing broke).
- `wc -l doc/**/*.md doc/*.md` — gut-check no page exploded past ~300 lines.

3.5. Commit.
- Message: `docs: add at-a-glance boxes and standardise page footers`

---

## Verification after each commit

- `head -5 <changed-file>` for two random changed files — confirm frontmatter shape.
- Spot-render a guide on github.com (push branch first) to confirm callouts and at-a-glance boxes display as intended.
- After Commit 2: `grep -rE '^> [⚠ℹ🏦]' doc | wc -l` — should be non-trivial (~15+).
- After Commit 3: every guide has an "At a glance" block (visual inspection or grep).

## Out of scope (deferred / not planned)

- Auto-rebuild of `gotchas.md` from per-page callouts — keeping hand-maintained per Phase 3 decision.
- Static site generator setup (MkDocs, Docusaurus). When that happens it becomes Phase 4 and gets its own plan.
- Search index / tag taxonomy. The chosen "minimal frontmatter" decision rules out `tags:` for now.
- API doc autogeneration (phpDocumentor / phpDoc-md). Phase 2 hand-wrote the class reference pages; if we ever want auto-sync, that's a separate effort.
- Translation / i18n.
