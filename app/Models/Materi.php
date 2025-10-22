<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $fillable = ['judul','konten'];

    public function babmateri()
    {
        return $this->hasMany(BabMateri::class);
    }

    public function soal()
    {
        return $this->hasMany(Soal::class);
    }
}
