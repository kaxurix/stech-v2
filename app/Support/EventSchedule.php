<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Sumber tunggal untuk pertanyaan "apakah pendaftaran/pengumpulan masih buka?".
 *
 * Dipakai controller (untuk menolak request) sekaligus dibagikan ke frontend
 * lewat HandleInertiaRequests, supaya tampilan tombol dan aturan di server
 * tidak pernah berbeda. Tanggalnya diatur di config/stech.php.
 */
class EventSchedule
{
    public const BEFORE = 'before';
    public const OPEN = 'open';
    public const CLOSED = 'closed';

    public static function registrationOpensAt(): ?CarbonImmutable
    {
        return self::parse(config('stech.registration.opens_at'));
    }

    public static function registrationClosesAt(): ?CarbonImmutable
    {
        return self::parse(config('stech.registration.closes_at'));
    }

    public static function submissionClosesAt(): ?CarbonImmutable
    {
        return self::parse(config('stech.submission.closes_at'));
    }

    /**
     * BEFORE = belum dibuka, OPEN = sedang berlangsung, CLOSED = sudah ditutup.
     */
    public static function registrationStatus(): string
    {
        $now = CarbonImmutable::now();

        if (($opens = self::registrationOpensAt()) && $now->lt($opens)) {
            return self::BEFORE;
        }

        if (($closes = self::registrationClosesAt()) && $now->gt($closes)) {
            return self::CLOSED;
        }

        return self::OPEN;
    }

    public static function registrationIsOpen(): bool
    {
        return self::registrationStatus() === self::OPEN;
    }

    public static function submissionIsOpen(): bool
    {
        $closes = self::submissionClosesAt();

        return $closes === null || CarbonImmutable::now()->lte($closes);
    }

    /**
     * Pesan siap tampil untuk peserta. Dipakai di halaman depan maupun sebagai
     * pesan error validasi, supaya kalimatnya konsisten.
     */
    public static function registrationMessage(): ?string
    {
        return match (self::registrationStatus()) {
            self::BEFORE => 'Pendaftaran dibuka mulai '
                .self::registrationOpensAt()->translatedFormat('j F Y').'.',
            self::CLOSED => 'Pendaftaran sudah ditutup pada '
                .self::registrationClosesAt()->translatedFormat('j F Y').'.',
            default => null,
        };
    }

    /**
     * @return array{status: string, is_open: bool, opens_at: ?string, closes_at: ?string, message: ?string}
     */
    public static function toArray(): array
    {
        return [
            'status' => self::registrationStatus(),
            'is_open' => self::registrationIsOpen(),
            'opens_at' => self::registrationOpensAt()?->toIso8601String(),
            'closes_at' => self::registrationClosesAt()?->toIso8601String(),
            'message' => self::registrationMessage(),
        ];
    }

    private static function parse(?string $value): ?CarbonImmutable
    {
        return filled($value) ? CarbonImmutable::parse($value) : null;
    }
}
