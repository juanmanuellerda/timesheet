<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $casts = [
        'start_at' => 'datetime'
    ];

    public function tasks(): HasMany {
        return $this->hasMany(Task::class);
    } 

    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class);
    } 
}