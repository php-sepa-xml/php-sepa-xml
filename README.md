# php-sepa-xml


Fork of digitick/sepa-xml / php-sepa-xml/php-sepa-xml

Packagist : https://packagist.org/packages/digitick/sepa-xml

Github : https://github.com/php-sepa-xml/php-sepa-xml

## Fork reasons

We had to update digitick/sepa-xml from 1.2.1 to get the possibility to send address information in DirectDebit xml.
Unfortunately, the sanitize function **StringHelper::sanitizeString** since 1.3.0 get rid of "/" that we have in some of our clientToken.

**28/10/2021** : The fork just take care of the sanitize function at the moment.