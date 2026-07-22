<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    protected $fillable = [
        'registration_id',
        'position',
        'is_leader',
        'full_name',
        'identity_number',
        'institution',
        'major',
        'batch',
        'phone',
        'email',
    ];

    protected $casts = [
        'is_leader' => 'boolean',
        'position'  => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
