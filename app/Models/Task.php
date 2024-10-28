<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class Task extends Model
{
    use HasFactory;

    public function users(): HasMany {
        return $this->hasMany(user::class);
    } 
    

}
