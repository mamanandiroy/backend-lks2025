<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BabMateri;
use Illuminate\Http\Request;

class BabMateriController extends Controller
{
    /**
     * Menampilkan daftar Bab Materi dengan fitur pencarian dan paginasi.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 5); // jumlah data per halaman
        $offset = (int) $request->get('offset', 0);
        $search = $request->get('search', '');

        // Mulai query dasar
        $query = BabMateri::with('materi'); // relasi ke tabel materi

        // Filter berdasarkan pencarian judul (jika ada)
        if (!empty($search)) {
            $query->where('judul', 'like', '%' . $search . '%');
        }

        // Hitung total data sebelum dibatasi limit
        $total = $query->count();

        // Jika limit = 'all', ambil semua data tanpa batasan
        if ($limit === 'all') {
            $babMateri = $query->orderBy('id', 'desc')->get();
        } else {
            $babMateri = $query->orderBy('id', 'desc')
                ->offset($offset)
                ->limit((int) $limit)
                ->get();
        }

        // Cek apakah data ada
        if ($babMateri->isEmpty()) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        // Jika sukses
        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'total_data' => $total,
            'data' => $babMateri,
        ], 200);
    }

    /**
     * Menampilkan detail Bab Materi berdasarkan ID
     */
    public function show($id)
    {
        $babMateri = BabMateri::with('materi')->find($id);

        if (!$babMateri) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $babMateri
        ], 200);
    }

    /**
     * Menambahkan Bab Materi baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'materi_id' => 'required|exists:materis,id',
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
        ]);

        $babMateri = BabMateri::create($validated);

        if (!$babMateri) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $babMateri
        ], 200);
    }

    /**
     * Mengupdate Bab Materi
     */
    public function update(Request $request, $id)
    {
        $babMateri = BabMateri::find($id);

        if (!$babMateri) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        $validated = $request->validate([
            'materi_id' => 'required|exists:materis,id',
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
        ]);

        $updated = $babMateri->update($validated);

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
            'data' => $babMateri,
        ], 200);
    }

    /**
     * Menghapus Bab Materi
     */
    public function destroy($id)
    {
        $babMateri = BabMateri::find($id);

        if (!$babMateri) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        $babMateri->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Success',
        ], 200);
    }
}
