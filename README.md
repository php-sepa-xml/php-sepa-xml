php-sepa-xml
============

Master: [![Build Status](https://api.travis-ci.org/php-sepa-xml/php-sepa-xml.png?branch=master)](http://travis-ci.org/php-sepa-xml/php-sepa-xml)

SEPA file generator for PHP.

Creates an XML file for a Single Euro Payments Area (SEPA) Credit Transfer and Direct Debit.

License: GNU Lesser General Public License v3.0


## Supported PAIN Formats
### Credit
* _pain.001.003.03_
* _pain.001.002.03_ 
* _pain.001.001.03_
### Debit
* _pain.008.002.03_
* _pain.008.002.02_ 
* _pain.008.001.02_

Institutions and associations that should accept this format:
* Deutsche Kreditwirtschaft
* Fédération bancaire française

However, always verify generated files with your bank before using!


##Installation
###Composer
This library is available in packagist.org, you can add it to your project
via Composer.

In the "require" section of your composer.json file:

Always up to date (bleeding edge, API *not* guaranteed stable)
```javascript
"digitick/sepa-xml" : "dev-master"
```

Specific version, API stability
```javascript
"digitick/sepa-xml" : "1.1.*"
```

## Documentation

* [handling Direct Debit](doc/direct_debit.md)
* [handling Direct Credit](doc/direct_credit.md)

## Development
* [complete dev setup](doc/dev_setup.md)





