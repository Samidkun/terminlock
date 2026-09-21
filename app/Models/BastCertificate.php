<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BastCertificate extends Model
{
    use HasUuids;

    protected $fillable = [
        'signoff_request_id',
        'milestone_id',
        'bast_number',
        'snapshot_data',
        'sha256_checksum',
        'pdf_path',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
    ];

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function signoffRequest(): BelongsTo
    {
        return $this->belongsTo(SignoffRequest::class);
    }
}
