# abduns/qrcode

A from-scratch, framework-agnostic QR Code generator for PHP 8.2+ with **zero
required runtime dependencies**.

## Status

Pre-release. Under active development against ISO/IEC 18004. The public API
shown below is the v1 target — not all of it is wired up yet.

## Install

```bash
composer require abduns/qrcode
```

For Laravel, use the bridge package:

```bash
composer require abduns/laravel-qrcode
```

## Usage (target API)

```php
use Dunn\QrCode\QrCode;
use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Renderer\Svg\SvgRenderer;
use Dunn\QrCode\Style\Color;
use Dunn\QrCode\Style\ModuleShape\DotModule;
use Dunn\QrCode\Style\EyeStyle\RoundedEye;

$qr = QrCode::create('https://example.com')
    ->errorCorrection(EccLevel::Quartile)
    ->size(300)
    ->margin(4)
    ->foreground(Color::hex('#1a1a2e'))
    ->moduleShape(new DotModule())
    ->eyeStyle(new RoundedEye())
    ->build();

file_put_contents('out.svg', (new SvgRenderer())->render($qr));
```

## Development

```bash
composer install
composer test    # pest
composer stan    # phpstan level 8
composer lint    # php-cs-fixer dry-run
composer ci      # all of the above
```

## License

MIT
