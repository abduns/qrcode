# Migration guide

## v0.3.0 → v1.0.0

No code changes required. v1.0.0 is API-identical to v0.3.0; the major
bump signals that the public surface is now semver-protected. See the
"API stability" section in the README for the committed surface.

Added in v1.0.0: `RoundedEyeOuter`, `RoundedEyeInner`, examples gallery,
this migration guide.

## v0.2.0 → v0.3.0

`ModuleShape::svgPath()` signature widened to accept a `ModuleNeighbours`
third parameter. Custom shapes need a one-line signature update:

```php
// v0.2.0
public function svgPath(int $x, int $y): string { /* … */ }

// v0.3.0+
public function svgPath(int $x, int $y, ModuleNeighbours $neighbours): string { /* … */ }
```

Context-free shapes (square, dot, fixed glyphs) ignore the new parameter.
Neighbour-aware shapes (rounded, smooth-join) use it to decide which
corners or edges to round.

Bundled shapes (`SquareModule`, `DotModule`, `RoundedModule`) updated
automatically — no consumer action needed for those.

## v0.1.0 → v0.2.0

The combined `EyeStyle` interface plus `SquareEye` / `CircleEye` classes
were replaced by `EyeOuter` + `EyeInner` pairs so the outer ring and inner
pupil can be styled and coloured independently.

```php
// v0.1.0
new SvgRenderer(eyeStyle: new CircleEye());

// v0.2.0+
new SvgRenderer(
    eyeOuter: new CircleEyeOuter(),
    eyeInner: new CircleEyeInner(),
);
```
