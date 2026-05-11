<?php

declare(strict_types=1);

namespace Dunn\QrCode;

use Dunn\QrCode\Encoder\Mode;
use Dunn\QrCode\Mask\MaskPattern;
use Dunn\QrCode\Matrix\Matrix;
use Dunn\QrCode\Payload\Email;
use Dunn\QrCode\Payload\Event;
use Dunn\QrCode\Payload\Geo;
use Dunn\QrCode\Payload\Phone;
use Dunn\QrCode\Payload\Sms;
use Dunn\QrCode\Payload\Text;
use Dunn\QrCode\Payload\Url;
use Dunn\QrCode\Payload\VCard;
use Dunn\QrCode\Payload\Wifi;
use Dunn\QrCode\Payload\WifiAuth;

/**
 * Immutable result of the QR Code generation pipeline. Holds the masked
 * matrix and the metadata renderers need.
 *
 * Use {@see QrCode::create()} for raw strings, or one of the typed factories
 * (url, text, phone, sms, email, geo, wifi, vCard, event) for semantic
 * payloads:
 *
 *     $qr = QrCode::url('https://example.com')
 *         ->errorCorrection(EccLevel::Quartile)
 *         ->build();
 */
final readonly class QrCode
{
    public function __construct(
        public Matrix $matrix,
        public int $version,
        public EccLevel $eccLevel,
        public Mode $mode,
        public MaskPattern $maskPattern,
    ) {
    }

    public static function create(string|\Stringable $data): Builder
    {
        return new Builder((string) $data);
    }

    public static function url(string $url): Builder
    {
        return self::create(new Url($url));
    }

    public static function text(string $text): Builder
    {
        return self::create(new Text($text));
    }

    public static function phone(string $number): Builder
    {
        return self::create(new Phone($number));
    }

    public static function sms(string $number, ?string $body = null, bool $useSmsUri = false): Builder
    {
        return self::create(new Sms($number, $body, $useSmsUri));
    }

    /**
     * @param list<string> $cc
     * @param list<string> $bcc
     */
    public static function email(
        string $to,
        ?string $subject = null,
        ?string $body = null,
        array $cc = [],
        array $bcc = [],
    ): Builder {
        return self::create(new Email($to, $subject, $body, $cc, $bcc));
    }

    public static function geo(float $latitude, float $longitude, ?string $label = null): Builder
    {
        return self::create(new Geo($latitude, $longitude, $label));
    }

    public static function wifi(
        string $ssid,
        ?string $password = null,
        WifiAuth $auth = WifiAuth::WPA,
        bool $hidden = false,
    ): Builder {
        return self::create(new Wifi($ssid, $password, $auth, $hidden));
    }

    public static function vCard(VCard $vcard): Builder
    {
        return self::create($vcard);
    }

    public static function event(Event $event): Builder
    {
        return self::create($event);
    }

    public function size(): int
    {
        return $this->matrix->size();
    }
}
