<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $casts = [
        'file' => 'array',
    ];

    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class);
    } 
    // public function users() {
    //     return $this->belongsToMany('App\Models\User');
    // } 

    public function project(): BelongsTo {
        return $this->belongsTo(Project::class);
    } 

    public function activities():HasMany {
        return $this->hasMany(Activity::class);
    } 

}

