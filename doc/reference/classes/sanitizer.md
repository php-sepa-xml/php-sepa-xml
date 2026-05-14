# Sanitizer

**Namespace:** `Digitick\Sepa\Util\Sanitizer`
**File:** `src/Util/Sanitizer.php`

Global, static-state sanitiser hooked into every DOM write. The DomBuilder feeds every user-supplied string through `Sanitizer::sanitize()` before placing it in the XML, so unsupported characters never reach the bank. Replace the default if you have stricter rules (or want to be more permissive).

## Static methods

### `setSanitizer(callable $callback): void`

Install a custom sanitiser. The callable must accept a `string` and return a `string`.

```php
Sanitizer::setSanitizer(function (string $value): string {
    // your rules
    return $cleaned;
});
```

Global state — affects every subsequent DOM write in the process. Call `resetSanitizer()` at the end of a test or short-lived script.

### `getSanitizer(): callable`

Returns the currently-installed callable. Falls back to `[StringHelper::class, 'sanitizeString']` if none has been set.

### `sanitize(string $value): string`

Apply the current sanitiser. The DomBuilder calls this on your behalf — you only need to call it directly if you're prepping strings for a custom flow.

### `disableSanitizer(): void`

Install a no-op callable. Use with care: the bank will receive your strings verbatim. Mostly useful for tests that need to verify XML escaping behaviour.

### `resetSanitizer(): void`

Restore the default. Equivalent to clearing the override and letting `getSanitizer()` fall back to `StringHelper::sanitizeString`.

## Default behaviour

The default sanitiser is `Digitick\Sepa\Util\StringHelper::sanitizeString`. It normalises non-SEPA characters (cyrillic, accented variants, some punctuation) to a SEPA-compliant subset. The exact mapping lives in `StringHelper`; consult that file if you need the precise rules.

## Related

- [Guide: Custom sanitization](../../guides/custom-sanitization.md) — when and how to swap the default
- [Gotchas](../../gotchas.md) — character-set notes
