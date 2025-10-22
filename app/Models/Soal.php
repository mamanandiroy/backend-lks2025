<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    protected $fillable = ['materi_id'];

    public function soalpertanyaan()
    {
        return $this->hasMany(SoalPertanyaan::class);
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }
}
