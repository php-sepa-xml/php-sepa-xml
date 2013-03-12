php-sepa-xml
============

SEPA file generator for PHP.

License: GNU Lesser General Public License v3.0

ALPHA QUALITY SOFTWARE

Do **not** use in production environments!!!

**API subject to change.**

###Basic Usage
```php
$sepaFile = new SepaTransferFile();
$sepaFile->messageIdentification = 'transferID';
$sepaFile->initiatingPartyName = 'Me';

/* 
 * Set the payment information
 */
$sepaFile->setPaymentInfo(array(
	'id'					=> 'Payment Info ID',
	'debtorName'			=> 'My Corp',
	'debtorAccountIBAN'		=> 'MY_ACCOUNT_IBAN',
	'debtorAgentBIC'		=> 'MY_BANK_BIC'
//	'debtorAccountCurrency'	=> 'GPB', // optional, defaults to 'EUR'
//	'categoryPurposeCode'	=> 'SUPP', // optional, defaults to NULL
));

/* 
 * Add the credit transfer(s). This method may be called
 * more than once to add multiple transfers for the same
 * payment information.
 */
$sepaFile->addCreditTransfer(array(
	'id'					=> 'Id shown in bank statement',
	'currency'				=> 'EUR',
	'amount'				=> '0.02', // or as float: 0.02 or as integer: 2
	'creditorBIC'			=> 'THEIR_BANK_BIC',
	'creditorName'			=> 'THEIR_NAME',
	'creditorAccountIBAN'	=> 'THEIR_IBAN',
	'remittanceInformation'	=> 'Transaction description',
));

// generate the file and return the XML string
echo $sepaFile->asXML();

// After generating the file, these two values can be retrieved:
echo $sepaFile->getHeaderControlSumCents();
echo $sepaFile->getPaymentControlSumCents();
```
