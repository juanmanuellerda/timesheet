<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Activity extends Model
{
    use HasFactory;

    protected $casts = ['
        start_at' => 'datetime
    '];
    
    public function task(): BelongsTo {
        return $this->belongsTo(Task::class);
    } 

    protected function isCompleted(): Attribute 
    {
    
    return Attribute::make(
        get: fn (mixed $value) => (bool) $this->end_at,
        );
    }
}

