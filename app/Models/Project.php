<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'name',
        'contract_number',
        'total_amount',
        'retention_percentage',
        'retention_days',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'retention_percentage' => 'integer',
        'retention_days' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('order');
    }
}
