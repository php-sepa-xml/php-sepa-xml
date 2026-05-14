---
title: "GroupHeader"
description: "Public surface of Digitick\\Sepa\\GroupHeader — constructor and key methods."
---

# GroupHeader

**Namespace:** `Digitick\Sepa\GroupHeader`
**File:** `src/GroupHeader.php`

Represents the `<GrpHdr>` block — one per ISO 20022 message. Carries the message-level identifiers and the aggregated counters (number of transactions, control sum) that the DomBuilder fills in as it walks the tree. You construct one explicitly when using the direct flow; the facades build a default `GroupHeader` for you (unless you use the `*WithGroupHeader` factory variant).

## Constructor

```php
public function __construct(
    string $messageIdentification,
    string $initiatingPartyName,
    bool $isTest = false
)
```

- **`$messageIdentification`** — `<MsgId>`. Max 35 chars. Should be unique per bulk submission to the same bank on the same day (banks use it as the duplicate-detection key). Per the factory docstring, the first 8 or 11 characters of `<MsgId>` should match the BIC of the Instructing Agent for some bank profiles; the rest is free-form.
- **`$initiatingPartyName`** — `<InitgPty><Nm>`. The legal name of the party initiating the message (typically your company).
- **`$isTest`** — when true, emits `<GrpHdr>` with `<TstInd>true</TstInd>` (or equivalent for the message version). Most banks ignore this for routing; some staging environments check it.

## Key methods

### `setControlSumCents(int $controlSumCents): void`

Sets `<CtrlSum>` in cents. Normally populated automatically by `BaseTransferFile::addPaymentInformation` as you attach payments — only call this directly if you're building the header in isolation.

### `setNumberOfTransactions(int $numberOfTransactions): void`

Sets `<NbOfTxs>`. Same caveat as `setControlSumCents` — auto-populated under normal flows.

### `setInitiatingPartyId(string $initiatingPartyId): void` + `setInitiatingPartyIdentificationScheme(string $scheme): void`

Adds an `<Id><OrgId><Othr><Id>` block under `<InitgPty>`. Use this when the bank requires a structured identifier (e.g. a customer number or KvK). The scheme string is emitted as `<SchmeNm><Prtry>` and is free-form.

### `setIssuer(string $issuer): void`

Optional `<Issr>` under the same `<Othr>` block. Identifies the scheme issuer when relevant.

### `setCreationDateTimeFormat(string $format): void`

The `<CreDtTm>` is generated automatically. Override its format only if your bank requires a non-standard one (default is ISO 8601 with timezone). Pass a PHP `date()`-compatible format string.

### `setIsTest(bool $isTest): void`

Toggles `<TstInd>` post-construction.

## Related

- [Guide: Group Header](../../guides/group-header.md) — using a custom header with the facade
- [Reference: CustomerCreditTransferFile](customer-credit-transfer-file.md)
- [Reference: CustomerDirectDebitTransferFile](customer-direct-debit-file.md)
