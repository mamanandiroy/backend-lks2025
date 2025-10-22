<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\SoalPertanyaan;
use Illuminate\Http\Request;

class SoalPertanyaanController extends Controller
{
    /**
     * Menampilkan daftar Soal Pertanyaan dengan fitur pencarian dan paginasi.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 5); // jumlah data per halaman
        $offset = (int) $request->get('offset', 0);
        $search = $request->get('search', '');

        // Mulai query dasar
        $query = SoalPertanyaan::with('soal.materi'); // relasi ke tabel soal dan materi

        // Filter berdasarkan pencarian judul materi (jika ada)
        if (!empty($search)) {
            $query->whereHas('soal.materi', function ($q) use ($search) {
                $q->where('judul', 'like', '%' . $search . '%');
            });
        }

        // Hitung total data sebelum paginasi
        $total = $query->count();

        // Jika limit = 'all', ambil semua data tanpa batasan
        if ($limit === 'all') {
            $soalpertanyaan = $query->orderBy('id', 'desc')->get();
        } else {
            $soalpertanyaan = $query->orderBy('id', 'desc')
                ->offset($offset)
                ->limit((int) $limit)
                ->get();
        }

        // Jika data kosong
        if ($soalpertanyaan->isEmpty()) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        // Berhasil
        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'total_data' => $total,
            'data' => $soalpertanyaan,
        ], 200);
    }

    /**
     * Menampilkan detail Soal Pertanyaan berdasarkan ID
     */
    public function show($id)
    {
        $soalpertanyaan = SoalPertanyaan::with('soal.materi')->find($id);

        if (!$soalpertanyaan) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $soalpertanyaan,
        ], 200);
    }

    // GET /api/soalpertanyaan/materi/{materiId}
    public function getByMateri($materiId)
    {
        try {
            // Ambil data materi
            $materi = Materi::find($materiId);
            if (!$materi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Materi tidak ditemukan.'
                ], 404);
            }

            // Ambil daftar soal berdasarkan materi
            $soals = Soal::where('materi_id', $materiId)->pluck('id');

            // Ambil daftar pertanyaan dari semua soal tersebut
            $pertanyaan = SoalPertanyaan::whereIn('soal_id', $soals)->get();

            // Ubah kolom pilihan_json ke array agar bisa ditampilkan di Vue
            $pertanyaan->transform(function ($item) {
                $item->opsi = json_decode($item->pilihan_json, true);
                unset($item->pilihan_json);
                return $item;
            });

            return response()->json([
                'success' => true,
                'materi' => $materi,
                'data' => $pertanyaan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Menambahkan Soal Pertanyaan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'soal_id' => 'required|exists:soals,id',
            'pertanyaan' => 'required|string',
            'pilihan_json' => 'required|string',
            'jawaban' => 'required|string',
        ]);

        $soalpertanyaan = SoalPertanyaan::create($validated);

        if (!$soalpertanyaan) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $soalpertanyaan,
        ], 200);
    }

    /**
     * Mengupdate Soal Pertanyaan
     */
    public function update(Request $request, $id)
    {
        $soalpertanyaan = SoalPertanyaan::find($id);

        if (!$soalpertanyaan) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        $validated = $request->validate([
            'soal_id' => 'required|exists:soals,id',
            'pertanyaan' => 'required|string',
            'pilihan_json' => 'required|string',
            'jawaban' => 'required|string',
        ]);

        $updated = $soalpertanyaan->update($validated);

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
            'data' => $soalpertanyaan,
        ], 200);
    }

    /**
     * Menghapus Soal Pertanyaan
     */
    public function destroy($id)
    {
        $soalpertanyaan = SoalPertanyaan::find($id);

        if (!$soalpertanyaan) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
            ], 200);
        }

        $soalpertanyaan->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Success',
        ], 200);
    }
}
