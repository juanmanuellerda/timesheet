<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class);
    } 
    // public function users() {
    //     return $this->belongsToMany('App\Models\User');
    // } 
}
