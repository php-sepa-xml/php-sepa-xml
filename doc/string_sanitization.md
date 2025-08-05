## Custom String Sanitization

By default, this package sanitizes strings using an internal helper method — `StringHelper::sanitizeString()` — to ensure safe and valid output for SEPA XML.

If the default sanitization doesn't suit your needs, you can override it globally using the `Sanitizer::setSanitizer()` method.

Note: XML entities (like `<`, `>`, `&`, etc.) will still be escaped separately. This customization affects **pre-processing** before XML generation.

### Example: Custom Sanitization
```php
use SepaXml\Util\Sanitizer;

// Change the global sanitizer to a custom implementation
Sanitizer::setSanitizer(function (string $value): string {
    return strtoupper($value);
});
```

### Example: Disable Sanitization
```php
use SepaXml\Util\Sanitizer;

// Disable the sanitizer globally
Sanitizer::disableSanitizer();
```

### Reset Sanitization
```php
use SepaXml\Util\Sanitizer;

// Reset the sanitizer to its default behavior
Sanitizer::resetSanitizer();
```