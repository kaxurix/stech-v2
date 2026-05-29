<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    protected $fillable = [
        'user_id',
        'team_name',
        'competition',
        'member_count',
        'institution',
        'phone',
        'category',
        'status',
        'is_finalist',
    ];

    protected $casts = [
        'is_finalist' => 'boolean',
    ];

    // ── Status labels ─────────────────────────────────────────────────────────

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending_payment'      => 'Menunggu Pembayaran',
            'pending_verification' => 'Menunggu Verifikasi',
            'verified'             => 'Terverifikasi',
            'rejected'             => 'Ditolak',
            default                => 'Tidak Diketahui',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending_payment'      => 'amber',
            'pending_verification' => 'violet',
            'verified'             => 'green',
            'rejected'             => 'red',
            default                => 'gray',
        };
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function submission()
    {
        return $this->hasOne(Submission::class);
    }
}
