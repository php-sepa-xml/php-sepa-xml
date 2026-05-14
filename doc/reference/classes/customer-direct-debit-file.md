---
title: "CustomerDirectDebitTransferFile"
description: "Public surface of the pain.008.* root container class."
---

# CustomerDirectDebitTransferFile

**Namespace:** `Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile`
**File:** `src/TransferFile/CustomerDirectDebitTransferFile.php`

Root container for a `pain.008.*` direct debit message. Wraps a `GroupHeader` plus one or more `PaymentInformation` blocks. Extends `BaseTransferFile`.

## Constructor

```php
public function __construct(GroupHeader $groupHeader)
```

Inherited from `BaseTransferFile`.

## Key methods

### `addPaymentInformation(PaymentInformation $paymentInformation): void`

Force-sets `paymentMethod` to `DD` on the payment (and restricts the valid set to `['DD']`) before delegating to the parent's accumulator. Note this means a single `PaymentInformation` instance cannot be attached to both a CT file and a DD file in the same session — the valid-methods list gets clobbered.

### `validate(): void`

Runs as part of `accept(DomBuilder)`. Enforces, in addition to the inherited "at least one `PaymentInformation`" check:

- Every `PaymentInformation` has a non-empty sequence type (`setSequenceType` was called). Throws `InvalidTransferFileConfiguration` with message `"Payment must contain a SequenceType"`.
- Every `PaymentInformation` has a non-empty creditor ID (`setCreditorId`). Throws `InvalidTransferFileConfiguration` with `"Payment must contain a CreditorSchemeId"`.
- Every transfer is an instance of `CustomerDirectDebitTransferInformation`. Throws `InvalidTransferTypeException` if a credit-transfer instance leaks in.

Note: per-transfer mandate fields (`mandateId`, `mandateSignDate`) are **not** validated here — the DomBuilder will emit empty elements if you forget them.

### `accept(DomBuilderInterface $domBuilder): void`

Inherited. Same walk as the credit-transfer variant.

## Related

- [Reference: PaymentInformation](payment-information.md)
- [Reference: CustomerDirectDebitTransferInformation](customer-direct-debit-transfer-information.md)
- [Reference: DomBuilder](dom-builder.md)
- [Guide: Direct Debit](../../guides/direct-debit.md)
- [Guide: Amendments](../../guides/amendments.md)
