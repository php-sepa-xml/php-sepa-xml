# Gotchas

Subtle things that bite. Read this before you debug your first "but the bank rejected my file" ticket.

## Amounts are integer cents, not floats

`BaseTransferInformation::__construct` takes `int $amount` as its first arg. `500` means 5.00 EUR. Passing `5.00` is silently truncated to `5`. In the facade form the same rule applies to the `amount` array key.

## Sequence type lives on the PaymentInformation, not the transfer

For direct debits, `S_FIRST` / `S_RECURRING` / `S_ONEOFF` / `S_FINAL` are set with `PaymentInformation::setSequenceType($constant)`. All transfers grouped under one `PaymentInformation` share that type. If you need a mix, create separate `PaymentInformation` blocks.

## Currency defaults to EUR; override per-PaymentInformation, not per-transfer

`PaymentInformation::__construct` has `$originAccountCurrency = 'EUR'` as the fifth arg. There is no `setCurrency` on the transfer info that affects the issued amount currency — the file uses the `PaymentInformation` currency for all its children.

## `validate()` runs automatically — but only at render time

Both flows call `validate()` for you: `DomBuilderFactory::createDomBuilder($file, ...)` invokes `$file->accept($builder)` which validates first, and the facade does the same inside its `asXML()` / `asDOC()`. So you don't need to call `validate()` explicitly — but the throw happens at the moment you ask for output, not when you build the object graph. See [Reference: Exceptions](reference/exceptions.md).

## BIC is optional in newer pain versions but the XML still emits the element

If your bank refuses files because the `<BIC>` element is present but empty, flip `setOmitAgentElementIfBicMissing(true)` on the facade or DomBuilder. See [Bank profiles](guides/bank-profiles.md).

## Group header control sum may need to be omitted

Some banks reject the `<CtrlSum>` element in the group header (only accepting per-PmtInf control sums). Set `setOmitGroupHeaderControlSum(true)` on the facade or DomBuilder.

## `mandateSignDate` accepts two different types depending on entry point

- **Facade array form** (`addTransfer(['debtorMandateSignDate' => '13.10.2012'])`): accepts a string in `d.m.Y` format.
- **Direct construction** (`$transfer->setMandateSignDate(...)`): requires a `DateTimeInterface`.

Mixing these is a common source of "why is my date 1970-01-01" output.

## DD non-EEA addresses need the full setter cascade

For SEPA Direct Debit to a non-EEA debtor, the XML requires a structured `<PstlAdr>` block. You must call `setCountry`, `setPostCode`, `setTownName`, `setStreetName`, `setBuildingNumber`, and `setFloorNumber` on the `CustomerDirectDebitTransferInformation`. Setting only some of them emits a partial address that some banks reject. See [Addresses](guides/addresses.md).

## Character set: ISO 20022 has a SEPA-approved subset

Non-conforming characters (cyrillic, accented variants, emoji, ...) are sanitised via `Digitick\Sepa\Util\Sanitizer` before being written to the DOM. The default sanitiser is `StringHelper::sanitizeString`. Override with `Sanitizer::setSanitizer(callable)` if you need custom rules. See [Custom sanitization](guides/custom-sanitization.md).

## `endToEndId` vs `instructionId` vs `UUID`

Three different identifiers travel with each transfer; they're not interchangeable.

- **`endToEndId`** — caller-supplied, round-trips through the banking network end-to-end. Use this to correlate rejects/returns to your records.
- **`instructionId`** — caller-supplied, ID used by your bank's processing systems.
- **`UUID`** (UETR) — auto-generated UUIDv4 since v2.3.0, retrievable via `$transfer->getUUID()`. Store it if you want to track the SWIFT-side journey.

## Facades are single-shot — `asXML()` finalises them

After the first call to `asXML()` or `asDOC()` the facade is frozen: subsequent `addPaymentInfo` / `addTransfer` calls throw `\LogicException`. Subsequent `asXML()` calls return a cached string. If you need to amend the output, build a new facade rather than reusing one.

## `setNumberOfTransactions` is usually wrong if you set it manually

The DomBuilder writes the actual count when generating the XML. Setting `GroupHeader::setNumberOfTransactions` manually is for advanced cases (e.g. building the header without yet having attached all the payments). For 99% of flows, leave it alone.

## See also

- [Reference: Exceptions](reference/exceptions.md)
- [Reference: pain version matrix](reference/pain-version-matrix.md)
- [Bank profiles](guides/bank-profiles.md)
