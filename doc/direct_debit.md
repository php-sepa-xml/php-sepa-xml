##Sample Usage DirectDebit with Factory
```php
use PhpSepa\TransferFile\Factory\TransferFileFacadeFactory;
use PhpSepa\PaymentInformation;

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
use PhpSepa\TransferFile\Factory\TransferFileFacadeFactory;
use PhpSepa\PaymentInformation;
use PhpSepa\GroupHeader;

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