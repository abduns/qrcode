<?php

declare(strict_types=1);

use Dunn\QrCode\Exception\PayloadException;
use Dunn\QrCode\Payload\Wifi;
use Dunn\QrCode\Payload\WifiAuth;

it('produces a WIFI: payload with WPA auth by default', function (): void {
    expect((string) new Wifi('MyNet', 'secret'))
        ->toBe('WIFI:T:WPA;S:MyNet;P:secret;;');
});

it('omits the password field for an open network', function (): void {
    expect((string) new Wifi('OpenNet', auth: WifiAuth::NoPass))
        ->toBe('WIFI:T:nopass;S:OpenNet;;');
});

it('emits H:true for hidden networks', function (): void {
    expect((string) new Wifi('MyNet', 'pwd', WifiAuth::WPA, hidden: true))
        ->toBe('WIFI:T:WPA;S:MyNet;P:pwd;H:true;;');
});

it('escapes the special characters \\ ; , : " in SSID and password', function (): void {
    $payload = (string) new Wifi('My;Net,"x"', 'p\\:ss');
    expect($payload)->toBe('WIFI:T:WPA;S:My\\;Net\\,\\"x\\";P:p\\\\\\:ss;;');
});

it('throws on empty SSID', function (): void {
    expect(fn () => new Wifi(''))->toThrow(PayloadException::class, 'ssid');
});
