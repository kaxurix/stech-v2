<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'registration_id',
        'proof_file_path',
        'original_filename',
        'file_size',
        'uploaded_at',
        'status',
        'verified_at',
        'verified_by',
        'admin_notes',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'file_size'   => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function formattedSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    public function isImage(): bool
    {
        $ext = strtolower(pathinfo($this->original_filename, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
}
