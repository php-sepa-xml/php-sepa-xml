---
title: "pain Version Matrix"
description: "Catalogue of supported pain.001.* and pain.008.* message versions and their defaults."
---

# pain Version Matrix

The library accepts any pain version string matching `pain.NNN.NNN.NN`, but only the formats listed below are explicitly supported (validated against the XSD and exercised by tests). The list is sourced from `Digitick\Sepa\Util\MessageFormat::$supportedMessageFormats`.

If you pass a version not on this list the library still tries to render it (treating the closest known schema as the template), so in theory intermediate versions work too — but verify the output against your bank's XSD before production use.

## Credit Transfer — `pain.001.*`

| Version          | Notes                                                |
|------------------|------------------------------------------------------|
| pain.001.001.03  |                                                      |
| pain.001.001.04  |                                                      |
| pain.001.001.05  |                                                      |
| pain.001.001.06  |                                                      |
| pain.001.001.07  |                                                      |
| pain.001.001.08  |                                                      |
| pain.001.001.09  | **Default** (`MessageFormat::$defaultMessageFormats['SCT']`). Minimum version recommended for new integrations. |
| pain.001.001.10  |                                                      |
| pain.001.001.11  |                                                      |
| pain.001.001.12  |                                                      |
| pain.001.001.13  |                                                      |
| pain.001.002.03  | Variant — `STPCreditTransferInitiationV03`           |
| pain.001.003.03  | Variant — `EUSTPCreditTransferInitiationV03`         |

## Direct Debit — `pain.008.*`

| Version          | Notes                                                |
|------------------|------------------------------------------------------|
| pain.008.001.02  |                                                      |
| pain.008.001.03  |                                                      |
| pain.008.001.04  |                                                      |
| pain.008.001.05  |                                                      |
| pain.008.001.06  |                                                      |
| pain.008.001.07  |                                                      |
| pain.008.001.08  | Minimum version recommended for new integrations.    |
| pain.008.001.09  | **Default** (`MessageFormat::$defaultMessageFormats['SDD']`). |
| pain.008.001.10  |                                                      |
| pain.008.001.11  |                                                      |
| pain.008.001.12  |                                                      |
| pain.008.002.02  | Variant                                              |
| pain.008.003.02  | Variant                                              |

## Picking a version

- **Facade flow:** if you omit the third arg to `TransferFileFacadeFactory::createCustomerCredit(...)` the library uses `pain.001.001.03` (factory default), not the `defaultMessageFormats` constant. Pass an explicit version to opt into newer schemas.
- **Direct flow:** pass the version string to `DomBuilderFactory::createDomBuilder($transferFile, $painFormat)`.
> 🏦 **Bank profile — older banks**
>
> Some institutions are locked to older versions (e.g. RABO direct-debit historically accepts `pain.008.001.02` only). Check [Bank profiles](../guides/bank-profiles.md) and confirm with your bank before picking a version.

## Related

- [Reference: ISO 20022 message names](iso20022-naming.md) — naming convention reference
- [Guide: Credit Transfer](../guides/credit-transfer.md)
- [Guide: Direct Debit](../guides/direct-debit.md)
- [Bank profiles](../guides/bank-profiles.md)
