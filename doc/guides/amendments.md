---
title: "Amendments"
description: "Mark a direct debit mandate as amended and supply the original mandate references."
---

# Amendments

Add an amendment to a transfer by passing the amendment fields when calling
`addTransfer` on the named `PaymentInformation` object.

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
