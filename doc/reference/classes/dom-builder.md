---
title: "DomBuilder (BaseDomBuilder, concrete subclasses, DomBuilderFactory)"
description: "Public surface of the DomBuilder family — the visitor that walks a TransferFile and emits XML."
---

# DomBuilder (`BaseDomBuilder`, concrete subclasses, `DomBuilderFactory`)

**Namespace:** `Digitick\Sepa\DomBuilder\*`
**Files:** `src/DomBuilder/{BaseDomBuilder,CustomerCreditTransferDomBuilder,CustomerDirectDebitTransferDomBuilder,DomBuilderFactory}.php`

The DomBuilder is the visitor that walks a `TransferFile` and emits the XML. Most code uses it via `DomBuilderFactory` or transparently through a facade. Construct it directly when you need to tweak its options (omit-flags, schema location) before rendering.

## `DomBuilderFactory::createDomBuilder(...)`

```php
public static function createDomBuilder(
    TransferFileInterface $transferFile,
    string $painFormat = '',
    bool $withSchemaLocation = true
): DomBuilderInterface
```

Dispatches on the concrete `TransferFile` class:

- `CustomerCreditTransferFile` → `CustomerCreditTransferDomBuilder`
- `CustomerDirectDebitTransferFile` → `CustomerDirectDebitTransferDomBuilder`
- anything else → `InvalidArgumentException`

**Important:** the factory calls `$transferFile->accept($domBuilder)` before returning. The DOM is already built by the time you have the builder back — you can call `asXml()` / `asDoc()` immediately.

If `$painFormat` is an empty string the concrete subclass's own default kicks in (`pain.001.001.09` / `pain.008.001.09`).

## `BaseDomBuilder` constructor

```php
public function __construct(
    string $painFormat,
    bool $withSchemaLocation = true
)
```

The concrete subclasses provide defaults:

```php
new CustomerCreditTransferDomBuilder($painFormat = 'pain.001.001.09', $withSchemaLocation = true)
new CustomerDirectDebitTransferDomBuilder($painFormat = 'pain.008.001.09', $withSchemaLocation = true)
```

- **`$painFormat`** — one of the supported message names. See [pain version matrix](../pain-version-matrix.md).
- **`$withSchemaLocation`** — emit `xsi:schemaLocation` on the root element. Some banks require it; some reject it.

## Key methods

### `asXml(): string`

Serialise the DOM to a UTF-8 XML string. Idempotent — safe to call multiple times.

### `asDoc(): DomDocument`

Return the underlying `DOMDocument`. Use this for downstream mutation (signing, encryption, manual XPath fixups).

### `setOmitGroupHeaderControlSum(bool $omit): void`

When `true`, suppresses the `<CtrlSum>` element under `<GrpHdr>`. Required by some bank profiles (notably German DK pain.001.001.03). Per-payment `<CtrlSum>` is still emitted.

### `setOmitAgentElementIfBicMissing(bool $omit): void`

When `true`, omits the `<CdtrAgt>` / `<DbtrAgt>` block entirely when no BIC is present. When `false` (default for older pain versions), emits `<FinInstnId><Othr><Id>NOTPROVIDED</Id></Othr>` as the placeholder.

### `visitGroupHeader(GroupHeader $groupHeader): void` / `visitTransferFile(...)` / etc.

The visitor entry points. You rarely call these — they're invoked by `TransferFile::accept($domBuilder)`. Listed here for completeness if you're implementing a custom traversal.

### `getIbanElement(string $iban): DOMElement`

Helper that returns a properly wrapped `<IBAN>` element. Exposed because some subclasses need to splice IBANs into custom blocks.

## Concrete subclasses

`CustomerCreditTransferDomBuilder` and `CustomerDirectDebitTransferDomBuilder` differ only in which XML namespace / root element they write and which visit methods accept which transfer types. Don't subclass these unless you really need a new transfer type; the constants and element naming are version-tied.

## Related

- [Reference: pain version matrix](../pain-version-matrix.md)
- [Reference: CustomerCreditTransferFile](customer-credit-transfer-file.md)
- [Reference: CustomerDirectDebitTransferFile](customer-direct-debit-file.md)
- [Guide: Output and validation](../../guides/output-and-validation.md)
- [Guide: Bank profiles](../../guides/bank-profiles.md)
