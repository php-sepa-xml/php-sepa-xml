---
title: "Facades (CustomerCreditFacade, CustomerDirectDebitFacade, TransferFileFacadeFactory)"
description: "Public surface of the facade trio — factory methods and supported array keys for addPaymentInfo and addTransfer."
---

# Facades (`CustomerCreditFacade`, `CustomerDirectDebitFacade`, `TransferFileFacadeFactory`)

**Namespace:** `Digitick\Sepa\TransferFile\Facade\*` and `Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory`
**Files:** `src/TransferFile/Facade/{CustomerCreditFacade,CustomerDirectDebitFacade,BaseCustomerTransferFileFacade}.php`, `src/TransferFile/Factory/TransferFileFacadeFactory.php`

The facade collapses the four-class assembly (`GroupHeader` + `TransferFile` + `PaymentInformation` + `Transfer`) into two associative-array calls. Trade-off: less control over the XML, less explicit type safety, no access to the `GroupHeader` between construction and rendering (unless you use the `*WithGroupHeader` factory variant).

## `TransferFileFacadeFactory`

Four static methods. The `WithGroupHeader` variants exist for when you want a customised `GroupHeader` (e.g. with `isTest=true` or with `setInitiatingPartyId`).

```php
TransferFileFacadeFactory::createCustomerCredit(
    string $uniqueMessageIdentification,
    string $initiatingPartyName,
    string $painFormat = 'pain.001.001.09'
): CustomerCreditFacade

TransferFileFacadeFactory::createCustomerCreditWithGroupHeader(
    GroupHeader $groupHeader,
    string $painFormat = 'pain.001.001.09',
    bool $withSchemaLocation = true
): CustomerCreditFacade

TransferFileFacadeFactory::createDirectDebit(
    string $uniqueMessageIdentification,
    string $initiatingPartyName,
    string $painFormat = 'pain.008.001.09'
): CustomerDirectDebitFacade

TransferFileFacadeFactory::createDirectDebitWithGroupHeader(
    GroupHeader $groupHeader,
    string $painFormat = 'pain.008.001.09'
): CustomerDirectDebitFacade
```

The `@TODO` in source notes the credit-transfer methods will be renamed in v3.0 to `createCustomerCreditTransfer(...)`.

## `CustomerCreditFacade::addPaymentInfo(string $paymentName, array $info): PaymentInformation`

Supported array keys (from the PHPDoc shape):

| Key                  | Required | Notes |
|----------------------|----------|-------|
| `id`                 | yes      | `<PmtInfId>` |
| `debtorName`         | yes      | originator name |
| `debtorAccountIBAN`  | yes      | originator IBAN |
| `debtorAgentBIC`     | no       | originator BIC; null-able for newer pain |
| `dueDate`            | no       | `string` or `DateTimeInterface`; defaults to "now" |
| `batchBooking`       | no       | bool, default `false` |

Returns the underlying `PaymentInformation`. Throws `InvalidArgumentException` if `$paymentName` is already registered. Throws `\LogicException` if `asXML()`/`asDOC()` has already been called.

## `CustomerCreditFacade::addTransfer(string $paymentName, array $info): TransferInformationInterface`

Supported keys:

| Key                       | Required | Notes |
|---------------------------|----------|-------|
| `amount`                  | yes      | **int cents** |
| `creditorIban`            | yes      | |
| `creditorName`            | yes      | |
| `creditorBic`             | no       | |
| `remittanceInformation`   | yes\*    | unless `creditorReference` is set (mutually exclusive) |
| `creditorReference`       | no       | structured reference; suppresses `remittanceInformation` |
| `creditorReferenceType`   | no       | |
| `endToEndId`              | no       | defaults to `paymentName + transferIndex` |
| `instructionId`           | no       | |
| `postCode`, `townName`, `streetName`, `buildingNumber`, `floorNumber`, `debtorCountry`, `debtorAdrLine` | no | address cascade |

Throws `InvalidArgumentException` if `$paymentName` isn't registered. Throws `\LogicException` if the facade has been finalised.

## `CustomerDirectDebitFacade::addPaymentInfo(string $paymentName, array $info): PaymentInformation`

| Key                   | Required | Notes |
|-----------------------|----------|-------|
| `id`                  | yes      | |
| `creditorName`        | yes      | initiator name |
| `creditorAccountIBAN` | yes      | |
| `creditorAgentBIC`    | no       | |
| `seqType`             | yes      | one of `PaymentInformation::S_FIRST|S_RECURRING|S_ONEOFF|S_FINAL` |
| `creditorId`          | yes      | SEPA Creditor Identifier |
| `localInstrumentCode` | no       | `CORE`, `B2B`, `COR1` |
| `batchBooking`        | no       | |
| `dueDate`             | no       | defaults to "now + 5 days" |

## `CustomerDirectDebitFacade::addTransfer(string $paymentName, array $info): TransferInformationInterface`

| Key                      | Required | Notes |
|--------------------------|----------|-------|
| `amount`                 | yes      | int cents |
| `debtorIban`             | yes      | |
| `debtorName`             | yes      | |
| `debtorBic`              | no       | |
| `debtorMandate`          | yes      | mandate ID |
| `debtorMandateSignDate`  | yes      | `string` or `DateTimeInterface`; string form is `d.m.Y` |
| `remittanceInformation`  | yes\*    | unless `creditorReference` set |
| `creditorReference`      | no       | |
| `endToEndId`             | no       | |
| `instructionId`          | no       | |
| `originalMandateId`, `originalDebtorIban`, `amendedDebtorAccount` | no | amendment cascade |
| `postCode`, `townName`, `streetName`, `buildingNumber`, `floorNumber`, `debtorCountry`, `debtorAdrLine` | no | address cascade |
| `ultimateDebtorName`     | no       | when the actual debtor differs from the account holder |

## Inherited methods (from `BaseCustomerTransferFileFacade`)

### `asXML(): string`

Renders the document.

> ⚠️ **Gotcha**
>
> Finalises the facade — subsequent `addPaymentInfo`/`addTransfer` calls throw `\LogicException`. Subsequent `asXML()` calls return a cached string. See [Gotchas: facades are single-shot](../../gotchas.md#facades-are-single-shot--asxml-finalises-them).

### `asDOC(): DOMDocument`

Same finalisation semantics; returns the `DOMDocument` for in-memory manipulation (signing, encryption, custom mutation).

### `getPaymentInfo(string $paymentName): ?PaymentInformation`

Look up an already-added `PaymentInformation` by its facade-side name. Returns `null` if unknown.

### `setOmitGroupHeaderControlSum(bool $omit): void`

Forwards to the underlying `BaseDomBuilder`. Required by some bank profiles (German DK pain.001.001.03).

### `setOmitAgentElementIfBicMissing(bool $omit): void`

Forwards to the DomBuilder. Suppresses `<CdtrAgt>`/`<DbtrAgt>` when BIC is absent (instead of emitting `NOTPROVIDED`).

### `createDueDateFromPaymentInformation(array $info, string $timestamp = 'now'): DateTimeInterface`

Helper used internally; surface-public. Accepts either a `DateTimeInterface` or a string parseable by `DateTimeImmutable`. Throws `InvalidArgumentException` on bad input.

## Related

- [Reference: GroupHeader](group-header.md)
- [Reference: PaymentInformation](payment-information.md)
- [Reference: DomBuilder](dom-builder.md)
- [Guide: Choosing facade vs direct](../../guides/choosing-facade-vs-direct.md)
- [Guide: Credit Transfer](../../guides/credit-transfer.md)
- [Guide: Direct Debit](../../guides/direct-debit.md)
