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

- **`SvgRenderer`** (zero deps) — emits a single `<svg>` element. Configurable
  size, margin, foreground, background. Typical output: <5 KB.
- **`GdPngRenderer`** (requires `ext-gd`) — pixel-perfect raster with integer
  module sizing for crisp edges.
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

| Region | Default | Alternative |
|---|---|---|
| `moduleShape` (data) | `SquareModule` | `DotModule` |
| `eyeOuter` (marker border) | `SquareEyeOuter` | `CircleEyeOuter` |
| `eyeInner` (marker center) | `SquareEyeInner` | `CircleEyeInner` |

Mix and match — e.g. `CircleEyeOuter` + `SquareEyeInner` gives a round border
around a square pupil.

**Colours:** every colour parameter accepts a `Color` instance or a hex
string. `Color` provides `Color::hex()`, `Color::rgb()`, `Color::rgba()`,
plus `Color::black()` / `Color::white()`. Unspecified per-region colours
fall back to the `foreground`.

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

Out of v0.2.x scope (planned for v0.3 / v1.x): Kanji mode, Micro QR (M1–M4),
ECI, optimal mixed-mode segmentation, Structured Append, gradients,
neighbour-aware rounded shapes, per-region colours in the PNG renderer.

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
