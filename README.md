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

Swap in alternative module shapes and finder-pattern (eye) styles via the SVG
renderer:

```php
use Dunn\QrCode\Renderer\Svg\SvgRenderer;
use Dunn\QrCode\Style\Color;
use Dunn\QrCode\Style\ModuleShape\DotModule;
use Dunn\QrCode\Style\EyeStyle\CircleEye;

$renderer = new SvgRenderer(
    size: 300,
    margin: 4,
    foreground: Color::hex('#1a1a2e')->toCss(),
    background: Color::hex('#fafafa')->toCss(),
    moduleShape: new DotModule(),
    eyeStyle: new CircleEye(),
);
```

Available shapes:
- `Style\ModuleShape\SquareModule` (default), `DotModule`
- `Style\EyeStyle\SquareEye` (default), `CircleEye`

`Color` is an immutable RGBA value object with `Color::hex()`, `Color::rgb()`,
`Color::rgba()`, and named factories `Color::black()` / `Color::white()`.

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

Out of v0.1.0 scope (planned for v1.x): Kanji mode, Micro QR (M1–M4), ECI,
optimal mixed-mode segmentation, Structured Append, gradients, logo overlay,
neighbour-aware rounded shapes.

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
