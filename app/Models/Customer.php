<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $casts = ['
        start_at' => 'datetime'
    ];
    
    public function projects(): HasMany {
        return $this->hasMany(Project::class);
    } 
}


