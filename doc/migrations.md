---
title: "Migrations"
description: "Upgrade notes for moving between major versions of the library."
---

# Migrations

Upgrade notes for users moving across major versions. For exhaustive release notes see [`CHANGELOG.md`](../CHANGELOG.md) and the [GitHub releases page](https://github.com/php-sepa-xml/php-sepa-xml/releases).

## PHP version

| Library version | Minimum PHP |
|-----------------|-------------|
| 1.x             | 5.6 (legacy) |
| 2.x             | 7.2         |
| 3.x             | 7.2         |

## 2.x → 3.x

The 3.0.0 release was a major version bump; consult the [3.0.0 GitHub release](https://github.com/php-sepa-xml/php-sepa-xml/releases) for the full list. The salient runtime-visible changes that landed by 3.1.0:

### Facade is now single-shot

`asXML()` / `asDOC()` finalise the facade. Re-calling `addPaymentInfo` or `addTransfer` after a render throws `\LogicException`. Repeated `asXML()` calls return a cached string.

**Migration:** if you previously rendered, then mutated, then re-rendered the same facade, build a new facade for each render. The cached behaviour is also the safer one — pre-3.x flows could double-count `<NbOfTxs>` / `<CtrlSum>` on a second render.

See [Reference: Facade](reference/classes/facade.md).

### DK / German bank compliance flags

`setOmitGroupHeaderControlSum(bool)` and `setOmitAgentElementIfBicMissing(bool)` were added on `BaseCustomerTransferFileFacade` (and the underlying `BaseDomBuilder`).

**Migration:** if you previously hand-edited the rendered XML to strip `<CtrlSum>` from `<GrpHdr>` or replace `NOTPROVIDED` BIC placeholders, switch to the flags. See [Bank profiles](guides/bank-profiles.md).

### Ultimate debitor support

`setUltimateDebtorName` / `ultimateDebtorName` array key for direct debits — emits `<UltmtDbtr>`. New surface; nothing to migrate.

## 1.x → 2.x

Per the 2.0-rc1 release notes:

### Direct debit amount: string → int (cents)

The direct-debit transfer amount was previously a string. From 2.0 onwards it is an **integer** representing cents.

```php
// 1.x
new CustomerDirectDebitTransferInformation('500', $iban, $name); // 500 = "five hundred" string

// 2.x and later
new CustomerDirectDebitTransferInformation(50000, $iban, $name); // 50000 cents = 500.00 EUR
```

Files generated with the old string form may have contained the wrong amounts — re-export anything you still rely on.

### PSR-4 layout

The 2.0 line introduced the `Digitick\Sepa\` PSR-4 namespace structure used today. If you had hand-built `require` statements pointing at the old layout, replace with Composer autoload.

### PHP 7.2 minimum

PHP 5.6 / 7.0 / 7.1 support was dropped. `DateTimeInterface` is used throughout instead of `DateTime`.

## Pre-1.x

Not supported. Migrate via 1.x → 2.x → 3.x in sequence if you're still on those releases.

## Related

- [`CHANGELOG.md`](../CHANGELOG.md) — full release history
- [Reference: pain version matrix](reference/pain-version-matrix.md) — which message versions are usable in your release
