---
title: "Choosing: Facade vs. Direct construction"
description: "Pick the right entry point — facade for speed and array config, direct construction for control."
---

# Choosing: Facade vs. Direct construction

The library exposes two entry points to the same XML output. Pick based on the controls you need.

## Decision matrix

| Need                                                  | Facade | Direct construction |
|-------------------------------------------------------|:------:|:-------------------:|
| Get a working file fast                               | ✅     |                     |
| Pass associative arrays from a form / config         | ✅     |                     |
| Type-checked construction (signatures, IDE help)      |        | ✅                  |
| Customise the `GroupHeader` (isTest flag, party ID)   | ⚠️*    | ✅                  |
| Multiple `PaymentInformation` blocks per file         | ✅     | ✅                  |
| Per-transfer non-EEA address cascade                  | ✅†    | ✅                  |
| Direct-debit mandate amendments                       | ✅     | ✅                  |
| Modify the rendered DOM (signing, encryption)         | ✅‡    | ✅                  |
| Re-render after mutations                             |        | ✅                  |
| Set arbitrary BaseTransferInformation fields          |        | ✅                  |

\* Use `TransferFileFacadeFactory::createCustomerCreditWithGroupHeader($groupHeader, ...)` to inject a hand-built `GroupHeader`.
† Facade supports the standard address keys (`postCode`, `streetName`, etc.); some less-common fields require the direct path.
‡ Call `$facade->asDOC()` (instead of `asXML()`) to retrieve the `DOMDocument`, then mutate before serialising.

## Sketch: facade flow

```php
$facade = TransferFileFacadeFactory::createCustomerCredit('msg-1', 'My Co');
$facade->addPaymentInfo('p1', [/* ... */]);
$facade->addTransfer('p1', [/* ... */]);
$xml = $facade->asXML();
```

> ⚠️ **Gotcha**
>
> Once `asXML()` runs, the facade is frozen. Subsequent `addPaymentInfo` / `addTransfer` calls throw `\LogicException`. Build a new facade if you need to amend. See [Gotchas: facades are single-shot](../gotchas.md#facades-are-single-shot--asxml-finalises-them).

## Sketch: direct flow

```php
$header   = new GroupHeader('msg-1', 'My Co');
$file     = new CustomerCreditTransferFile($header);
$payment  = new PaymentInformation('p1', $iban, $bic, 'My Co');
$transfer = new CustomerCreditTransferInformation(12500, $creditorIban, 'Supplier');
$transfer->setRemittanceInformation('Invoice 2026-04-17');

$payment->addTransfer($transfer);
$file->addPaymentInformation($payment);

$domBuilder = DomBuilderFactory::createDomBuilder($file, 'pain.001.001.09');
$xml = $domBuilder->asXml();
```

You can mutate `$transfer` and `$payment` between `addTransfer`/`addPaymentInformation` and `createDomBuilder`. After `createDomBuilder` returns, the DOM is built — mutations to source objects no longer affect output, but you can call `$domBuilder->asDoc()` to mutate the DOM directly and `asXml()` again.

## Heuristics

- **Single batch of similar payments** → facade.
- **Different sequence types or due dates in the same file** → both work; direct flow makes the `PaymentInformation` grouping more visible.
- **You need to set a field the facade array shape doesn't expose** → direct flow.
- **You need to render twice with different DOM tweaks** → direct flow (facade caches).
- **Library upgrade safety** → facade. Internal class signatures are version-tied; the facade array shape is the more stable contract.

## Related

- [Reference: Facade](../reference/classes/facade.md) — full array-key tables for `addPaymentInfo` / `addTransfer`
- [Reference: PaymentInformation](../reference/classes/payment-information.md)
- [Reference: CustomerCreditTransferInformation](../reference/classes/customer-credit-transfer-information.md)
- [Reference: CustomerDirectDebitTransferInformation](../reference/classes/customer-direct-debit-transfer-information.md)
- [Guide: Group Header](group-header.md) — using the `WithGroupHeader` factories
