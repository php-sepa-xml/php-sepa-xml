---
title: "Getting Started"
description: "Install the library and generate your first credit transfer and direct debit XML in under five minutes."
---

# Getting Started

This page gets you from `composer require` to a valid SEPA XML file in under five minutes. Assumes PHP 7.2+ and familiarity with the SEPA / ISO 20022 message shape — if you need the message-name primer, see [ISO 20022 naming](reference/iso20022-naming.md).

## Install

```bash
composer require digitick/sepa-xml
```

The package name remains `digitick/sepa-xml` (legacy Packagist alias) — the actual code is maintained by the [php-sepa-xml community](https://github.com/php-sepa-xml).

## Credit Transfer — minimal facade example

```php
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

$customerCredit = TransferFileFacadeFactory::createCustomerCredit(
    'msg-' . date('YmdHis'),   // <MsgId>, max 35 chars, unique per day
    'My Company Ltd',          // <InitgPty><Nm>
    'pain.001.001.09'          // optional; .09 is the default
);

$customerCredit->addPaymentInfo('batch-1', [
    'id'                => 'batch-1',
    'debtorName'        => 'My Company Ltd',
    'debtorAccountIBAN' => 'FI1350001540000056',
    'debtorAgentBIC'    => 'PSSTFRPPMON',
]);

$customerCredit->addTransfer('batch-1', [
    'amount'                => 12500,                  // cents — 125.00 EUR
    'creditorIban'          => 'DE89370400440532013000',
    'creditorBic'           => 'COBADEFFXXX',
    'creditorName'          => 'Supplier GmbH',
    'remittanceInformation' => 'Invoice 2026-04-17',
]);

file_put_contents('credit-transfer.xml', $customerCredit->asXML());
```

## Direct Debit — minimal facade example

```php
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

$directDebit = TransferFileFacadeFactory::createDirectDebit(
    'msg-' . date('YmdHis'),
    'My Company Ltd',
    'pain.008.001.09'
);

$directDebit->addPaymentInfo('batch-1', [
    'id'                  => 'batch-1',
    'creditorName'        => 'My Company Ltd',
    'creditorAccountIBAN' => 'FI1350001540000056',
    'creditorAgentBIC'    => 'PSSTFRPPMON',
    'seqType'             => PaymentInformation::S_ONEOFF,
    'creditorId'          => 'DE21WVM1234567890',     // your SEPA Creditor Identifier
]);

$directDebit->addTransfer('batch-1', [
    'amount'                => 5000,                   // cents — 50.00 EUR
    'debtorIban'            => 'DE89370400440532013000',
    'debtorName'            => 'Customer GmbH',
    'debtorMandate'         => 'MNDT-00042',
    'debtorMandateSignDate' => '01.10.2024',           // d.m.Y string OR DateTimeInterface
    'remittanceInformation' => 'Order 12345',
]);

file_put_contents('direct-debit.xml', $directDebit->asXML());
```

## Three things to know before you ship

1. **Amounts are integer cents** — `12500` is 125.00 EUR. Passing floats silently truncates. See [Gotchas](gotchas.md).
2. **Validate against your bank** — every bank has slightly different acceptance criteria. Generate, send a test file, confirm before any production run.
3. **Facades are single-shot** — `asXML()` finalises the facade. Build a new one if you need to amend the output.

## Next steps

- [Choosing facade vs. direct construction](guides/choosing-facade-vs-direct.md) — when to drop down to the four-class API
- [Credit Transfer guide](guides/credit-transfer.md) / [Direct Debit guide](guides/direct-debit.md) — both flows in detail
- [Output and validation](guides/output-and-validation.md) — `asXML()` vs `asDOC()`, validate behaviour
- [Bank profiles](guides/bank-profiles.md) — known per-bank quirks (DK, BIC-less files, …)
- [Gotchas](gotchas.md) — sharp edges in one place
- [Reference: pain version matrix](reference/pain-version-matrix.md) — supported message versions
