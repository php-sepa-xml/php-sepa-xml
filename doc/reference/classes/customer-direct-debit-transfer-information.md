---
title: "CustomerDirectDebitTransferInformation"
description: "Public surface of the per-transfer direct-debit line class, including mandate and amendment setters."
---

# CustomerDirectDebitTransferInformation

**Namespace:** `Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation`
**File:** `src/TransferInformation/CustomerDirectDebitTransferInformation.php`

A single direct debit line (`<DrctDbtTxInf>`). Extends `BaseTransferInformation`; adds mandate handling and amendment support.

## Constructor

```php
public function __construct(
    int $amount,
    string $iban,
    string $name,
    ?string $identification = null
)
```

Inherited from `BaseTransferInformation`.

- **`$amount`** — integer cents.
- **`$iban`** — debtor IBAN (the party being debited).
- **`$name`** — debtor name.
- **`$identification`** — optional initial `EndToEndIdentification`.

## DD-specific methods

### `setMandateId(string $mandateId): void`

`<MndtRltdInf><MndtId>`. The unique mandate reference your debtor signed. Required for SDD — missing values produce empty XML elements that banks will reject.

### `setMandateSignDate(DateTimeInterface $mandateSignDate): void`

`<DtOfSgntr>`. The date the mandate was signed. Note that the facade entry point accepts either a `DateTimeInterface` or a `d.m.Y` string — direct construction requires `DateTimeInterface`. See [Gotchas](../../gotchas.md).

### `setFinalCollectionDate(DateTimeInterface $finalCollectionDate): void`

`<FnlColltnDt>`. Last scheduled collection date for a recurring mandate. Used in combination with `S_FINAL` sequence type.

### Amendment setters

When a mandate has been amended (e.g. debtor account changed), emit the `<AmdmntInd>true</AmdmntInd>` block by combining:

- `setAmendedDebtorAccount(bool $status)` — flags the debtor account itself was amended.
- `setOriginalDebtorIban(string $originalDebtorIban)` — the previous IBAN, emitted under `<OrgnlDbtrAcct>`.
- `setOriginalMandateId(string $originalMandateId)` — the previous mandate reference, emitted under `<OrgnlMndtId>`.

`hasAmendments(): bool` and `hasAmendedDebtorAccount(): bool` report whether the corresponding setters have been called.

See [Guide: Amendments](../../guides/amendments.md).

### `getDebitorName(): string`

DD-specific accessor returning the `name` passed to the constructor. (Note the historical misspelling — "debitor" vs "debtor".)

## Inherited methods worth noting

`setBic`, `setEndToEndIdentification`, `setInstructionId`, `setRemittanceInformation`, `setUltimateDebtorName`, and the address-setter cascade — see [CustomerCreditTransferInformation](customer-credit-transfer-information.md) for shared surface.

## Related

- [Reference: PaymentInformation](payment-information.md) — `setSequenceType` is here, not on the transfer
- [Reference: CustomerDirectDebitTransferFile](customer-direct-debit-file.md)
- [Guide: Direct Debit](../../guides/direct-debit.md)
- [Guide: Amendments](../../guides/amendments.md)
- [Guide: Addresses](../../guides/addresses.md)
