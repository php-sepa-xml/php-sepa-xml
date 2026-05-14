---
title: "Addresses"
description: "Emit structured postal addresses for credit and direct debit transfers, including non-EEA debtors."
---

# Addresses

If the debtor account belongs to a bank that is not a member of the European
Economic Area (EEA), the address data of the account holder must be added to
the transaction. For sure one must do this for the following countries:
Switzerland, Andorra, Monaco, San Marino, Vatican City and the United Kingdom.
Though it is generally a good practice to add this data anyway.

## Direct Debit example

```php
$directDebit->addTransfer('firstPayment', [
    'amount'            => 1499,
    'debtorIban'        => 'CH6089144731137988786',
    'debtorBic'         => 'CRESCHZZXXX',
    'debtorName'        => 'John Doe',
    // ...
    // and the relevant address data
    'debtorCountry'     => 'CH',
    'postCode'          => '8245',
    'townName'          => 'Feuerthalen',
    'streetName'        => 'Example Street',
    'buildingNumber'    => '12',
    'floorNumber'       => '13'
]);
```

## Credit Transfer example

For Credit Transfer the equivalent address setters live on
`CustomerCreditTransferInformation` and are called individually:

```php
$transfer->setCountry('BG');
$transfer->setPostCode('1000');
$transfer->setTownName('Nowhere');
$transfer->setStreetName('Some Street');
$transfer->setBuildingNumber(12);
$transfer->setFloorNumber(13);
```
