<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BabMateri extends Model
{
    protected $fillable = ['materi_id','judul','konten'];

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }
}
