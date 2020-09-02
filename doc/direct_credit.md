

## Sample Usage CreditTransfer with Factory
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
    'amount'                  => 500,
    'creditorIban'            => 'FI1350001540000056',
    'creditorBic'             => 'OKOYFIHH',
    'creditorName'            => 'Their Company',
    'remittanceInformation'   => 'Purpose of this credit transfer'
));
// Retrieve the resulting XML
$customerCredit->asXML();
```

## Extended Usage CreditTransfer
```php
// Create the initiating information
$groupHeader = new GroupHeader('SEPA File Identifier', 'Your Company Name');
$sepaFile = new CustomerCreditTransferFile($groupHeader);

$transfer = new CustomerCreditTransferInformation(
    2, // Amount
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
