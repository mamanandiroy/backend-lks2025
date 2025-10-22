<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use Illuminate\Http\Request;

class SoalController extends Controller
{
    /**
     * Menampilkan daftar Soal dengan fitur pencarian dan paginasi.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 5); // jumlah data per halaman
        $offset = (int) $request->get('offset', 0);
        $search = $request->get('search', '');

        // Mulai query dasar
        $query = Soal::with('materi'); // relasi ke tabel materi

        // Filter berdasarkan pencarian judul (jika ada)
        if (!empty($search)) {
            $query->whereHas('materi', function ($q) use ($search) {
                $q->where('judul', 'like', '%' . $search . '%');
            });
        }

        // Hitung total data sebelum dibatasi limit
        $total = $query->count();

        // Jika limit = 'all', ambil semua data tanpa batasan
        if ($limit === 'all') {
            $soal = $query->orderBy('id', 'desc')->get();
        } else {
            $soal = $query->orderBy('id', 'desc')
                ->offset($offset)
                ->limit((int) $limit)
                ->get();
        }

        // Cek apakah data ada

        if ($soal->isEmpty()) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'total_data' => $total,
            'data' => $soal
        ], 200);
    }

    /**
     * Menampilkan detail Soal berdasarkan ID
     */
    public function show($id)
    {
        $soal = Soal::with('materi')->find($id);

        if (!$soal) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $soal,
        ], 200);
    }

    /**
     * Menambahkan Soal baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'materi_id' => 'required|exists:materis,id',
        ]);

        $soal = Soal::create($validated);

        if (!$soal) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $soal
        ], 200);
    }

    /**
     * Mengupdate Soal
     */
    public function update(Request $request, $id)
    {
        $soal = Soal::find($id);

        if (!$soal) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
            ], 200);
        }

        $validated = $request->validate([
            'materi_id' => 'required|exists:materis,id',
        ]);

        $updated = $soal->update($validated);

        if (!$updated) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $soal
        ], 200);
    }

    /**
     * Menghapus Soal
     */
    public function destroy($id)
    {
        $soal = Soal::find($id);

        if (!$soal) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        $soal->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Success',
        ], 200);
    }
}
