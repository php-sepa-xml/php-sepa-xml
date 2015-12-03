php-sepa-xml
============

Master: [![Build Status](https://api.travis-ci.org/php-sepa-xml/php-sepa-xml.png?branch=master)](http://travis-ci.org/php-sepa-xml/php-sepa-xml)

SEPA file generator for PHP.

Creates an XML file for a Single Euro Payments Area (SEPA) Credit Transfer and Direct Debit.

License: GNU Lesser General Public License v3.0


The versions of the standard followed are:
* _pain.001.002.03_ (or _pain.001.001.03_) for credits
* and _pain.008.002.02_ (or _pain.008.001.02_) for debits

Institutions and associations that should accept this format:
* Deutsche Kreditwirtschaft
* Fédération bancaire française

However, always verify generated files with your bank before using!


##Installation
###Composer
This library is available in packagist.org, you can add it to your project
via Composer.

In the "require" section of your composer.json file:

Always up to date (bleeding edge, API *not* guaranteed stable)
```javascript
"digitick/sepa-xml" : "dev-master"
```

Specific version, API stability
```javascript
"digitick/sepa-xml" : "1.1.*"
```

##Sample Usage DirectDebit with Factory
```php
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;

//Set the initial information
$directDebit = TransferFileFacadeFactory::createDirectDebit('test123', 'Me');

// create a payment, it's possible to create multiple payments,
// "firstPayment" is the identifier for the transactions
$directDebit->addPaymentInfo('firstPayment', array(
    'id'                    => 'firstPayment',
    'creditorName'          => 'My Company',
    'creditorAccountIBAN'   => 'FI1350001540000056',
    'creditorAgentBIC'      => 'PSSTFRPPMON',
    'seqType'               => PaymentInformation::S_ONEOFF,
    'creditorId'            => 'DE21WVM1234567890'
));
// Add a Single Transaction to the named payment
$directDebit->addTransfer('firstPayment', array(
    'amount'                => '500',
    'debtorIban'            => 'FI1350001540000056',
    'debtorBic'             => 'OKOYFIHH',
    'debtorName'            => 'Their Company',
    'debtorMandate'         =>  'AB12345',
    'debtorMandateSignDate' => '13.10.2012',
    'remittanceInformation' => 'Purpose of this direct debit'
));
// Retrieve the resulting XML
$directDebit->asXML();
```

##Sample Usage DirectDebit with Factory and Custom Header
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
    'creditorName'          => 'My Company',
    'creditorAccountIBAN'   => 'FI1350001540000056',
    'creditorAgentBIC'      => 'PSSTFRPPMON',
    'seqType'               => PaymentInformation::S_ONEOFF,
    'creditorId'            => 'DE21WVM1234567890'
));
// Add a Single Transaction to the named payment
$directDebit->addTransfer('firstPayment', array(
    'amount'                => '500',
    'debtorIban'            => 'FI1350001540000056',
    'debtorBic'             => 'OKOYFIHH',
    'debtorName'            => 'Their Company',
    'debtorMandate'         =>  'AB12345',
    'debtorMandateSignDate' => '13.10.2012',
    'remittanceInformation' => 'Purpose of this direct debit'
));
// Retrieve the resulting XML
$directDebit->asXML();
```

##Sample Usage CreditTransfer with Factory
```php
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

//Set the initial information
$customerCredit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me');

// create a payment, it's possible to create multiple payments,
// "firstPayment" is the identifier for the transactions
$customerCredit->addPaymentInfo('firstPayment', array(
    'id'                      => 'firstPayment',
    'debtorName'              => 'My Company',
    'debtorAccountIBAN'       => 'FI1350001540000056',
    'debtorAgentBIC'          => 'PSSTFRPPMON',
));
// Add a Single Transaction to the named payment
$customerCredit->addTransfer('firstPayment', array(
    'amount'                  => '500',
    'creditorIban'            => 'FI1350001540000056',
    'creditorBic'             => 'OKOYFIHH',
    'creditorName'            => 'Their Company',
    'remittanceInformation'   => 'Purpose of this credit transfer'
));
// Retrieve the resulting XML
$customerCredit->asXML();
```

##Extended Usage CreditTransfer
```php
// Create the initiating information
$groupHeader = new GroupHeader('SEPA File Identifier', 'Your Company Name');
$sepaFile = new CustomerCreditTransferFile($groupHeader);

$transfer = new CustomerCreditTransferInformation(
    '0.02', // Amount
    'FI1350001540000056', //IBAN of creditor
    'Their Corp' //Name of Creditor
);
$transfer->setBic('OKOYFIHH'); // Set the BIC explicitly
$transfer->setRemittanceInformation('Transaction Description');

// Create a PaymentInformation the Transfer belongs to
$payment = new PaymentInformation(
    'Payment Info ID',
    'FR1420041010050500013M02606', // IBAN the money is transferred from
    'PSSTFRPPMON',  // BIC
    'My Corp' // Debitor Name
);
// It's possible to add multiple Transfers in one Payment
$payment->addTransfer($transfer);

// It's possible to add multiple payments to one SEPA File
$sepaFile->addPaymentInformation($payment);

// Attach a dombuilder to the sepaFile to create the XML output
$domBuilder = DomBuilderFactory::createDomBuilder($sepaFile);

// Or if you want to use the format 'pain.001.001.03' instead
// $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile, 'pain.001.001.03');

$domBuilder->asXml();
```
