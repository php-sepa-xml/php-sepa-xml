---
title: "Custom String Sanitization"
description: "Override or disable the default string sanitiser applied before XML output."
---

# Custom String Sanitization

> **At a glance**
>
> - **Use this when:** the default SEPA character-set sanitiser doesn't match your bank's expectations.
> - **Key types:** `Digitick\Sepa\Util\Sanitizer`, `StringHelper`.
> - **Output:** process-global side effect applied to every subsequent DOM write.

By default, this package sanitizes strings using an internal helper method — `StringHelper::sanitizeString()` — to ensure safe and valid output for SEPA XML.
If the default sanitization doesn't suit your needs, you can override it globally using the `Sanitizer::setSanitizer()` method.

> ℹ️ **Version note**
>
> XML entities (`<`, `>`, `&`, etc.) are escaped separately by the DOM. The `Sanitizer` customisation affects **pre-processing** before XML generation, not output-side escaping.

> ⚠️ **Gotcha**
>
> The sanitiser is **global, static state**. Installing a custom callback affects every subsequent DOM write in the process — call `Sanitizer::resetSanitizer()` at the end of short-lived scripts and tests. See [Gotchas: character set](../gotchas.md#character-set-iso-20022-has-a-sepa-approved-subset).

## Example: Custom Sanitization
```php
use Digitick\Sepa\Util\Sanitizer;

// Change the global sanitizer to a custom implementation
Sanitizer::setSanitizer(function (string $value): string {
    return strtoupper($value);
});
```

## Example: Disable Sanitization
```php
use Digitick\Sepa\Util\Sanitizer;

// Disable the sanitizer globally
Sanitizer::disableSanitizer();
```

## Reset Sanitization
```php
use Digitick\Sepa\Util\Sanitizer;

// Reset the sanitizer to its default behavior
Sanitizer::resetSanitizer();
```

## Related

- [Reference: Sanitizer](../reference/classes/sanitizer.md)
- [Gotchas: character set](../gotchas.md#character-set-iso-20022-has-a-sepa-approved-subset)
