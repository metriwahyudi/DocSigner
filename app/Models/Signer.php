<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signer extends Model
{
    use HasFactory, SoftDeletes;

    public function documents(){
        return $this->hasMany(Signer::class);
    }
}
