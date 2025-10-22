<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoalPertanyaan extends Model
{
    protected $fillable = ['soal_id','pertanyaan','pilihan_json','jawaban'];

    public function sesisoal()
    {
        return $this->hasMany(SesiSoal::class);
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class);
    }
}
