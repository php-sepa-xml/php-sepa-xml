---
title: "Group Header"
description: "Customise the message-level GroupHeader: identifiers, party metadata, and the test indicator."
---

# Group Header

> **At a glance**
>
> - **Use this when:** you need a custom `<MsgId>`, `InitgPty/Id`, or test indicator that the facade default doesn't expose.
> - **Key types:** `GroupHeader`, `TransferFileFacadeFactory::createCustomerCreditWithGroupHeader` / `createDirectDebitWithGroupHeader`.
> - **Output:** a configured `GroupHeader` instance attached to a facade or transfer file.

The `GroupHeader` controls the file-level metadata: `MsgId`, `CreDtTm`,
`InitgPty/Nm`, and `InitgPty/Id`. The Facade auto-creates a `GroupHeader`
from the constructor arguments, but you can pass your own when you need a
custom `InitiatingPartyId` (common with Spanish banks) or a deterministic
`MsgId`.

> 🏦 **Bank profile — Spanish banks**
>
> Spanish institutions typically require `InitgPty/Id` (set via
> `GroupHeader::setInitiatingPartyId(...)`) populated with the company's
> SEPA Creditor Identifier. Use `TransferFileFacadeFactory::createDirectDebitWithGroupHeader`
> or `createCustomerCreditWithGroupHeader` so you can pre-configure the header.

> ⚠️ **Gotcha**
>
> `MsgId` is used by banks as a duplicate-submission key. Two files with
> the same `MsgId` submitted to the same bank on the same day may have one
> silently dropped. Use a timestamp or unique reference rather than a
> hard-coded constant. See [Gotchas: MsgId is a duplicate key](../gotchas.md#msgid-is-a-bank-side-duplicate-submission-key).

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

## Related

- [Reference: GroupHeader](../reference/classes/group-header.md)
- [Bank profiles](bank-profiles.md) — Spanish banks and similar that need a custom `InitgPty/Id`
- [Gotchas: MsgId duplicate-key](../gotchas.md#msgid-is-a-bank-side-duplicate-submission-key)
