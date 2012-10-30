php-sepa-xml
============

SEPA file generator for PHP.

License: GNU Lesser General Public License v3.0

**ALPHA QUALITY SOFTWARE**

Do **not** use in production environments!!!

API subject to change.

###Usage
~~~
[php]
$sepaFile = new SepaTransferFile();
$sepaFile->messageIdentification = 'transferID';
$sepaFile->paymentInfoId = 'paymentInfo';
$sepaFile->initiatingPartyName = 'Me';
$sepaFile->debtorName = 'My Corp';
$sepaFile->debtorAgentBIC = 'MY_BANK_BIC';
$sepaFile->debtorAccountIBAN = 'MY_ACCOUNT_IBAN';

$sepaFile->addCreditTransfer(array(
	'CreditorPaymentId'			=> 'Id shown in bank statement',
	'CreditorPaymentCurrency'	=> 'EUR',
	'CreditorPaymentAmount'		=> '0.02',
	'CreditorBIC'				=> $THEIR_BANK_BIC,
	'CreditorName'				=> $THEIR_NAME,
	'CreditorAccountIBAN'		=> $THEIR_IBAN,
	'RemittanceInformation'		=> 'Transaction description',
));

$sepaFile->headerControlSum = '0.02';
$sepaFile->paymentControlSum = '0.02';
      
echo $sepaFile->generateXml();
/*Output XML*/
echo $sepaFile->outputXML();
~~~