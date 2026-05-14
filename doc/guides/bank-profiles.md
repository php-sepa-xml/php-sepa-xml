---
title: "Bank Profiles"
description: "Country- and bank-specific recipes for producing files that pass validation at each institution."
---

# Bank Profiles

Country- and bank-specific recipes for producing files that pass validation
at institutions whose XML conventions diverge from the bare ISO 20022
defaults. Phase 1 of the documentation restructure covers only the two
generic flags. Per-bank recipes (German DK, Spanish initiating-party,
NL Rabo, AT Raiffeisen, AT Volksbank) are added in Phase 2.

## Generic flags

The library exposes two opt-in flags via `BaseDomBuilder` and as passthrough
methods on `BaseCustomerTransferFileFacade`. Both default to `false`, so
existing callers are unaffected.

- `setOmitGroupHeaderControlSum(bool)` — suppresses `<CtrlSum>` inside
  `<GrpHdr>`. Required by the German DK pain.001.001.03 profile, which
  forbids CtrlSum at group-header level.
- `setOmitAgentElementIfBicMissing(bool)` — omits the whole
  `<CdtrAgt>` / `<DbtrAgt>` wrapper when the corresponding BIC is missing,
  instead of emitting `<Othr><Id>NOTPROVIDED</Id></Othr>`. Applied at all
  four agent-element call sites (SCT and SDD, payment and transfer levels).

Set the flags on the facade instance before adding transfers:

```php
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

$customerCredit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me');

$customerCredit->setOmitGroupHeaderControlSum(true);
$customerCredit->setOmitAgentElementIfBicMissing(true);
```

The same two methods are available on the Direct Debit facade.
