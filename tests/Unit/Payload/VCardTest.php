<?php

declare(strict_types=1);

use Dunn\QrCode\Exception\PayloadException;
use Dunn\QrCode\Payload\VCard;

it('produces a minimal vCard 3.0 with FN and N', function (): void {
    $expected = \implode("\r\n", [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'FN:John Doe',
        'N:John Doe;;;;',
        'END:VCARD',
    ]);

    expect((string) VCard::make('John Doe'))->toBe($expected);
});

it('emits ORG, TITLE, URL, ADR, NOTE when set', function (): void {
    $card = VCard::make('John Doe')
        ->withOrg('Acme')
        ->withTitle('Engineer')
        ->withUrl('https://acme.com')
        ->withAddress('1 Main St, SF')
        ->withNote('hi');

    $out = (string) $card;
    expect($out)->toContain('ORG:Acme');
    expect($out)->toContain('TITLE:Engineer');
    expect($out)->toContain('URL:https://acme.com');
    expect($out)->toContain('ADR:;;1 Main St\\, SF;;;;');
    expect($out)->toContain('NOTE:hi');
});

it('emits TEL and EMAIL entries with TYPE when provided', function (): void {
    $card = VCard::make('Jane')
        ->addPhone('+14155550123', VCard::TYPE_WORK)
        ->addPhone('+14155559999')
        ->addEmail('a@b.com', VCard::TYPE_WORK)
        ->addEmail('b@c.com');

    $out = (string) $card;
    expect($out)->toContain('TEL;TYPE=WORK:+14155550123');
    expect($out)->toContain('TEL:+14155559999');
    expect($out)->toContain('EMAIL;TYPE=WORK:a@b.com');
    expect($out)->toContain('EMAIL:b@c.com');
});

it('escapes backslash, semicolon, comma, and newlines in field values', function (): void {
    $card = VCard::make('John')
        ->withOrg('Ac;me\\Inc,"X"')
        ->withNote("line 1\nline 2");

    $out = (string) $card;
    expect($out)->toContain('ORG:Ac\\;me\\\\Inc\\,"X"');
    expect($out)->toContain('NOTE:line 1\\nline 2');
});

it('wither methods return new instances (immutable)', function (): void {
    $a = VCard::make('John');
    $b = $a->withOrg('Acme');

    expect($a)->not->toBe($b);
    expect((string) $a)->not->toContain('ORG:');
    expect((string) $b)->toContain('ORG:Acme');
});

it('throws on empty fullName', function (): void {
    expect(fn () => VCard::make('  '))->toThrow(PayloadException::class, 'fullName');
});

it('uses CRLF line endings per RFC 6350', function (): void {
    expect((string) VCard::make('John'))->toContain("\r\n");
});
