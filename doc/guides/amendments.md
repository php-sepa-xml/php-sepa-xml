---
title: "Amendments"
description: "Mark a direct debit mandate as amended and supply the original mandate references."
---

# Amendments

Add an amendment to a transfer by passing the amendment fields when calling
`addTransfer` on the named `PaymentInformation` object.

> ⚠️ **Gotcha**
>
> The `amendedDebtorAccount` flag is independent of the original-mandate
> fields. If only the debtor account changed, set `amendedDebtorAccount =>
> true` and `originalDebtorIban`. If the mandate itself was reissued, set
> `originalMandateId`. Combining the two signals different things to the
> receiving bank. See [Gotchas: amendedDebtorAccount vs originalMandateId](../gotchas.md#amendeddebtoraccount-vs-originalmandateid-mean-different-things).

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
