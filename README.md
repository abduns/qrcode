# qrcode

A from-scratch, framework-agnostic QR Code generator for PHP 8.2+ with zero required runtime dependencies.

[![Tests](https://github.com/abduns/qrcode/actions/workflows/ci.yml/badge.svg)](https://github.com/abduns/qrcode/actions/workflows/ci.yml)
[![Coverage](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/abduns/qrcode/main/coverage.json)](https://github.com/abduns/qrcode)
[![Version](https://img.shields.io/packagist/v/abduns/qrcode.svg)](https://packagist.org/packages/abduns/qrcode)
[![Downloads](https://img.shields.io/packagist/dt/abduns/qrcode.svg)](https://packagist.org/packages/abduns/qrcode)
[![License](https://img.shields.io/packagist/l/abduns/qrcode.svg)](LICENSE.md)

---

## Features

- Modern PHP support
- Lightweight and fast
- Typed API
- Framework agnostic
- Standards-oriented
- Extensible architecture
- Zero runtime dependencies
- Three renderers (SVG, PNG, Console)
- ISO/IEC 18004 compliant
- Payload helpers (URL, vCard, Event, etc.)

---

## Installation

```bash
composer require abduns/qrcode
```

---

## Quick Start

```php
use Dunn\QrCode\QrCode;
use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Renderer\Svg\SvgRenderer;

$qr = QrCode::create('https://example.com')
    ->errorCorrection(EccLevel::Quartile)
    ->build();

$svg = (new SvgRenderer(size: 300, margin: 4))->render($qr);
file_put_contents('qr.svg', $svg);
```

---

## Why This Package?

- Existing solutions are outdated
- Missing modern PHP features
- Poor developer experience
- No standards compliance
- Too framework-coupled

This package focuses on simplicity, interoperability, and modern developer ergonomics.

---

## Usage

### Basic Usage

```php
use Dunn\QrCode\QrCode;

// Payload helpers
QrCode::url('https://example.com')->build();
QrCode::text('hello')->build();
QrCode::phone('+14155550123')->build();
QrCode::sms('+14155550123', body: 'hi')->build();
QrCode::email('a@b.com', subject: 'hello', body: 'hi')->build();
QrCode::geo(37.7749, -122.4194, label: 'SF')->build();
```

### Advanced Usage

```php
use Dunn\QrCode\Renderer\Svg\SvgRenderer;
use Dunn\QrCode\Style\Color;
use Dunn\QrCode\Style\Logo;
use Dunn\QrCode\Style\ModuleShape\DotModule;
use Dunn\QrCode\Style\EyeStyle\CircleEyeOuter;
use Dunn\QrCode\Style\EyeStyle\SquareEyeInner;

$renderer = new SvgRenderer(
    size: 360,
    margin: 4,
    background: Color::hex('#fafafa'),

    // Data dots: round, dark navy.
    moduleShape: new DotModule(),
    dotColor: Color::hex('#264653'),

    // Marker outer ring: round, teal.
    eyeOuter: new CircleEyeOuter(),
    markerOuterColor: Color::hex('#2a9d8f'),

    // Marker inner pupil: square, terracotta.
    eyeInner: new SquareEyeInner(),
    markerInnerColor: Color::hex('#e76f51'),

    // Optional center logo
    logo: Logo::fromFile(__DIR__ . '/logo.png', sizeRatio: 0.18),
);

$svg = $renderer->render($qr);
```

### Configuration

All renderers are highly configurable directly via their constructors.

```php
use Dunn\QrCode\Renderer\Png\GdPngRenderer;
use Dunn\QrCode\Renderer\Console\ConsoleRenderer;

$png = (new GdPngRenderer(size: 300))->render($qr);
echo (new ConsoleRenderer(margin: 2))->render($qr);
```

---

## Standards / Specifications

- ISO/IEC 18004
- RFC 3966 (tel)
- RFC 6068 (mailto)
- RFC 5870 (geo)

References:

- https://www.thonky.com/qr-code-tutorial/

---

## Supported Features

| Feature | Support |
|---|---|
| SVG Renderer | ✅ |
| PNG Renderer | ✅ |
| Console Renderer | ✅ |
| Custom Colors & Gradients | ✅ |
| Custom Logos | ✅ |

---

## Compatibility

| Platform | Supported |
|---|---|
| PHP 8.2+ | ✅ |
| Laravel | ✅ (via laravel-qrcode) |

---

## Design Goals

- Developer experience first
- Predictable APIs
- Minimal dependencies
- Strong typing
- Extensibility
- Interoperability
- Byte-exact ISO/IEC 18004 compliance

---

## Architecture

- immutable objects
- independent encoding logic
- rendering adapters

---

## Performance

| Operation | Time |
|---|---|
| Parse & Encode | 2ms |
| Serialize SVG | 1ms |

---

## Testing

```bash
composer test
```

---

## Roadmap

- [ ] Kanji mode
- [ ] Micro QR
- [ ] Structured Append

---

## Contributing

Contributions, issues, and discussions are welcome.

---

## Security

If you discover security issues, please report them responsibly.

---

## License

MIT
