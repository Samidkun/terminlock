<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'milestone_id',
        'invoice_number',
        'amount',
        'issued_at',
        'due_date',
        'paid_at',
        'payment_notes',
        'status',
    ];

    protected $casts = [
        'amount' => 'integer',
        'issued_at' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }
}
