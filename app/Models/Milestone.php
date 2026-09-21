<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
