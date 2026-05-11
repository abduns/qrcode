# abduns/qrcode

A from-scratch, framework-agnostic QR Code generator for PHP 8.2+ with **zero
required runtime dependencies**. Implements ISO/IEC 18004 byte-exact: data
encoding, Reed–Solomon error correction, block interleaving, all 8 mask
patterns with spec-correct penalty scoring, and three renderers (SVG, PNG via
ext-gd, monospace console).

[![Latest version](https://img.shields.io/packagist/v/abduns/qrcode.svg?style=flat-square)](https://packagist.org/packages/abduns/qrcode)
[![PHP version](https://img.shields.io/packagist/php-v/abduns/qrcode.svg?style=flat-square)](https://packagist.org/packages/abduns/qrcode)
[![CI](https://github.com/abduns/qrcode/actions/workflows/ci.yml/badge.svg)](https://github.com/abduns/qrcode/actions/workflows/ci.yml)
[![Downloads](https://img.shields.io/packagist/dt/abduns/qrcode.svg?style=flat-square)](https://packagist.org/packages/abduns/qrcode)
[![License](https://img.shields.io/packagist/l/abduns/qrcode.svg?style=flat-square)](LICENSE.md)

For Laravel apps, see the bridge package
[`abduns/laravel-qrcode`](../qr-code-laravel).

## Features

- **Zero runtime dependencies** — pure PHP, no Composer requires beyond the language itself.
- **ISO/IEC 18004 compliant** — data encoder, Reed–Solomon ECC, block interleaver, and all 8 mask patterns verified byte-exact against the spec's worked examples.
- **Three renderers** — `SvgRenderer` (zero deps), `GdPngRenderer` (requires `ext-gd`), and `ConsoleRenderer` for terminal output and debugging.
- **Rich styling** — three module shapes (`Square`, `Dot`, neighbour-aware `Rounded`) and three eye styles (`Square`, `Circle`, `Rounded`), mixable per region.
- **Gradients** — `LinearGradient` and `RadialGradient` with multi-stop RGBA on any paint region (SVG only).
- **Logo embedding** — drop SVG / PNG / JPEG / GIF logos into the centre, with size validated against the QR's error-correction budget.
- **Typed payload helpers** — first-class builders for URL, text, vCard, WiFi, mailto, SMS, tel, geo and iCalendar events; no need to hand-format wire strings.
- **Stable, SemVer-backed API** — v1.x commits the public surface; internals are free to evolve.
- **Quality bar** — PHPStan level 8, 295 Pest tests, 71k assertions, strict-types throughout.

## Table of contents

- [Install](#install)
- [Quick start](#quick-start)
- [Payload helpers](#payload-helpers)
- [Renderers](#renderers)
- [Styling](#styling)
- [Examples gallery](#examples-gallery)
- [Builder reference](#builder-reference)
- [Error handling](#error-handling)
- [Limitations](#limitations)
- [Spec correctness](#spec-correctness)
- [Versioning](#versioning)
- [Development](#development)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Install

```bash
composer require abduns/qrcode
```

PHP 8.2+. Zero runtime dependencies. `ext-gd` is needed only if you use `GdPngRenderer` for PNG output — SVG and console renderers have no extension requirements.

## Quick start

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

The builder is immutable — each setter returns a new instance. The result is a
read-only `QrCode` value object exposing `matrix`, `version`, `eccLevel`,
`mode`, and `maskPattern`.

## Payload helpers

The encoder is data-agnostic, but most QR codes carry one of a handful of
well-known formats (URL, vCard, WiFi join, mailto, …). `Dunn\QrCode\Payload`
ships immutable value objects for each and `QrCode` exposes a matching static
factory that returns a configured `Builder`. The wire format is built for you
according to the relevant RFC / convention, with proper escaping.

| Type                | Factory                                            | Wire format             |
|---------------------|----------------------------------------------------|-------------------------|
| URL / Link          | `QrCode::url($url)`                                | passes the URL through  |
| Plain text          | `QrCode::text($text)`                              | passes the text through |
| Phone (`tel:`)      | `QrCode::phone($number)`                           | RFC 3966                |
| SMS                 | `QrCode::sms($number, body: ...)`                  | `SMSTO:` (or `sms:` URI)|
| Email (`mailto:`)   | `QrCode::email($to, subject:, body:, cc:, bcc:)`   | RFC 6068                |
| Geo                 | `QrCode::geo($lat, $lng, label: ...)`              | RFC 5870                |
| WiFi                | `QrCode::wifi($ssid, $pwd, $auth, $hidden)`        | Wi-Fi Alliance `WIFI:`  |
| vCard               | `QrCode::vCard($card)`                             | RFC 2426 (vCard 3.0)    |
| Calendar event      | `QrCode::event($event)`                            | RFC 5545 (VEVENT)       |

Each factory returns the same `Builder` you get from `QrCode::create()`, so
the rest of the pipeline (error correction, mode forcing, rendering) is
unchanged:

```php
use Dunn\QrCode\QrCode;
use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Payload\VCard;
use Dunn\QrCode\Payload\Event;
use Dunn\QrCode\Payload\WifiAuth;

// Link / text / phone / sms / email / geo — one-liners
QrCode::url('https://example.com')->build();
QrCode::text('hello')->build();
QrCode::phone('+14155550123')->build();
QrCode::sms('+14155550123', body: 'hi')->build();
QrCode::email('a@b.com', subject: 'hello', body: 'hi')->build();
QrCode::geo(37.7749, -122.4194, label: 'SF')->build();

// WiFi join (defaults to WPA)
QrCode::wifi('MyNet', password: 'secret', auth: WifiAuth::WPA)
    ->errorCorrection(EccLevel::Quartile)
    ->build();

// vCard — fluent value object, then hand to QrCode::vCard()
$card = VCard::make('John Doe')
    ->withOrg('Acme')
    ->withTitle('Engineer')
    ->addPhone('+14155550123', VCard::TYPE_WORK)
    ->addEmail('john@acme.com')
    ->withUrl('https://acme.com');

QrCode::vCard($card)->build();

// Calendar event (iCalendar 2.0 VEVENT)
$event = Event::make('Launch party')
    ->from(new DateTimeImmutable('2026-06-01 18:00', new DateTimeZone('UTC')))
    ->to(new DateTimeImmutable('2026-06-01 22:00', new DateTimeZone('UTC')))
    ->at('HQ')
    ->withDescription('See you there');

QrCode::event($event)->build();
```

All payload value objects implement `\Stringable`, so you can also hand them
to `QrCode::create()` directly: `QrCode::create($card)->build()` works
identically to `QrCode::vCard($card)`. Invalid inputs (empty SSID, latitude
out of range, end-before-start event, …) throw `Dunn\QrCode\Exception\PayloadException`.

## Renderers

All renderers share the `Dunn\QrCode\Renderer\Renderer` interface:

```php
interface Renderer
{
    public function render(QrCode $qr): string;
    public function mimeType(): string;
}
```

- **`SvgRenderer`** (zero deps) — emits an `<svg>` element with three
  independent paths (data dots, marker outer ring, marker inner pupil) plus
  an optional `<image>` logo. Supports per-region colours, gradients
  (`LinearGradient`, `RadialGradient`), and the full shape catalogue.
  Typical output: <5 KB without gradients/logo.
- **`GdPngRenderer`** (requires `ext-gd`) — pixel-perfect raster with the
  same customisation surface as the SVG renderer except gradients (which
  fall back to flat colours). Logos are decoded via `imagecreatefromstring`
  so PNG/JPEG/GIF logos work; SVG logos require the SVG renderer.
- **`ConsoleRenderer`** — Unicode block characters; useful for debugging.

```php
use Dunn\QrCode\Renderer\Png\GdPngRenderer;
use Dunn\QrCode\Renderer\Console\ConsoleRenderer;

$png = (new GdPngRenderer(size: 300))->render($qr);
echo (new ConsoleRenderer(margin: 2))->render($qr);
```

## Styling

The SVG renderer paints three regions independently — **data dots**, **marker
outer ring**, **marker inner pupil** — plus an optional center logo. Each
region can have its own shape and colour:

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

    // Optional center logo (validated against the QR's ECC level).
    logo: Logo::fromFile(__DIR__ . '/logo.png', sizeRatio: 0.18),
);
```

### Shapes

| Region | Default | Alternatives |
|---|---|---|
| `moduleShape` (data) | `SquareModule` | `DotModule`, `RoundedModule` |
| `eyeOuter` (marker border) | `SquareEyeOuter` | `CircleEyeOuter`, `RoundedEyeOuter` |
| `eyeInner` (marker center) | `SquareEyeInner` | `CircleEyeInner`, `RoundedEyeInner` |

Mix and match — e.g. `CircleEyeOuter` + `SquareEyeInner` gives a round border
around a square pupil. `RoundedModule` is **neighbour-aware**: corners are
rounded only when both adjacent neighbours are absent, so adjacent modules
merge into pills, L-shapes, and larger blobs as the data dictates.

### Colours and gradients

Every paint parameter accepts a `Color`, a `Gradient`, or a hex string.
Unspecified per-region paints fall back to the `foreground` paint.

```php
use Dunn\QrCode\Style\Gradient\{LinearGradient, RadialGradient, GradientStop};

new SvgRenderer(
    dotColor: new LinearGradient([
        new GradientStop(0.0, Color::hex('#264653')),
        new GradientStop(1.0, Color::hex('#2a9d8f')),
    ]),
    markerInnerColor: new RadialGradient([
        new GradientStop(0.0, Color::hex('#f4a261')),
        new GradientStop(1.0, Color::hex('#e76f51')),
    ]),
);
```

`Color` provides `Color::hex()`, `Color::rgb()`, `Color::rgba()`, plus named
factories `Color::black()` / `Color::white()`. Gradients with RGBA stops emit
`stop-opacity` so semi-transparent gradients work.

### Logos

`Logo` accepts raw bytes + MIME or loads from a file via
`Logo::fromFile($path, sizeRatio)`. Supports SVG, PNG, JPEG, GIF. The renderer
validates the logo size against the QR's error-correction level and throws
`InvalidConfigurationException` if oversized. Safe maximum linear ratios:

| ECC | Max ratio | Recommended ratio |
|---|---|---|
| Low | 0.26 | ≤ 0.15 |
| Medium | 0.38 | ≤ 0.20 |
| Quartile | 0.50 | ≤ 0.25 |
| High | 0.54 | ≤ 0.30 |

## Examples gallery

Four copy-pasteable presets covering the customization surface. All share
the same QrCode build:

```php
$qr = QrCode::create($data)->errorCorrection(EccLevel::Quartile)->build();
```

**Classic** — plain black-and-white square modules with square markers:

```php
$svg = (new SvgRenderer())->render($qr);
```

**Dotted** — round modules, round markers, single brand colour:

```php
$svg = (new SvgRenderer(
    moduleShape: new DotModule(),
    eyeOuter: new CircleEyeOuter(),
    eyeInner: new CircleEyeInner(),
    foreground: Color::hex('#264653'),
))->render($qr);
```

**Rounded with gradient** — neighbour-aware rounded modules, rounded
markers, linear-gradient dots:

```php
$svg = (new SvgRenderer(
    moduleShape: new RoundedModule(),
    eyeOuter: new RoundedEyeOuter(),
    eyeInner: new RoundedEyeInner(),
    dotColor: new LinearGradient([
        new GradientStop(0.0, Color::hex('#264653')),
        new GradientStop(1.0, Color::hex('#2a9d8f')),
    ]),
))->render($qr);
```

**Branded** — per-region colours plus a centre logo:

```php
$svg = (new SvgRenderer(
    moduleShape: new DotModule(),
    eyeOuter: new CircleEyeOuter(),
    eyeInner: new SquareEyeInner(),
    dotColor: Color::hex('#264653'),
    markerOuterColor: Color::hex('#2a9d8f'),
    markerInnerColor: Color::hex('#e76f51'),
    logo: Logo::fromFile(__DIR__ . '/logo.png', sizeRatio: 0.18),
))->render($qr);
```

## Builder reference

```php
QrCode::create($data)
    ->errorCorrection(EccLevel::Low | Medium | Quartile | High)
    ->forceVersion(1..40)
    ->forceMode(Mode::Numeric | Alphanumeric | Byte)
    ->build();
```

Defaults: `EccLevel::Medium`, auto-version (smallest that fits), auto-mode
(smallest single mode that fits).

## Error handling

All exceptions extend `Dunn\QrCode\Exception\QrCodeException` (which extends
`RuntimeException`), so a single catch can isolate library failures:

- **`DataTooLongException`** — the input cannot fit into a v40 symbol at the
  chosen ECC level. Lower the ECC level, shorten the data, or pick a denser
  mode (e.g. numeric data with `forceMode(Mode::Numeric)`).
- **`InvalidConfigurationException`** — a renderer was misconfigured:
  `ext-gd` is missing for `GdPngRenderer`, the logo file path doesn't exist,
  the logo MIME is unsupported, or the logo `sizeRatio` exceeds the ECC
  budget (see the ratio table above).

```php
use Dunn\QrCode\Exception\DataTooLongException;
use Dunn\QrCode\Exception\InvalidConfigurationException;

try {
    $qr = QrCode::create($input)->errorCorrection(EccLevel::High)->build();
    $svg = (new SvgRenderer(logo: Logo::fromFile($logoPath)))->render($qr);
} catch (DataTooLongException $e) {
    // Fall back to lower ECC, or chunk the payload.
} catch (InvalidConfigurationException $e) {
    // Bad renderer/logo config — log $e->getMessage() and fix the input.
}
```

## Limitations

Known v1.0.x boundaries — tracked for v1.x minor releases or a future v2:

- **No Kanji mode** — `Mode::Kanji` is declared in the enum but not implemented.
- **No Micro QR** (M1–M4) — only full-size QR (versions 1–40).
- **No ECI** (Extended Channel Interpretation) — payload is interpreted as UTF-8 bytes.
- **No optimal mixed-mode segmentation** — the encoder picks a single best mode for the whole payload rather than mixing numeric / alphanumeric / byte segments.
- **No Structured Append** — multi-symbol chaining is not supported.
- **No gradients in `GdPngRenderer`** — gradient paints fall back to the first stop's flat colour. Use `SvgRenderer` for gradient output.
- **SVG logos require `SvgRenderer`** — the GD path decodes logos via `imagecreatefromstring`, which doesn't support SVG.

## Spec correctness

Every byte-level transformation is verified against ISO/IEC 18004 Annex I
worked examples or Thonky's canonical tutorial:

- GF(256) multiply, divide, log/antilog round-trip
- Reed–Solomon: V1-M "HELLO WORLD" (16 data → 10 ECC) and V5-Q (15 → 18) byte-exact
- Generator polynomials of degree 7, 10, 18 match Annex A coefficients
- Data encoder: V1-M "HELLO WORLD" → canonical 16 data codewords
- Block interleaver: V1-M produces the canonical 26-byte (data + ECC) stream
- All 160 (version, ECC) entries in the block-layout table sum to the
  per-version capacity table
- BCH(15, 5) format info: L/0, M/5, H/7 match Thonky's published table
- BCH(18, 6) version info: V7 = 0x07C94, V10 = 0x0A4D3, V40 = 0x28C69

## Versioning

`abduns/qrcode` follows [Semantic Versioning](https://semver.org/) from v1.0
onwards. The following surface is committed — v1.x will only break on a
major (v2.0) bump:

- `Dunn\QrCode\QrCode`, `Builder`, `EccLevel`
- `Dunn\QrCode\Encoder\Mode`
- `Dunn\QrCode\Renderer\*` (interfaces + bundled implementations)
- `Dunn\QrCode\Style\*` (Color, Logo, all ModuleShape / EyeStyle / Gradient interfaces and bundled implementations)
- `Dunn\QrCode\Exception\*`

Internal classes (`Math\*`, `ErrorCorrection\*`, `Matrix\*`, `Mask\*`,
`Tables\*`, and `Encoder\*` excluding `Mode`) may change between minor
versions and are not part of the SemVer contract.

See [CHANGELOG.md](CHANGELOG.md) for release notes and [MIGRATION.md](MIGRATION.md) for upgrade paths across pre-1.0 releases.

## Development

```bash
composer install
composer test     # pest
composer stan     # phpstan level 8
composer lint     # php-cs-fixer dry-run
composer ci       # all three
```

CI runs the same `composer ci` matrix on PHP 8.2, 8.3, and 8.4 — see
[`.github/workflows/ci.yml`](.github/workflows/ci.yml).

## Contributing

Issues and pull requests are welcome on [GitHub](https://github.com/abduns/qrcode/issues).

Before opening a PR:

1. **Open an issue first** for non-trivial changes so we can agree on the approach.
2. **Run `composer ci`** locally — Pest, PHPStan level 8, and php-cs-fixer must all pass.
3. **Cover new code with Pest tests.** The existing suite uses Pest 3; match the style.
4. **Follow PSR-12** plus the `:risky` ruleset in `.php-cs-fixer.php`. `declare(strict_types=1)` is required in every file.
5. **No new runtime dependencies** without prior discussion — zero-deps is a core design constraint.
6. **Spec-correctness changes** to encoding, ECC, or matrix construction should reference the ISO/IEC 18004 clause or Annex I example they're verified against.

## Security

If you discover a security vulnerability, please report it privately via
[GitHub Security Advisories](https://github.com/abduns/qrcode/security/advisories/new)
rather than opening a public issue. We'll acknowledge receipt within a few
days and coordinate disclosure.

## Credits

- ISO/IEC 18004:2015 — the canonical QR Code specification this implementation is verified against.
- Thonky's [QR Code Tutorial](https://www.thonky.com/qr-code-tutorial/) — worked examples used as a secondary cross-check during development.

## License

Released under the [MIT License](LICENSE.md). Copyright © 2026 Abduns.
