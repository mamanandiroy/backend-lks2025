<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesiSoal extends Model
{
    protected $fillable = ['user_id','soal_id','skor'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class);
    }

}
