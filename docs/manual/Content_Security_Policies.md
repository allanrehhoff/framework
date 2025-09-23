# Content Security Policies

Content Security Policy (CSP) is a crucial security feature that helps prevent various types of attacks, such as Cross-Site Scripting (XSS).
The framework provides a flexible way to configure CSP through configuration files.

## Configuration Location

CSP configurations are stored in the `storage/content-security-policies` directory.
Best practice dictates each domain should have its own configuration file.

```text
storage/content-security-policies/
├── example.com.jsonc
├── admin.example.com.jsonc
└── api.example.com.jsonc
```

## Default Configuration

The framework employs a strict default CSP configuration:

```jsonc
{
	"default-src": [
		"'self'"
	],
	"script-src": [
		"'self'"
	],
	"style-src": [
		"'self'"
	],
	"img-src": [
		"'self'",
		"data:"
	],
	"connect-src": [
		"'self'"
	],
	"font-src": [
		"'self'",
		"data:"
	],
	"object-src": [
		"'none'"
	],
	"frame-ancestors": [
		"'none'"
	],
	"base-uri": [
		"'self'"
	],
	"form-action": [
		"'self'"
	]
}
```

## Nonce Support

The framework automatically generates a nonce for each request to securely allow specific inline scripts and styles.
This nonce is available in your views as the `$nonce` variable.

```html
<!-- Secure inline script with nonce -->
<script nonce="<?php echo $nonce; ?>"></script>

<!-- Secure inline styles with nonce -->
<style nonce="<?php echo $nonce; ?>"></style>
```

The nonce is automatically included in the CSP header and changes with each request.

## Common Values

- `'self'`: Allow resources from the same origin
- `'none'`: Block all resources of this type
- `'nonce-[value]'`: Allow resources with matching nonce (automatically handled)
- `data:`: Allow data: URIs
- `https://example.com`: Allow resources from specific domain

Remember that CSP is an important security feature, and you should carefully consider any modifications to the default policy.
