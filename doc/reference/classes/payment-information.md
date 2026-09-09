---
title: "PaymentInformation"
description: "Public surface of Digitick\\Sepa\\PaymentInformation — constructor, sequence-type constants, key methods."
---

# PaymentInformation

**Namespace:** `Digitick\Sepa\PaymentInformation`
**File:** `src/PaymentInformation.php`

One `PaymentInformation` (`<PmtInf>` block) groups a set of transfers sharing the same originating account, payment method, sequence type, and processing options. A `TransferFile` may contain multiple `PaymentInformation` blocks — split them whenever a group-level attribute (sequence type, due date, batch booking) differs.

## Constructor

```php
public function __construct(
    string $id,
    string $originAccountIBAN,
    ?string $originAgentBIC,
    string $originName,
    string $originAccountCurrency = 'EUR'
)
```

- **`$id`** — `<PmtInfId>`. Caller-defined identifier for this payment-information block. Max 35 chars.
- **`$originAccountIBAN`** — the debtor IBAN (credit transfer) or creditor IBAN (direct debit). Renamed contextually by the DomBuilder.
- **`$originAgentBIC`** — BIC of the originating bank. `null` is acceptable for newer pain versions; combine with `BaseDomBuilder::setOmitAgentElementIfBicMissing(true)` to suppress the `<Agt>` element entirely.
- **`$originName`** — the party name on the originating account.
- **`$originAccountCurrency`** — three-letter ISO currency. SEPA payments are EUR; only override for multi-currency wrappers.

## Sequence type constants (direct debit only)

```php
PaymentInformation::S_FIRST     // 'FRST'
PaymentInformation::S_RECURRING // 'RCUR'
PaymentInformation::S_ONEOFF    // 'OOFF'
PaymentInformation::S_FINAL     // 'FNAL'
```

Pass one of these to `setSequenceType()`. The sequence type is **per-`PaymentInformation`**, not per-transfer — see [Gotchas](../../gotchas.md).

## Key methods

### `addTransfer(TransferInformationInterface $transfer): void`

Append a transfer. Each call updates the running `numberOfTransactions` and `controlSumCents` totals on this block.

### `setSequenceType(string $sequenceType): void`

Required for direct debits. Use one of the four `S_*` constants. Throws nothing locally, but `CustomerDirectDebitTransferFile::validate()` enforces presence.

### `setLocalInstrumentCode(string $localInstrumentCode): void`

`<LclInstrm><Cd>`. Common values: `CORE`, `B2B`, `COR1` (direct debit); `INST` (instant credit transfer). Validated against the list set by `setValidPaymentMethods()` — throws `InvalidArgumentException` if unrecognised.

### `setCreditorId(string $creditorSchemeId): void`

Required for direct debits. Your company's SEPA Creditor Identifier (e.g. `DE21WVM1234567890`). Validated by `CustomerDirectDebitTransferFile::validate()`.

### `setBatchBooking(bool $batchBooking): void`

`<BtchBookg>`. When `true`, the bank should book the bulk as a single line on the originator's statement. When `false`, one line per transfer.

### `setDueDate(DateTimeInterface $dueDate): void`

`<ReqdExctnDt>` for credit transfers; `<ReqdColltnDt>` for direct debits. Defaults to the day of file generation when unset.

### `setCategoryPurposeCode(string $categoryPurposeCode): void`

`<CtgyPurp><Cd>`. ISO 20022 purpose code (e.g. `SALA` for salary, `SUPP` for supplier payment).

### `setMandateSignDate(DateTimeInterface $mandateSignDate): void`

Direct debit. Sets a fallback mandate signature date used when a transfer doesn't provide its own.

## Related

- [Guide: Credit Transfer](../../guides/credit-transfer.md)
- [Guide: Direct Debit](../../guides/direct-debit.md)
- [Reference: CustomerCreditTransferInformation](customer-credit-transfer-information.md)
- [Reference: CustomerDirectDebitTransferInformation](customer-direct-debit-transfer-information.md)
