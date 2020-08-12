# Changelog

## [Unreleased](https://github.com/php-sepa-xml/php-sepa-xml/tree/HEAD)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/2.0-rc2...HEAD)

**Closed issues:**

- PaymentInformation::getLocalInstrumentCode\(\) must be of the type int or null, string returned [\#105](https://github.com/php-sepa-xml/php-sepa-xml/issues/105)

## [2.0-rc2](https://github.com/php-sepa-xml/php-sepa-xml/tree/2.0-rc2) (2020-08-12)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/2.0-rc1...2.0-rc2)

**Closed issues:**

- Question about this project [\#103](https://github.com/php-sepa-xml/php-sepa-xml/issues/103)

**Merged pull requests:**

- fix local instrument code return type [\#106](https://github.com/php-sepa-xml/php-sepa-xml/pull/106) ([monofone](https://github.com/monofone))
- Added support for creditorReferenceType to CustomerCreditFacade [\#104](https://github.com/php-sepa-xml/php-sepa-xml/pull/104) ([ttaelman](https://github.com/ttaelman))
- Rebase of pull request \#66 [\#102](https://github.com/php-sepa-xml/php-sepa-xml/pull/102) ([mogilvie](https://github.com/mogilvie))

## [2.0-rc1](https://github.com/php-sepa-xml/php-sepa-xml/tree/2.0-rc1) (2020-06-19)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.6.2...2.0-rc1)

**Closed issues:**

- Payment Info CIF/NIF \(id\) Required [\#98](https://github.com/php-sepa-xml/php-sepa-xml/issues/98)
- \[Question\] Schema pain.008.002.02 is inofficial? [\#91](https://github.com/php-sepa-xml/php-sepa-xml/issues/91)
- Debitor name and end-to-end id not sanitized [\#90](https://github.com/php-sepa-xml/php-sepa-xml/issues/90)
- Impossible to add PstlAdr in format pain.008.001.02 [\#88](https://github.com/php-sepa-xml/php-sepa-xml/issues/88)

**Merged pull requests:**

- Php 7.2 / Phpunit 9 / PSR-4 / Refacto tests [\#101](https://github.com/php-sepa-xml/php-sepa-xml/pull/101) ([VincentLanglet](https://github.com/VincentLanglet))
- Add throws tag [\#100](https://github.com/php-sepa-xml/php-sepa-xml/pull/100) ([VincentLanglet](https://github.com/VincentLanglet))
- Increase php versions to run tests for [\#97](https://github.com/php-sepa-xml/php-sepa-xml/pull/97) ([monofone](https://github.com/monofone))

## [1.6.2](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.6.2) (2020-02-13)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.6.1...1.6.2)

**Merged pull requests:**

- Add additional pain format for PstlAdr [\#89](https://github.com/php-sepa-xml/php-sepa-xml/pull/89) ([monofone](https://github.com/monofone))
- remove php 5.4 and 5.5 [\#87](https://github.com/php-sepa-xml/php-sepa-xml/pull/87) ([dakira](https://github.com/dakira))

## [1.6.1](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.6.1) (2019-11-04)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.6.0...1.6.1)

**Closed issues:**

- Batch Booking [\#81](https://github.com/php-sepa-xml/php-sepa-xml/issues/81)
- \[PAIN.008.001.02\] Dutch validation issues [\#72](https://github.com/php-sepa-xml/php-sepa-xml/issues/72)

**Merged pull requests:**

- Init $paymentInformations value [\#86](https://github.com/php-sepa-xml/php-sepa-xml/pull/86) ([VincentLanglet](https://github.com/VincentLanglet))

## [1.6.0](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.6.0) (2019-06-06)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.5.0...1.6.0)

**Closed issues:**

- Version bump? [\#80](https://github.com/php-sepa-xml/php-sepa-xml/issues/80)
- PHP 5.1 [\#79](https://github.com/php-sepa-xml/php-sepa-xml/issues/79)

**Merged pull requests:**

- Update CustomerDirectDebitFacade.php [\#82](https://github.com/php-sepa-xml/php-sepa-xml/pull/82) ([ouraios](https://github.com/ouraios))

## [1.5.0](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.5.0) (2019-02-11)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.4.0...1.5.0)

**Merged pull requests:**

- Update StringHelper.php [\#78](https://github.com/php-sepa-xml/php-sepa-xml/pull/78) ([mrgarry](https://github.com/mrgarry))
- Small additions for Finnish banks compatibility [\#77](https://github.com/php-sepa-xml/php-sepa-xml/pull/77) ([joosev](https://github.com/joosev))
- Add unstructured creditor address to credit transfer xml. [\#76](https://github.com/php-sepa-xml/php-sepa-xml/pull/76) ([ghrichrist](https://github.com/ghrichrist))

## [1.4.0](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.4.0) (2018-11-16)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.3.0...1.4.0)

**Closed issues:**

- Amount is always divided by 100 [\#73](https://github.com/php-sepa-xml/php-sepa-xml/issues/73)
- CreDtTm bad string length [\#70](https://github.com/php-sepa-xml/php-sepa-xml/issues/70)
- BIC optional [\#69](https://github.com/php-sepa-xml/php-sepa-xml/issues/69)
- Output is not ISO 20022 XSD comliant [\#65](https://github.com/php-sepa-xml/php-sepa-xml/issues/65)
- Add pain.008.003.02 support [\#59](https://github.com/php-sepa-xml/php-sepa-xml/issues/59)
- Make sure \<ReqdColltnDt\> is a TARGET2 day [\#58](https://github.com/php-sepa-xml/php-sepa-xml/issues/58)

**Merged pull requests:**

- Add option to set due date for credit transfer [\#74](https://github.com/php-sepa-xml/php-sepa-xml/pull/74) ([aquariuz](https://github.com/aquariuz))
- Add instruction priority to payment information [\#71](https://github.com/php-sepa-xml/php-sepa-xml/pull/71) ([mludvik](https://github.com/mludvik))

## [1.3.0](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.3.0) (2018-01-30)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.2.1...1.3.0)

**Closed issues:**

- Not working with PHP 5.6, debian 8 [\#56](https://github.com/php-sepa-xml/php-sepa-xml/issues/56)

**Merged pull requests:**

- ability to set instructionId in "CreditTransfer with Factory" [\#62](https://github.com/php-sepa-xml/php-sepa-xml/pull/62) ([mwenghi](https://github.com/mwenghi))
- Added support for pain.008.003.02 [\#61](https://github.com/php-sepa-xml/php-sepa-xml/pull/61) ([monofone](https://github.com/monofone))
- Readme improvements [\#57](https://github.com/php-sepa-xml/php-sepa-xml/pull/57) ([ArneTR](https://github.com/ArneTR))

## [1.2.1](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.2.1) (2017-08-31)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.2.0...1.2.1)

**Closed issues:**

- Format of PaymentInformation-\>getDueDate\(\) [\#54](https://github.com/php-sepa-xml/php-sepa-xml/issues/54)
- Is the BIC required? [\#32](https://github.com/php-sepa-xml/php-sepa-xml/issues/32)

## [1.2.0](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.2.0) (2017-08-31)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.1.4...1.2.0)

**Closed issues:**

- Filter out characters like \_ [\#43](https://github.com/php-sepa-xml/php-sepa-xml/issues/43)
- Support for pain.001.003.03? [\#27](https://github.com/php-sepa-xml/php-sepa-xml/issues/27)

**Merged pull requests:**

- Allow common slashes in sanitized strings [\#60](https://github.com/php-sepa-xml/php-sepa-xml/pull/60) ([keltuo](https://github.com/keltuo))
- Setters for organization id and identification scheme [\#53](https://github.com/php-sepa-xml/php-sepa-xml/pull/53) ([Jontsa](https://github.com/Jontsa))
- Better headers formatting [\#51](https://github.com/php-sepa-xml/php-sepa-xml/pull/51) ([caseycs](https://github.com/caseycs))
- Update README.md [\#50](https://github.com/php-sepa-xml/php-sepa-xml/pull/50) ([BorislavSabev](https://github.com/BorislavSabev))
- Update Util\StringHelper - add some more chars [\#48](https://github.com/php-sepa-xml/php-sepa-xml/pull/48) ([BorislavSabev](https://github.com/BorislavSabev))
- Update string helper [\#46](https://github.com/php-sepa-xml/php-sepa-xml/pull/46) ([monofone](https://github.com/monofone))
- Directdebit bic optional [\#45](https://github.com/php-sepa-xml/php-sepa-xml/pull/45) ([monofone](https://github.com/monofone))
- Fix \#43 [\#44](https://github.com/php-sepa-xml/php-sepa-xml/pull/44) ([amenk](https://github.com/amenk))
- Don't add RemittanceElement when there is no RemittanceInformation [\#41](https://github.com/php-sepa-xml/php-sepa-xml/pull/41) ([gertvr](https://github.com/gertvr))
- Update amendments with compliance to SDD Rulebook v9 [\#39](https://github.com/php-sepa-xml/php-sepa-xml/pull/39) ([jarnovanleeuwen](https://github.com/jarnovanleeuwen))

## [1.1.4](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.1.4) (2017-01-01)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.1.3...1.1.4)

**Closed issues:**

- BtchBookg element [\#36](https://github.com/php-sepa-xml/php-sepa-xml/issues/36)

**Merged pull requests:**

- Update PaymentInformation.php [\#38](https://github.com/php-sepa-xml/php-sepa-xml/pull/38) ([andreasschroth](https://github.com/andreasschroth))

## [1.1.3](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.1.3) (2016-11-18)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.1.2...1.1.3)

**Closed issues:**

- How to set \<ReqdColltnDt\> [\#35](https://github.com/php-sepa-xml/php-sepa-xml/issues/35)

**Merged pull requests:**

- Check for value to be not null [\#37](https://github.com/php-sepa-xml/php-sepa-xml/pull/37) ([monofone](https://github.com/monofone))

## [1.1.2](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.1.2) (2016-11-02)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.1.1...1.1.2)

**Closed issues:**

- No luck getting diaeresis/trema in the XML [\#30](https://github.com/php-sepa-xml/php-sepa-xml/issues/30)
- CustomerCreditFacade missing from current stable [\#25](https://github.com/php-sepa-xml/php-sepa-xml/issues/25)
- Actual status? [\#13](https://github.com/php-sepa-xml/php-sepa-xml/issues/13)
- Referenced XSD version should be updated to 008.003.02 for direct debit [\#12](https://github.com/php-sepa-xml/php-sepa-xml/issues/12)

**Merged pull requests:**

- Fixed amounts with decimals [\#31](https://github.com/php-sepa-xml/php-sepa-xml/pull/31) ([vaites](https://github.com/vaites))
- Extended string sanitizer with many characters [\#29](https://github.com/php-sepa-xml/php-sepa-xml/pull/29) ([naitsirch](https://github.com/naitsirch))

## [1.1.1](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.1.1) (2015-12-14)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.1.0...1.1.1)

**Implemented enhancements:**

- Improved Jarnovanleeuwen amendments [\#20](https://github.com/php-sepa-xml/php-sepa-xml/pull/20) ([monofone](https://github.com/monofone))

**Fixed bugs:**

- Add: Missing CustomerCreditFacade [\#16](https://github.com/php-sepa-xml/php-sepa-xml/pull/16) ([BenjaminPaap](https://github.com/BenjaminPaap))

**Closed issues:**

- Refactor Documentation [\#21](https://github.com/php-sepa-xml/php-sepa-xml/issues/21)
- Missing class CustomerCreditFacade [\#14](https://github.com/php-sepa-xml/php-sepa-xml/issues/14)
- Excess data in pain.008.001.02 [\#10](https://github.com/php-sepa-xml/php-sepa-xml/issues/10)

**Merged pull requests:**

- Install compatible PHPUnit version and use it on Travis CI [\#24](https://github.com/php-sepa-xml/php-sepa-xml/pull/24) ([xabbuh](https://github.com/xabbuh))
- Tests for all schemas [\#23](https://github.com/php-sepa-xml/php-sepa-xml/pull/23) ([BenjaminPaap](https://github.com/BenjaminPaap))
- Split the docs into two parts to get a better overview [\#22](https://github.com/php-sepa-xml/php-sepa-xml/pull/22) ([monofone](https://github.com/monofone))
- Updated README with CreditTransfer facade example [\#19](https://github.com/php-sepa-xml/php-sepa-xml/pull/19) ([monofone](https://github.com/monofone))
- Remove PHP7 from allow\_failures [\#17](https://github.com/php-sepa-xml/php-sepa-xml/pull/17) ([soullivaneuh](https://github.com/soullivaneuh))

## [1.1.0](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.1.0) (2015-11-25)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/1.0.0...1.1.0)

**Closed issues:**

- Documentation [\#9](https://github.com/php-sepa-xml/php-sepa-xml/issues/9)
- New release tag [\#8](https://github.com/php-sepa-xml/php-sepa-xml/issues/8)
- Travis build state "unknown" [\#7](https://github.com/php-sepa-xml/php-sepa-xml/issues/7)

**Merged pull requests:**

- Added DirectDebit and Credit from custom GroupHeader [\#11](https://github.com/php-sepa-xml/php-sepa-xml/pull/11) ([eusonlito](https://github.com/eusonlito))

## [1.0.0](https://github.com/php-sepa-xml/php-sepa-xml/tree/1.0.0) (2014-12-15)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/0.10.1...1.0.0)

**Merged pull requests:**

- Fixed: Custom EndToEndId in DirectDebits [\#6](https://github.com/php-sepa-xml/php-sepa-xml/pull/6) ([BenjaminPaap](https://github.com/BenjaminPaap))
- \[TASK\] Fixed travis build state [\#5](https://github.com/php-sepa-xml/php-sepa-xml/pull/5) ([monofone](https://github.com/monofone))
- \[TASK\] Changed the order for creating the timestamp [\#3](https://github.com/php-sepa-xml/php-sepa-xml/pull/3) ([monofone](https://github.com/monofone))
- Allow setting an optional EndToEndId [\#1](https://github.com/php-sepa-xml/php-sepa-xml/pull/1) ([BenjaminPaap](https://github.com/BenjaminPaap))

## [0.10.1](https://github.com/php-sepa-xml/php-sepa-xml/tree/0.10.1) (2013-12-19)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/0.10.0...0.10.1)

## [0.10.0](https://github.com/php-sepa-xml/php-sepa-xml/tree/0.10.0) (2013-10-24)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/0.9.3...0.10.0)

## [0.9.3](https://github.com/php-sepa-xml/php-sepa-xml/tree/0.9.3) (2013-09-05)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/0.9.2...0.9.3)

## [0.9.2](https://github.com/php-sepa-xml/php-sepa-xml/tree/0.9.2) (2013-09-05)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/0.9.1...0.9.2)

## [0.9.1](https://github.com/php-sepa-xml/php-sepa-xml/tree/0.9.1) (2013-07-12)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/0.9...0.9.1)

## [0.9](https://github.com/php-sepa-xml/php-sepa-xml/tree/0.9) (2013-05-13)

[Full Changelog](https://github.com/php-sepa-xml/php-sepa-xml/compare/d5b0151981907723cdf3dc53294d641a8060104f...0.9)



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
