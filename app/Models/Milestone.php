<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Milestone extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_id',
        'order',
        'name',
        'amount',
        'percentage',
        'status',
        'due_date',
        'is_retention',
    ];

    protected $casts = [
        'amount' => 'integer',
        'percentage' => 'integer',
        'is_retention' => 'boolean',
        'due_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class);
    }

    public function signoffRequests(): HasMany
    {
        return $this->hasMany(SignoffRequest::class);
    }

    public function latestSignoffRequest(): HasOne
    {
        return $this->hasOne(SignoffRequest::class)->latestOfMany();
    }
}
