<?php

declare(strict_types=1);

namespace Dunn\QrCode\Payload;

use Dunn\QrCode\Exception\PayloadException;

/**
 * WIFI:T:<auth>;S:<ssid>;P:<password>;H:<hidden>;; payload (Wi-Fi Alliance
 * informational convention; supported by iOS, Android and most scanners).
 *
 * The characters \ ; , : " inside the SSID and password are escaped with a
 * leading backslash per the format spec.
 */
final readonly class Wifi implements \Stringable
{
    public function __construct(
        public string $ssid,
        public ?string $password = null,
        public WifiAuth $auth = WifiAuth::WPA,
        public bool $hidden = false,
    ) {
        if ($ssid === '') {
            throw PayloadException::emptyValue('ssid');
        }
    }

    public function __toString(): string
    {
        $parts = [
            'T:' . $this->auth->value,
            'S:' . self::escape($this->ssid),
        ];

        if ($this->auth !== WifiAuth::NoPass && $this->password !== null && $this->password !== '') {
            $parts[] = 'P:' . self::escape($this->password);
        }

        if ($this->hidden) {
            $parts[] = 'H:true';
        }

        return 'WIFI:' . \implode(';', $parts) . ';;';
    }

    private static function escape(string $value): string
    {
        return \addcslashes($value, '\\;,:"');
    }
}
