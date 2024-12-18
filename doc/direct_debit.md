Direct Debit Payment Initiation
===============================

* [Sample Usage DirectDebit with Factory](#sample-usage-directdebit-with-factory)
* [Sample Usage DirectDebit with Factory and Custom Header](#sample-usage-directdebit-with-factory-and-custom-header)
* [Add an amendment to a transfer](#add-an-amendment-to-a-transfer)
* [Add address information to transaction](#add-address-information-to-transaction)


Sample Usage DirectDebit with Factory
-------------------------------------

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
    'localInstrumentCode'   => 'CORE', // default. optional.
    // Add/Set batch booking option, you can pass boolean value as per your requirement, optional
    'batchBooking'          => true, 
));

// Add a Single Transaction to the named payment
$directDebit->addTransfer('firstPayment', array(
    'amount'                => 500, // `amount` should be in cents
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

Sample Usage DirectDebit with Factory and Custom Header
-------------------------------------------------------

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

Add an amendment to a transfer
------------------------------

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

Add address information to transaction
--------------------------------------

If an account should be direct debitted, which belongs to a bank that is not a
member of the European Economic Area (EEA), the address data of the account
holder have to be added to the transaction.

```php
$directDebit->addTransfer('firstPayment', [
    'amount'            => 1499,
    'debtorIban'        => 'CH6089144731137988786',
    'debtorBic'         => 'CRESCHZZXXX',
    'debtorName'        => 'John Doe',
    // ...
    // and the relevant address data
    'debtorCountry'     => 'CH',
    'postCode'          => '8245',
    'townName'          => 'Feuerthalen',
    'streetName'        => 'Example Street',
    'buildingNumber'    => '25',
]);

Currently the affected countries are Switzerland, Andorra, Monaco, San Marino,
Vatican City and the United Kingdom (as of 05/23/2022).
