# Changelog

All notable changes to `abduns/qrcode` are documented here. The format is
based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this
project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html)
once it reaches 1.0.0. Pre-1.0 minor bumps may carry breaking changes.

## [1.1.0] — 2026-05-11

Additive release — no breaking changes. Adds first-class semantic payload
builders so callers no longer need to hand-format `WIFI:`, vCard, `mailto:`,
`geo:`, etc. wire strings.

### Added
- `Dunn\QrCode\Payload\Url` — URL / link payload.
- `Dunn\QrCode\Payload\Text` — plain-text payload (pass-through, included for
  API symmetry).
- `Dunn\QrCode\Payload\Phone` — `tel:` URI per RFC 3966; normalises
  formatting characters and validates the result.
- `Dunn\QrCode\Payload\Sms` — `SMSTO:<number>:<body>` (default, best scanner
  compatibility) or `sms:<number>?body=...` via `useSmsUri: true`.
- `Dunn\QrCode\Payload\Email` — `mailto:` URI per RFC 6068 with
  `subject` / `body` / `cc` / `bcc` support.
- `Dunn\QrCode\Payload\Geo` — `geo:` URI per RFC 5870 with optional `?q=`
  label and `[-90, 90]` / `[-180, 180]` validation.
- `Dunn\QrCode\Payload\Wifi` + `Dunn\QrCode\Payload\WifiAuth` — Wi-Fi Alliance
  `WIFI:` payload with proper escaping of `\ ; , : "` in SSID / password.
- `Dunn\QrCode\Payload\VCard` — vCard 3.0 (RFC 2426) with fluent `with* /
  add*` builders and RFC 6350 §3.4 escaping.
- `Dunn\QrCode\Payload\Event` — iCalendar 2.0 VEVENT (RFC 5545); auto-generates
  `UID` and `DTSTAMP`, serialises timestamps in UTC.
- `Dunn\QrCode\Exception\PayloadException` — single exception type for
  payload validation failures, extends `QrCodeException`.
- `QrCode::create()` now accepts `string|\Stringable`, so any payload value
  object can be handed to it directly.
- Nine static factories on `QrCode`: `url`, `text`, `phone`, `sms`, `email`,
  `geo`, `wifi`, `vCard`, `event` — each returns the existing `Builder`.

### Notes
- Backward-compatible: `QrCode::create(string)` keeps its existing behaviour.
- 60 new tests, all under `tests/Unit/Payload/`. Total: 295 tests.

## [1.0.0] — 2026-05-11

First stable release. The public surface listed in the README's "API
stability" section is now semver-protected — v1.x will only break on a
major bump.

### Added
- `Style\EyeStyle\RoundedEyeOuter` + `Style\EyeStyle\RoundedEyeInner` —
  rounded-square outer ring and rounded inner pupil that complement
  `RoundedModule`.
- `## Examples` section in the README with four copy-pasteable presets:
  classic, dotted, rounded-with-gradient, branded-with-logo.
- `MIGRATION.md` covering v0.1→v0.2 (`EyeStyle` split), v0.2→v0.3
  (`ModuleShape::svgPath` neighbours param), and v0.3→v1.0 (no-op).
- `## API stability` section in the README committing the public surface.

### Notes
- `GdPngRenderer` does not draw the rounded-square outline natively; it
  falls back to the SquareEye* renderer for `RoundedEyeOuter` /
  `RoundedEyeInner` (filled rect + hole). Use `SvgRenderer` for the
  rounded-eye visual.

### Migration
No code changes required vs v0.3.0. See `MIGRATION.md` for the full
v0.1 → v1.0 history if you're upgrading from older versions.

## [0.3.0] — 2026-05-11

### Added
- `Style\ModuleShape\RoundedModule` — neighbour-aware rounded module. Corners
  are rounded only when both adjacent neighbours are absent, so adjacent
  modules merge into pills, L-shapes, and larger blobs. With `r = 0.5`,
  isolated modules render as full circles.
- `Style\ModuleShape\ModuleNeighbours` readonly value object (top/right/
  bottom/left + an `isolated()` factory) passed to every `ModuleShape`.
- `Style\Gradient\Gradient` interface with `LinearGradient` + `RadialGradient`
  implementations and a `GradientStop` value object (offset 0..1 + `Color`,
  with `stop-opacity` emitted for RGBA stops).
- `SvgRenderer` accepts `Color|Gradient|string` for every paint parameter
  (`foreground`, `background`, `dotColor`, `markerOuterColor`,
  `markerInnerColor`). Gradients produce a `<defs>` block with unique
  per-render ids (`qr-{6-hex}-{region}`) and `fill="url(#…)"` references.
- `GdPngRenderer` now mirrors `SvgRenderer`'s customisation surface:
  `Color|string` everywhere, `dotColor`/`markerOuterColor`/`markerInnerColor`,
  `?ModuleShape`/`?EyeOuter`/`?EyeInner`/`?Logo` constructor args. Raster
  logos are composited via `imagecreatefromstring` + `imagecopyresampled`.
  Eye shapes use `imagefilledellipse` for circles and filled rects for
  squares.

### Changed
- `ModuleShape::svgPath()` signature is now
  `svgPath(int $x, int $y, ModuleNeighbours $neighbours): string`.
  `SquareModule` and `DotModule` continue to ignore neighbours — output is
  byte-identical to v0.2.

### Removed (BREAKING)
- The pre-v0.3 `ModuleShape::svgPath(int, int)` two-arg signature.

### Migration

Custom `ModuleShape` implementations need to widen the signature:

```php
// v0.2.0
public function svgPath(int $x, int $y): string { /* … */ }

// v0.3.0
public function svgPath(int $x, int $y, ModuleNeighbours $neighbours): string { /* … */ }
```

If your shape is context-free (square, dot, fixed glyph), ignore the new
parameter. Neighbour-aware shapes consult it to decide which corners to
round or edges to cut.

## [0.2.0] — 2026-05-11

### Added
- `Style\EyeStyle\EyeOuter` interface for rendering the outer 7×7 ring of a
  finder pattern. Implementations: `SquareEyeOuter` (default), `CircleEyeOuter`.
- `Style\EyeStyle\EyeInner` interface for rendering the inner 3×3 pupil.
  Implementations: `SquareEyeInner` (default), `CircleEyeInner`.
- `Style\Logo` readonly value object — raw bytes + MIME (SVG/PNG/JPEG/GIF)
  + `sizeRatio` (0..0.55) + `clearBackground`. Static `Logo::fromFile()`
  loader with MIME auto-detection. `dataUri()` for inline embedding.
- `SvgRenderer` new constructor parameters:
  - `dotColor`, `markerOuterColor`, `markerInnerColor` (each `Color|string|null`,
    falling back to `foreground` when null)
  - `eyeOuter`, `eyeInner` (replacing the old combined `eyeStyle`)
  - `logo` (a `Logo` instance)
- `SvgRenderer` accepts `Color|string` for every colour parameter and
  normalises internally.
- Logo overlays are validated against the QR's ECC level at render time:
  L 0.26, M 0.38, Q 0.50, H 0.54 maximum linear ratio. Oversized logos
  throw `InvalidConfigurationException`.

### Changed
- `SvgRenderer` output now contains **three independent `<path>` elements**
  (data dots, marker outer, marker inner), each with its own `fill`
  attribute, instead of one combined path.
- `shape-rendering` hint upgrades to `geometricPrecision` when any of the
  three region strategies requests it.
- `fill-rule="evenodd"` is applied to every region path so eye holes render
  correctly.

### Removed (BREAKING)
- `Style\EyeStyle\EyeStyle` interface.
- `Style\EyeStyle\SquareEye` class.
- `Style\EyeStyle\CircleEye` class.
- `SvgRenderer`'s `eyeStyle:` constructor parameter.

### Migration

```php
// v0.1.0
use Dunn\QrCode\Style\EyeStyle\CircleEye;

new SvgRenderer(eyeStyle: new CircleEye());
```

```php
// v0.2.0
use Dunn\QrCode\Style\EyeStyle\CircleEyeOuter;
use Dunn\QrCode\Style\EyeStyle\CircleEyeInner;

new SvgRenderer(
    eyeOuter: new CircleEyeOuter(),
    eyeInner: new CircleEyeInner(),
);
```

You can now mix the two regions (e.g. `CircleEyeOuter` + `SquareEyeInner`)
and give them independent colours via `markerOuterColor` and
`markerInnerColor`.

## [0.1.0] — 2026-05-11

Initial release.

- Versions 1..40, ECC L/M/Q/H, modes Numeric/Alphanumeric/Byte.
- GF(256) + Reed–Solomon error correction.
- All function patterns (finder, separator, timing, dark module, alignment,
  format info BCH 15,5, version info BCH 18,6).
- All 8 mask patterns with N1/N2/N3/N4 penalty scoring.
- Three renderers: `SvgRenderer` (zero deps), `GdPngRenderer` (ext-gd),
  `ConsoleRenderer`.
- Customization v1: `Color` value object, `ModuleShape` interface
  (`SquareModule`/`DotModule`), `EyeStyle` interface (`SquareEye`/`CircleEye`).
