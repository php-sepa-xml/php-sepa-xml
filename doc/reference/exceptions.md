# Exceptions

All library exceptions live under `Digitick\Sepa\Exception`. They extend the namespaced base `Digitick\Sepa\Exception\Exception`, which in turn extends PHP's `\Exception`. A small number of code paths also throw the PHP built-ins `\LogicException` and `\InvalidArgumentException` — those are listed at the end.

## `Digitick\Sepa\Exception\Exception`

**Extends:** `\Exception`

Base class for everything in the namespace. Catch this when you want a single catch-all for SEPA-side failures.

## `Digitick\Sepa\Exception\InvalidArgumentException`

**Extends:** `Digitick\Sepa\Exception\Exception`

Thrown when caller-supplied data is rejected before any XML is written. Typical triggers:

- Invalid pain message name passed to `MessageFormat::__construct` (does not match `pain.NNN.NNN.NN`).
- Invalid `paymentMethod`, `localInstrumentCode`, or `instructionPriority` on `PaymentInformation`.
- Reusing a `$paymentName` already added on a facade (`CustomerCreditFacade::addPaymentInfo` / `CustomerDirectDebitFacade::addPaymentInfo`).
- `addTransfer` called with a `$paymentName` that doesn't exist on the facade.
- Invalid `dueDate` passed to `BaseCustomerTransferFileFacade::createDueDateFromPaymentInformation` (wraps the underlying `DateTime` failure).
- `DomBuilderFactory::createDomBuilder` called with an object that isn't a recognised `TransferFile` subclass.

## `Digitick\Sepa\Exception\InvalidPaymentMethodException`

**Extends:** `Digitick\Sepa\Exception\Exception`

Defined but currently unreferenced from production code paths. Reserved for future strict-mode payment-method validation.

## `Digitick\Sepa\Exception\InvalidTransferFileConfiguration`

**Extends:** `Digitick\Sepa\Exception\Exception`

Thrown by `validate()` on the transfer files when the assembled object graph is incomplete:

- `BaseTransferFile::validate` — no `PaymentInformation` attached.
- `CustomerCreditTransferFile::validate` — a `PaymentInformation` has zero transfers.
- `CustomerDirectDebitTransferFile::validate` — `PaymentInformation` missing `sequenceType` or `creditorId`.

Run `validate()` before serialising if you want this surfaced; the DomBuilder will otherwise produce technically-XML-shaped but functionally invalid output.

## `Digitick\Sepa\Exception\InvalidTransferTypeException`

**Extends:** `Digitick\Sepa\Exception\Exception`

Thrown when a `PaymentInformation` declares a payment method incompatible with the enclosing transfer file (e.g. a `PaymentInformation` with `paymentMethod=DD` added to a `CustomerCreditTransferFile`, or vice versa). Surfaced via the same `validate()` call.

## PHP built-ins thrown by the library

- `\LogicException` — `BaseCustomerTransferFileFacade::asDOC` if the facade is asked for output before any payment info is added; `CustomerDirectDebitTransferDomBuilder` when transaction info is visited before its parent payment info.
- `\InvalidArgumentException` (PHP built-in, not the namespaced one) — `CustomerDirectDebitTransferDomBuilder` for some inner-state assertions.

Catch `\Throwable` if you want a guarantee of total coverage; otherwise catch `Digitick\Sepa\Exception\Exception` plus `\LogicException`.

## See also

- [Guide: Output and validation](../guides/output-and-validation.md)
- [Gotchas](../gotchas.md)
