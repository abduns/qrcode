# Changelog

All notable changes to `abduns/qrcode` are documented here. The format is
based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this
project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html)
once it reaches 1.0.0. Pre-1.0 minor bumps may carry breaking changes.

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
