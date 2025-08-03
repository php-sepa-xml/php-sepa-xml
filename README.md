php-sepa-xml
============

[![Build Status](https://github.com/php-sepa-xml/php-sepa-xml/actions/workflows/phpunit.yml/badge.svg)](https://github.com/php-sepa-xml/php-sepa-xml/actions/workflows/phpunit.yml)

SEPA / ISO20022 file generator for PHP.

Creates XML files for the Single Euro Payments Area (SEPA) Credit Transfer and Direct Debit Payments Initiation messages. These SEPA XML messages are a subset of the "ISO20022 Universal financial industry message scheme".

License: GNU Lesser General Public License v3.0

The versions of the standard followed are:
* For Credit Transfers:
  * pain.001.001.03
  * pain.001.001.09 (PR in review)
  * pain.001.001.12 (planned)
  * pain.001.002.03
  * pain.001.003.03
* For Direct Debits:
  * pain.008.001.02
  * pain.008.001.08 (PR in review)
  * pain.008.001.10
  * pain.008.001.11 (planned)
  * pain.008.002.02
  * pain.008.003.02

We do not claim 100% support of those formats but the files produced by this library are validated agains the official XSDs and they pass the validation. I you're missing a field please submit a PR.

Institutions that should accept these formats:
* Any bank that is part of the SEPA network and works with the ISO20022 standard

Institutions that are confirmed to accept these formats:
* Deutsche Kreditwirtschaft
* Fédération bancaire française
* ING (tested on direct credit, use _pain.001.001.03_)
* Commerzbank (tested on Credit Transfer, use _pain.001.001.03_)
* Spain: CaixaBank
* Spain: SantanderBank
* Netherlands: direct-debit (pain.008.001.02) at RABO-bank (thanks @rodekker)

Always verify generated files with your bank before using in production! If you encounter an institution that does accept this library's generated files please notify us to add it to the list or send a PR!


## Installation
### Composer
This library is available in [packagist.org as digitick/sepa-xml](https://packagist.org/packages/digitick/sepa-xml), you can add it to your project via Composer:
```
composer require digitick/sepa-xml
```

Please note that the library still carries it's original name (namely `digitick/sepa-xml`) as we don't want to confuse people. 
The latest versions are developed by the [php-sepa-xml community](https://github.com/php-sepa-xml) and the packagist alias (`digitick/sepa-xml`) points here.
In the near future we may switch to `php-sepa-xml/php-sepa-xml` as that is already the official package name.

## Documentation
Check out our docs at:
* [handling Direct Debits](doc/direct_debit.md)
* [handling Credit Transfers](doc/direct_credit.md)

## Development
Want to contribute? Please check out our [Dev docs](doc/dev_setup.md)

## Any Questions?
Feel free to open an issue. We will try to reply to the best of our knowledge.

## External Resources
* [Official ISO20022 Website](https://www.iso20022.org/)
* [ISO20022 Message Catalog](https://www.iso20022.org/full_catalogue.page)
* [ISO 20022 in XMLdation's wiki](https://wiki.xmldation.com/General_Information/ISO_20022)
* [InstdAmt vs. EqvtAmt](https://wiki.xmldation.com/General_Information/ISO_20022/Difference_between_InstdAmt_and_EqvtAmt)
* [CreditorIdentifier explanation](http://www.sepaforcorporates.com/sepa-direct-debits/sepa-creditor-identifier/)
* [SEPA Externded Character Set reference](http://www.sepahungary.hu/uploads/files/EPC217-08%20Best%20Practices%20-SEPA%20Requirements%20for%20Character%20Set.pdf)
* [ECB SEPA gateway page](http://www.ecb.europa.eu/paym/retpaym/html/index.en.html)
* [SEPA for Corporates Blog](http://www.sepaforcorporates.com/)

