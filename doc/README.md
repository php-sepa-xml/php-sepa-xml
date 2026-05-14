# php-sepa-xml Documentation

> Phase 1 and Phase 2 landed. Phase 3 (cross-page polish —
> frontmatter, "at a glance" boxes, callout convention) is upcoming.

## Start here

- [Getting started](getting-started.md) — install, minimal credit transfer + direct debit examples
- [Gotchas](gotchas.md) — sharp edges in one place
- [Migrations](migrations.md) — upgrade notes between major versions

## Guides

- [Credit Transfer](guides/credit-transfer.md)
- [Direct Debit](guides/direct-debit.md)
- [Choosing facade vs direct construction](guides/choosing-facade-vs-direct.md)
- [Output and validation](guides/output-and-validation.md)
- [Custom sanitization](guides/custom-sanitization.md)
- [Group Header](guides/group-header.md)
- [Addresses](guides/addresses.md)
- [Amendments](guides/amendments.md)
- [Bank profiles](guides/bank-profiles.md)

## Reference

- [pain version matrix](reference/pain-version-matrix.md)
- [ISO 20022 message names](reference/iso20022-naming.md)
- [Exceptions](reference/exceptions.md)

### Class reference

- [GroupHeader](reference/classes/group-header.md)
- [PaymentInformation](reference/classes/payment-information.md)
- [CustomerCreditTransferFile](reference/classes/customer-credit-transfer-file.md)
- [CustomerDirectDebitTransferFile](reference/classes/customer-direct-debit-file.md)
- [CustomerCreditTransferInformation](reference/classes/customer-credit-transfer-information.md)
- [CustomerDirectDebitTransferInformation](reference/classes/customer-direct-debit-transfer-information.md)
- [Facades (`CustomerCreditFacade`, `CustomerDirectDebitFacade`, `TransferFileFacadeFactory`)](reference/classes/facade.md)
- [DomBuilder (`BaseDomBuilder`, concrete subclasses, `DomBuilderFactory`)](reference/classes/dom-builder.md)
- [Sanitizer](reference/classes/sanitizer.md)

## Contributing

- [Contributing](contributing.md)
