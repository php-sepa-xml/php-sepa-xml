Credit Transfer Payment Initiation
===============================

* [Direct usage of CreditTransfer File](#direct-usage-of-credittransfer-file)
* [Sample usage of CreditTransfer File with Facade Factory](#sample-usage-of-credittransfer-file-with-facade-factory)


Direct usage of CreditTransfer File
-------------------------------------
The following example creates a CreditTransfer file, adds a PaymentInformation Object and a single transaction to it.  
The variable names are used to describe what should be contained within them.

```php
    use Digitick\Sepa\DomBuilder\DomBuilderFactory;
    use Digitick\Sepa\GroupHeader;
    use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
    use Digitick\Sepa\PaymentInformation;
    use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;

    $groupHeader = new GroupHeader($messageIdentification, $companyName);

    $sepaFile = new CustomerCreditTransferFile($groupHeader);
    $paymentInfo = new PaymentInformation(
        $paymentInfoId,
        $originAccIban,
        $originAccBic,
        $companyName
    );
    $paymentInfo->setCreditorId($creditorId);
    $paymentInfo->setSequenceType(PaymentInformation::S_ONEOFF);
    $paymentInfo->setOriginName($companyName);

    $transfer = new CustomerCreditTransferInformation(1234, $clientAccIban, $clientName, $endToEndId);
    $transfer->setBic($clientAccBic);
    $transfer->setCreditorReference('CdtrRefInf-Ref');
    $transfer->setCreditorReferenceType('CdtrRefInf-Tp-Issr');
    $transfer->setPurposeCode('SALA');

    $transfer->setCountry('BG');
    $transfer->setPostCode('1000');
    $transfer->setTownName('Nowhere');
    $transfer->setStreetName('Some Street');
    $transfer->setBuildingNumber(12);
    $transfer->setFloorNumber(13);

    //You can add as many transfers as you want to a single PaymentInformation object
    $paymentInfo->addTransfer($transfer);
    //You can add as many PaymentInformation objects as you want to a transfer file
    $sepaFile->addPaymentInformation($paymentInfo);

    // Write to a file:
    $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile, $painFormat); //For e.g. 'pain.001.001.08'
    file_put_contents($filePath, $domBuilder->asXml());.
    // ...or retrieve the \DomDocument object, modify it and do something else with it:
    $domBuilder->asDoc();
```


Sample usage of CreditTransfer File with Facade Factory
-------------------------------------

```php
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

// Returns a CustomerCreditFacade
$customerCredit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me');

// Add a PaymentInfo object (PmtInf), it's possible to create multiple such objects in one ISO20022 file
// "firstPayment" is the identifier for the transactions aka the PaymentInfoId (PmtInfId)
// Note that here the initiating party (your company) is the Debtor as opposed to being the Creditor when we initiate a Direct Debit
$customerCredit->addPaymentInfo('firstPayment', array(
    'id'                      => 'firstPayment',
    'debtorName'              => 'My Company',
    'debtorAccountIBAN'       => 'FI1350001540000056',
    'debtorAgentBIC'          => 'PSSTFRPPMON',
    // Add/Set batch booking option as per your requirement, optional
    'batchBooking'            => true, 
));

// Add a Single Transaction to the named PaymentInfo
$customerCredit->addTransfer('firstPayment', array(
    'amount'                  => 500, // `amount` in cents
    'creditorIban'            => 'FI1350001540000056',
    'creditorBic'             => 'OKOYFIHH',
    'creditorName'            => 'Their Company',
    'remittanceInformation'   => 'Purpose of this credit transfer'
));
// Retrieve the resulting XML
$customerCredit->asXML();
```
