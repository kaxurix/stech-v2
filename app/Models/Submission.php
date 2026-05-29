<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'registration_id',
        'github_url',
        'drive_url',
        'project_title',
        'description',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
