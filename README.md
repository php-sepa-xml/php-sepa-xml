php-sepa-xml
============

SEPA file generator for PHP.

Creates an XML file for a Single Euro Payments Area (SEPA) Credit Transfer and Direct Debit.

The version of the standard followed is: _pain.001.002.03_ and _pain.008.002.02_ as requested by the "Deutsche Kreditwirtschaft"

License: GNU Lesser General Public License v3.0

Verify generated files with your bank before using!!!

**API subject to change.**

##Sample Usage DirectDebit with Factory
```php
//Set the initial information
$directDebit = TransferFileFacadeFactory::createDirectDebit('test123', 'Me');

// create a payment, it´s allowed to create multiple payments, "firstPayment" is the identifier for the transactions
$directDebit->addPaymentInfo('firstPayment', array(
                                                    'id' => 'firstPayment',
                                                    'creditorName' => 'My Company',
                                                    'creditorAccountIBAN' => 'FI1350001540000056',
                                                    'creditorAgentBIC' => 'PSSTFRPPMON',
                                                    'seqType' => PaymentInformation::S_ONEOFF,
                                                    'creditorId' => 'DE21WVM1234567890'
                                             ));
// Add a Single Transaction to the named payment
$directDebit->addTransfer('firstPayment', array(
                                               'amount' => '500',
                                               'debtorIban' => 'FI1350001540000056',
                                               'debtorBic' => 'OKOYFIHH',
                                               'debtorName' => 'Their Company',
                                               'debtorMandate' =>  'AB12345',
                                               'debtorMandateSignDate' => '13.10.2012',
                                               'remittanceInformation' => 'Purpose of this direct debit'
                                          ));
// Retrieve the resulting XML
$directDebit->asXML();
```

##Extended Usage Credit Transfer
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
// It's allowed to add multiple Transfers in one Payment
$payment->addTransfer($transfer);

// It's allowed to add multiple payments to one SEPA File
$sepaFile->addPaymentInformation($payment);

// The DOMBuilder will be moved to a factory later
$domBuilder = new CustomerCreditTransferDomBuilder();
$sepaFile->accept($domBuilder);

$domBuilder->asXml();
```
