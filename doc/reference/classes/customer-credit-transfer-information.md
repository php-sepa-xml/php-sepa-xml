# CustomerCreditTransferInformation

**Namespace:** `Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation`
**File:** `src/TransferInformation/CustomerCreditTransferInformation.php`

A single credit transfer line (`<CdtTrfTxInf>`). Extends `BaseTransferInformation`; the credit-transfer-specific surface is small — most setters live on the base class.

## Constructor

```php
public function __construct(
    int $amount,
    string $iban,
    string $name,
    ?string $identification = null
)
```

Inherited from `BaseTransferInformation`.

- **`$amount`** — **integer cents**, not euros. `500` means 5.00 EUR. See [Gotchas](../../gotchas.md).
- **`$iban`** — creditor IBAN (the recipient).
- **`$name`** — creditor name.
- **`$identification`** — used as the initial `EndToEndIdentification` when set. Optional; if omitted, the facade fills it in as `paymentId + transferIndex`, and the direct flow leaves it null until you call `setEndToEndIdentification`.

## Key methods (inherited from `BaseTransferInformation` unless noted)

### `setBic(string $bic): void`

Creditor agent BIC. Optional in newer pain versions; pair with `setOmitAgentElementIfBicMissing(true)` on the DomBuilder when absent.

### `setEndToEndIdentification(string $EndToEndIdentification): void`

`<EndToEndId>` — caller-owned identifier that round-trips through the banking network. Use this to correlate rejects/returns to your records.

### `setInstructionId(string $instructionId): void`

`<InstrId>` — your bank's processing-side identifier. Distinct from `EndToEndId`.

### `setCreditorReference(string $creditorReference): void` + `setCreditorReferenceType(string $creditorReferenceType): void`

Structured remittance reference (`<RmtInf><Strd><CdtrRefInf>`). Setting this typically suppresses unstructured `setRemittanceInformation` output — pick one or the other.

### `setPurposeCode(string $purposeCode): void`

`<Purp><Cd>`. ISO 20022 purpose code, e.g. `SALA` (salary), `SUPP` (supplier), `TAXS` (tax).

### `setRemittanceInformation(string $remittanceInformation): void`

`<RmtInf><Ustrd>`. Free-text message visible on the recipient's statement. Mutually exclusive with `setCreditorReference` in many bank profiles.

### Address setters

`setCountry`, `setPostCode`, `setTownName`, `setStreetName`, `setBuildingNumber`, `setFloorNumber`, `setPostalAddress`. Emit `<CdtrPstlAdr>`. See [Addresses](../../guides/addresses.md) — fill the whole cascade or none of it.

### `getCreditorName(): string`

CT-specific accessor (subclass override) returning the `name` passed to the constructor. Symmetric with `getDebitorName()` on the direct-debit subclass.

### `getUUID(): ?string`

Auto-generated UUIDv4 (UETR) on construction since v2.3.0. Returned by `accept(DomBuilder)`. Store it if you need to track the SWIFT side.

## Related

- [Reference: PaymentInformation](payment-information.md)
- [Reference: CustomerDirectDebitTransferInformation](customer-direct-debit-transfer-information.md)
- [Guide: Credit Transfer](../../guides/credit-transfer.md)
- [Guide: Addresses](../../guides/addresses.md)
