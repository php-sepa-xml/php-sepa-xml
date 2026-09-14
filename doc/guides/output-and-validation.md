---
title: "Output and validation"
description: "asXML / asDOC / DomBuilder output methods, what validate() checks, and XSD validation."
---

# Output and validation

> **At a glance**
>
> - **Use this when:** picking which output method to call, or validating the output against an XSD.
> - **Key types:** `BaseCustomerTransferFileFacade::asXML` / `asDOC` / `validateSchema`, `BaseDomBuilder::asXml` / `asDoc` / `validateSchema`, `BaseTransferFile::validate`.
> - **Output:** an XML string or a `DOMDocument` you can further mutate.

Four serialisation entry points, one validation pass. This page is the reference for which one to call and when validation fires.

## Output methods

### `$facade->asXML(): string`

The 90%-case path. Renders, caches, returns a UTF-8 string.

```php
$xml = $facade->asXML();
file_put_contents('out.xml', $xml);
```

> ⚠️ **Gotcha**
>
> First call walks the tree and caches; subsequent calls return the cached string. `addPaymentInfo` / `addTransfer` after the first `asXML()` throw `\LogicException`. See [Gotchas: facades are single-shot](../gotchas.md#facades-are-single-shot--asxml-finalises-them).

### `$facade->asDOC(): \DOMDocument`

Same single-shot semantics, but returns a `DOMDocument` you can mutate before serialising. Useful for:

- XML-DSig signing
- Custom XPath fixups for a bank that wants a non-standard child order
- Injecting comments / processing instructions

```php
$doc = $facade->asDOC();
// mutate $doc here
$xml = $doc->saveXML();
```

### `$domBuilder->asXml(): string`

When you've used the direct flow (`DomBuilderFactory::createDomBuilder(...)`), pull the XML straight off the builder. Idempotent — call as many times as you like.

### `$domBuilder->asDoc(): \DOMDocument`

Same as the facade's `asDOC` but on the lower-level builder. Mutations between `asDoc()` calls survive (no caching). Useful when you want to render → mutate → render-again in a loop.

## Validation

`BaseTransferFile::accept(DomBuilder)` calls `validate()` before walking the tree. Both flows trigger this automatically:

- **Facade flow:** `validate()` runs inside `asXML()` / `asDOC()` (via the internal `finalize()` → `transferFile->accept($domBuilder)` chain).
- **Direct flow:** `DomBuilderFactory::createDomBuilder($file, ...)` calls `$file->accept($domBuilder)` internally — validation runs before the factory returns.

> ⚠️ **Gotcha**
>
> A misconfigured object graph will throw at **render time**, not at construction time. If you want earlier feedback, call `$transferFile->validate()` explicitly. See [Gotchas: validate() runs automatically](../gotchas.md#validate-runs-automatically--but-only-at-render-time).

### What `validate()` checks

| Check                                                     | Class                           | Exception                            |
|-----------------------------------------------------------|---------------------------------|--------------------------------------|
| At least one `PaymentInformation` attached                | `BaseTransferFile`              | `InvalidTransferFileConfiguration`   |
| (CT) Each PaymentInformation has ≥1 transfer              | `CustomerCreditTransferFile`    | `InvalidTransferFileConfiguration`   |
| (CT) All transfers are `CustomerCreditTransferInformation`| `CustomerCreditTransferFile`    | `InvalidTransferTypeException`       |
| (DD) PaymentInformation has a `sequenceType`              | `CustomerDirectDebitTransferFile` | `InvalidTransferFileConfiguration` |
| (DD) PaymentInformation has a `creditorId`                | `CustomerDirectDebitTransferFile` | `InvalidTransferFileConfiguration` |
| (DD) All transfers are `CustomerDirectDebitTransferInformation` | `CustomerDirectDebitTransferFile` | `InvalidTransferTypeException` |

### What `validate()` does **not** check

- Per-transfer mandate fields (`mandateId`, `mandateSignDate`) — missing values produce empty XML elements that banks will reject.
- IBAN format / checksum.
- BIC format.
- Whether the amount fits in your domestic currency rules.
- XSD conformance — see below.

## XSD validation

The ISO 20022 XSDs for every [supported pain version](../reference/pain-version-matrix.md) ship with the library, under `doc/ISO20022/`. Validate the rendered file against the matching one:

```php
$errors = $facade->getSchemaValidationErrors(); // string[], one "Line N: message" per error
if ($errors !== []) {
    // log / reject
}

$facade->validateSchema(); // or just a bool
```

The same two methods exist on `BaseDomBuilder` for the direct flow. On a facade they render the document first, with the same single-shot semantics as `asXML()`. The serialised XML is what gets validated, so changes made through `asDOC()` / `asDoc()` are included.

Pass a path to validate against another XSD — a bank's variant schema, or a pain version outside the supported list, which has no bundled XSD:

```php
$facade->validateSchema('/path/to/bank-variant.xsd');
```

Both methods throw `Digitick\Sepa\Exception\InvalidArgumentException` when the path doesn't exist, or when no XSD is bundled for the format and none was passed.

> ⚠️ **Gotcha**
>
> Bank-profile output isn't always valid against the base ISO XSD: `setOmitAgentElementIfBicMissing(true)` can drop `<DbtrAgt>` / `<CdtrAgt>` elements the ISO schema requires. Validate those files against the bank's own XSD. See [Bank profiles](bank-profiles.md).

Some banks publish variant XSDs alongside their integration docs — use those when present, since variants may add or constrain elements. Other ISO 20022 releases are in the [message catalogue](https://www.iso20022.org/full_catalogue.page).

## Schema location

`BaseDomBuilder::__construct(string $painFormat, bool $withSchemaLocation = true)` controls whether the root element carries an `xsi:schemaLocation` attribute. Most banks accept it; a few require it absent. Toggle via:

- **Facade:** `TransferFileFacadeFactory::createCustomerCreditWithGroupHeader($header, $painFormat, $withSchemaLocation = true)` — only the credit-transfer factory exposes this flag.
- **Direct:** pass `false` as the third arg to `DomBuilderFactory::createDomBuilder($file, $painFormat, $withSchemaLocation)`.

## Related

- [Reference: Exceptions](../reference/exceptions.md) — full thrown-by list
- [Reference: DomBuilder](../reference/classes/dom-builder.md)
- [Reference: CustomerCreditTransferFile](../reference/classes/customer-credit-transfer-file.md)
- [Reference: CustomerDirectDebitTransferFile](../reference/classes/customer-direct-debit-file.md)
- [Bank profiles](bank-profiles.md) — known per-bank schema-location / control-sum / BIC requirements
