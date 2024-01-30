<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    use HasFactory;

    protected $table = 'signatures';
    protected $guarded = [];
    public function signer(){
        return $this->belongsTo(Signer::class,'signer_id');
    }
    public function document(){
        return $this->belongsTo(Document::class,'document_id');
    }
}
