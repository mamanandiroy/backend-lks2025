<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Materi;
use App\Models\User;
use App\Models\SesiSoal;
use App\Models\Informasi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => [
                'soal' => Soal::count(),
                'materi' => Materi::count(),
                'user' => User::count(),
                'sesisoal' => SesiSoal::count(),
                'informasi' => Informasi::count(),
            ]
        ]);
    }
}
