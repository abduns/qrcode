<?php

declare(strict_types=1);

namespace Dunn\QrCode\Payload;

/**
 * WiFi network authentication scheme used in the WIFI:T:... payload.
 */
enum WifiAuth: string
{
    case WPA = 'WPA';
    case WEP = 'WEP';
    case NoPass = 'nopass';
}
