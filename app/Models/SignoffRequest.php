<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignoffRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'milestone_id',
        'token_hash',
        'expires_at',
        'status',
        'signatory_name',
        'signatory_title',
        'client_ip',
        'client_user_agent',
        'signed_at',
        'rejection_notes',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }
}
