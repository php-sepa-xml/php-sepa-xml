---
title: "Bank Profiles"
description: "Country- and bank-specific recipes for producing files that pass validation at each institution."
---

# Bank Profiles

> **At a glance**
>
> - **Use this when:** a specific institution rejects the bare ISO 20022 defaults.
> - **Key types:** `BaseDomBuilder` / `BaseCustomerTransferFileFacade` and their two opt-in flags.
> - **Output:** bank-compatible XML that passes the institution's validation.

Country- and bank-specific recipes for producing files that pass validation
at institutions whose XML conventions diverge from the bare ISO 20022
defaults. The library exposes the per-bank quirks via two opt-in flags on
`BaseDomBuilder` (and as passthroughs on `BaseCustomerTransferFileFacade`).

## Generic flags

Both flags default to `false`, so existing callers are unaffected.

> 🏦 **Bank profile — German DK**
>
> `setOmitGroupHeaderControlSum(bool)` suppresses `<CtrlSum>` inside
> `<GrpHdr>`. Required by the German DK pain.001.001.03 profile, which
> forbids `CtrlSum` at group-header level.

> 🏦 **Bank profile — BIC-optional pain versions**
>
> `setOmitAgentElementIfBicMissing(bool)` omits the whole
> `<CdtrAgt>` / `<DbtrAgt>` wrapper when the corresponding BIC is missing,
> instead of emitting `<Othr><Id>NOTPROVIDED</Id></Othr>`. Applied at all
> four agent-element call sites (SCT and SDD, payment and transfer levels).

Set the flags on the facade instance before adding transfers:

```php
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

$customerCredit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me');

$customerCredit->setOmitGroupHeaderControlSum(true);
$customerCredit->setOmitAgentElementIfBicMissing(true);
```

The same two methods are available on the Direct Debit facade.

## Confirmed institutions

The project README maintains the canonical list of confirmed institutions
and the pain versions they accept (RABO, Raiffeisen, Volksbank, ING,
Commerzbank, CaixaBank, SantanderBank). See the
[main README](../../README.md#installation) for the current list — verify
generated files with your bank before any production run.

## Related

- [Reference: DomBuilder](../reference/classes/dom-builder.md)
- [Reference: Facade](../reference/classes/facade.md)
- [Reference: pain version matrix](../reference/pain-version-matrix.md)
- [Gotchas](../gotchas.md)
