# CustomerCreditTransferFile

**Namespace:** `Digitick\Sepa\TransferFile\CustomerCreditTransferFile`
**File:** `src/TransferFile/CustomerCreditTransferFile.php`

Root container for a `pain.001.*` credit transfer message. Wraps a `GroupHeader` plus one or more `PaymentInformation` blocks. Extends `BaseTransferFile`.

## Constructor

```php
public function __construct(GroupHeader $groupHeader)
```

Inherited from `BaseTransferFile`. Holds the `GroupHeader` you pass in; aggregated counters (number of transactions, control sum) are accumulated on it as you call `addPaymentInformation`.

## Key methods

### `addPaymentInformation(PaymentInformation $paymentInformation): void`

Force-sets `paymentMethod` to `TRF` on the payment (and restricts the valid set to `['TRF']`) before delegating to the parent's accumulator. Updating `$paymentInformation->getNumberOfTransactions()` and `getControlSumCents()` happens here — make sure you've called `addTransfer()` on the `PaymentInformation` before attaching it, otherwise the header totals will be wrong.

### `getGroupHeader(): GroupHeader`

Inherited. Returns the same `GroupHeader` instance you constructed with.

### `validate(): void`

Runs as part of `accept(DomBuilder)` — you don't normally call it directly. Enforces:

- At least one `PaymentInformation` is attached (inherited check).
- Each `PaymentInformation` contains at least one transfer. Throws `InvalidTransferFileConfiguration` otherwise.
- Every transfer is an instance of `CustomerCreditTransferInformation`. Throws `InvalidTransferTypeException` if a direct-debit transfer leaks in.

### `accept(DomBuilderInterface $domBuilder): void`

Inherited. Invokes `validate()`, then walks the file → group header → payment infos, dispatching to the builder.

## Related

- [Reference: PaymentInformation](payment-information.md)
- [Reference: CustomerCreditTransferInformation](customer-credit-transfer-information.md)
- [Reference: DomBuilder](dom-builder.md)
- [Guide: Credit Transfer](../../guides/credit-transfer.md)
