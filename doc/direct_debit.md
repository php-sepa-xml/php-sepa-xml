## Sample Usage DirectDebit with Factory
```php
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;

//Set the initial information
// third parameter 'pain.008.003.02' is optional would default to 'pain.008.002.02' if not changed
$directDebit = TransferFileFacadeFactory::createDirectDebit('SampleUniqueMsgId', 'SampleInitiatingPartyName', 'pain.008.003.02');

// create a payment, it's possible to create multiple payments,
// "firstPayment" is the identifier for the transactions
// This creates a one time debit. If needed change use ::S_FIRST, ::S_RECURRING or ::S_FINAL respectively
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
// Add a Single Transaction to the named payment
$directDebit->addTransfer('firstPayment', array(
    'amount'                => 500,
    'debtorIban'            => 'FI1350001540000056',
    'debtorBic'             => 'OKOYFIHH',
    'debtorName'            => 'Their Company',
    'debtorMandate'         => 'AB12345',
    'debtorMandateSignDate' => '13.10.2012',
    'remittanceInformation' => 'Purpose of this direct debit',
    'endToEndId'            => 'Invoice-No X' // optional, if you want to provide additional structured info
));
// Retrieve the resulting XML
$directDebit->asXML();
```

## Sample Usage DirectDebit with Factory and Custom Header
```php
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\GroupHeader;

//Set the custom header (Spanish banks example) information
$header = new GroupHeader(date('Y-m-d-H-i-s'), 'Me');
$header->setInitiatingPartyId('DE21WVM1234567890');

$directDebit = TransferFileFacadeFactory::createDirectDebitWithGroupHeader($header, 'pain.008.001.02');

// create a payment, it's possible to create multiple payments,
// "firstPayment" is the identifier for the transactions
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
// Add a Single Transaction to the named payment
$directDebit->addTransfer('firstPayment', array(
    'amount'                => 500,
    'debtorIban'            => 'FI1350001540000056',
    'debtorBic'             => 'OKOYFIHH',
    'debtorName'            => 'Their Company',
    'debtorMandate'         =>  'AB12345',
    'debtorMandateSignDate' => '13.10.2012',
    'remittanceInformation' => 'Purpose of this direct debit',
    'endToEndId'            => 'Invoice-No X' // optional, if you want to provide additional structured info
));
// Retrieve the resulting XML
$directDebit->asXML();
```

## Add an amendment to a transfer

```php
// Add a Single Transaction to the named payment
$directDebit->addTransfer('firstPayment', array(
    'amount'                  => 500,
    'creditorIban'            => 'FI1350001540000056',
    'creditorBic'             => 'OKOYFIHH',
    'creditorName'            => 'Their Company',
    'remittanceInformation'   => 'Purpose of this credit transfer',
    'endToEndId'              => 'Invoice-No X' // optional, if you want to provide additional structured info
    // Amendments start here
    'originalMandateId'       => '1234567890',
    'originalDebtorIban'      => 'AT711100015440033700',
    'amendedDebtorAccount'    => true
));
```
