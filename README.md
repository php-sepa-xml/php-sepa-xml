php-sepa-xml
============

SEPA file generator for PHP.

License: GNU Lesser General Public License v3.0

**ALPHA QUALITY SOFTWARE**

Do **not** use in production environments!!!

###Usage
~~~
[php]
$sepaFile = new SepaTransferFile;
$sepaFile->messageIdentification = 'transferID';
$sepaFile->paymentInfoId = 'paymentInfo';
$sepaFile->initiatingPartyName = 'Me';
$sepaFile->debtorName = 'My Corp';
$sepaFile->debtorAgentBIC = 'MY_BANK_BIC';
$sepaFile->debtorAccountIBAN = 'MY_ACCOUNT_IBAN';

$sepaFile->addCreditor(array(
  			'CreditorPaymentEndToEndId' => 'someId',
				'CreditorPaymentCurrency'	=> 'EUR',
				'CreditorPaymentAmount'		=> '0.02',
				'CreditorBIC'				=> $THEIR_BANK_BIC,
				'CreditorName'				=> $THEIR_NAME,
				'CreditorAccountIBAN'		=> $THEIR_IBAN,
				'RemittanceInformation'		=> 'some string',
			));

$sepaFile->headerControlSum = '0.02';
$sepaFile->paymentControlSum = '0.02';
      
echo $sepaFile->generateXml();
~~~