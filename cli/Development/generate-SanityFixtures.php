<?php

/**
 * Generate the fixture XML files using the library directly (not through a Facade)
 * Test each generated file against it's appropriate XSD
 */

use Digitick\Sepa\DomBuilder\DomBuilderFactory;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;

require_once __DIR__ . '/../bootstrap-cli.php';
libxml_use_internal_errors(true);

$faker = Faker\Factory::create();

$companyName = $faker->company();
$originAccIban = $faker->iban();
$originAccBic = $faker->swiftBicNumber();
$messageIdentification = 'OurMessageIdentification';
$paymentInfoId = 'OurPaymentInfo';
$creditorId = 'BG40ZZZ507778';

$clientName = $faker->name();
$clientAccIban = $faker->iban();
$clientAccBic = $faker->swiftBicNumber();
$clientLabel = $faker->company() . ' Billing';
$endToEndId = 'SomethingUnique';
$mandateId = 'SomeOtherUniqueThing';

//Generate Direct Debit File
foreach (ddProvider() as $version) {
    $painFormat = $version[0];

    $groupHeader = new GroupHeader($messageIdentification, $companyName);
    $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

    $paymentInfo = new PaymentInformation(
        $paymentInfoId,
        $originAccIban,
        $originAccBic,
        $companyName
    );
    $paymentInfo->setCreditorId($creditorId);
    $paymentInfo->setSequenceType(PaymentInformation::S_ONEOFF);
    $paymentInfo->setOriginName($companyName);

    $transfer = new CustomerDirectDebitTransferInformation(1234, $clientAccIban, $clientName, $endToEndId);
    $transfer->setBic($clientAccBic);
    $transfer->setRemittanceInformation($clientLabel);
    $transfer->setMandateId($mandateId);
    $transfer->setMandateSignDate(new DateTime());

    $transfer->setFinalCollectionDate(new DateTime());

    $transfer->setCountry('BG');
    $transfer->setPostCode('1000');
    $transfer->setTownName('Nowhere');
    $transfer->setStreetName('Some Street');
    $transfer->setBuildingNumber(12);
    $transfer->setFloorNumber(12);

    $paymentInfo->addTransfer($transfer);
    $sepaFile->addPaymentInformation($paymentInfo);

    $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile, $painFormat);
    $filePath = XML_DIR.$painFormat.'.xml';
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    file_put_contents($filePath, $domBuilder->asXml());
}

//Generate Credit Transfer Files
foreach (ctProvider() as $version) {
    $painFormat = $version[0];
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
    $transfer->setFloorNumber(12);

    $paymentInfo->addTransfer($transfer);
    $sepaFile->addPaymentInformation($paymentInfo);

    $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile, $painFormat);
    $filePath = XML_DIR.$painFormat.'.xml';
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    file_put_contents($filePath, $domBuilder->asXml());
}

//Test all generated files
foreach (schemaVersionProvider() as $version) {
    $painFormat = $version[0];

    $domD = new \DOMDocument('1.0', 'UTF-8');
    $schema = XSD_DIR . $painFormat . '.xsd';
    $xmlFile = XML_DIR . $painFormat . '.xml';

    $domD->load($xmlFile);
    $validated = $domD->schemaValidate($schema);
    if (!$validated) {
        print_r(sprintf("Problem with %s:\n", $painFormat));
        var_dump(libxml_get_errors());
        die();
    }
    print_r(sprintf("Test passed for %s\n", $painFormat));
}

function schemaVersionProvider(): array
{
    return array_merge(ctProvider(), ddProvider());
}

function ddProvider(): array
{
    return [
        'pain.008.001.02' => ['pain.008.001.02'],
        'pain.008.001.03' => ['pain.008.001.03'],
        'pain.008.001.04' => ['pain.008.001.04'],
        'pain.008.001.05' => ['pain.008.001.05'],
        'pain.008.001.06' => ['pain.008.001.06'],
        'pain.008.001.07' => ['pain.008.001.07'],
        'pain.008.001.08' => ['pain.008.001.08'],
        'pain.008.001.09' => ['pain.008.001.09'],
        'pain.008.001.10' => ['pain.008.001.10'],
        'pain.008.001.11' => ['pain.008.001.11'],
        //SEPA Variants:
        'pain.008.002.02' => ['pain.008.002.02'],
        'pain.008.003.02' => ['pain.008.003.02'],
    ];
}
function ctProvider(): array
{
    return [
        'pain.001.001.03' => ['pain.001.001.03'],
        'pain.001.001.04' => ['pain.001.001.04'],
        'pain.001.001.05' => ['pain.001.001.05'],
        'pain.001.001.06' => ['pain.001.001.06'],
        'pain.001.001.07' => ['pain.001.001.07'],
        'pain.001.001.08' => ['pain.001.001.08'],
        'pain.001.001.09' => ['pain.001.001.09'],
        'pain.001.001.10' => ['pain.001.001.10'],
        'pain.001.001.12' => ['pain.001.001.12'],
        //SEPA Variants:
        'pain.001.002.03' => ['pain.001.002.03'],
        'pain.001.003.03' => ['pain.001.003.03'],
    ];
}

