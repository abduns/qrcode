# abduns/qrcode

A from-scratch, framework-agnostic QR Code generator for PHP 8.2+ with **zero
required runtime dependencies**. Implements ISO/IEC 18004 byte-exact: data
encoding, Reed–Solomon error correction, block interleaving, all 8 mask
patterns with spec-correct penalty scoring, and three renderers (SVG, PNG via
ext-gd, monospace console).

For Laravel apps, see the bridge package
[`abduns/laravel-qrcode`](../qr-code-laravel).

## Install

```bash
composer require abduns/qrcode
```

PHP 8.2+. Zero runtime dependencies. `ext-gd` is needed only for PNG output.

## API stability

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

## Hello world

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

## Customization

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

**Available shapes:**

| Region | Default | Alternatives |
|---|---|---|
| `moduleShape` (data) | `SquareModule` | `DotModule`, `RoundedModule` |
| `eyeOuter` (marker border) | `SquareEyeOuter` | `CircleEyeOuter`, `RoundedEyeOuter` |
| `eyeInner` (marker center) | `SquareEyeInner` | `CircleEyeInner`, `RoundedEyeInner` |

Mix and match — e.g. `CircleEyeOuter` + `SquareEyeInner` gives a round border
around a square pupil. `RoundedModule` is **neighbour-aware**: corners are
rounded only when both adjacent neighbours are absent, so adjacent modules
merge into pills, L-shapes, and larger blobs as the data dictates.

**Colours and gradients:** every paint parameter accepts a `Color`, a
`Gradient`, or a hex string. Unspecified per-region paints fall back to the
`foreground` paint.

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

**Logo:** `Logo` accepts raw bytes + MIME or loads from a file via
`Logo::fromFile($path, sizeRatio)`. Supports SVG, PNG, JPEG, GIF. The
renderer validates the logo size against the QR's error-correction level
and throws `InvalidConfigurationException` if oversized. Safe maximum
linear ratios:

| ECC | Max ratio | Recommended ratio |
|---|---|---|
| Low | 0.26 | ≤ 0.15 |
| Medium | 0.38 | ≤ 0.20 |
| Quartile | 0.50 | ≤ 0.25 |
| High | 0.54 | ≤ 0.30 |

## Examples

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

## Builder options

```php
QrCode::create($data)
    ->errorCorrection(EccLevel::Low | Medium | Quartile | High)
    ->forceVersion(1..40)
    ->forceMode(Mode::Numeric | Alphanumeric | Byte)
    ->build();
```

Defaults: `EccLevel::Medium`, auto-version (smallest that fits), auto-mode
(smallest single mode that fits).

Out of v1.0.x scope (tracked for v1.x or later): Kanji mode, Micro QR (M1–M4), ECI, optimal mixed-mode segmentation, Structured Append, gradients in the PNG renderer.

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

## Development

```bash
composer install
composer test     # pest
composer stan     # phpstan level 8
composer lint     # php-cs-fixer dry-run
composer ci       # all three
```

## License

MIT
