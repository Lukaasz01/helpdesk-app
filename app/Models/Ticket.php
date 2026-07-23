<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'client_id',
        'technician_id',
        'status',
        'priority',
        'resolved_at',
    ];

    public function client(): BelongsTo {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}