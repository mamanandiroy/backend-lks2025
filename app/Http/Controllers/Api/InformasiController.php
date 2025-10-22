<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Informasi;
use Illuminate\Http\Request;

class InformasiController extends Controller
{
    /**
     * Menampilkan daftar informasi dengan fitur pencarian dan paginasi.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 5);
        $offset = (int) $request->get('offset', 0);
        $search = $request->get('search', '');

        // Mulai query dasar
        $query = Informasi::query();

        // Filter berdasarkan pencarian judul (jika ada)
        if (!empty($search)) {
            $query->where('judul', 'like', '%' . $search . '%');
        }

        // Hitung total data sebelum dibatasi limit
        $total = $query->count();

        // Jika limit = 'all', ambil semua data
        if ($limit === 'all') {
            $informasi = $query->orderBy('id', 'desc')->get();
        } else {
            $informasi = $query->orderBy('id', 'desc')
                            ->offset($offset)
                            ->limit((int)$limit)
                            ->get();
        }

        if ($informasi->isEmpty()) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'total_data' => Informasi::count(),
            'data' => $informasi,
        ], 200);
    }

    /**
     * Menampilkan detail informasi berdasarkan ID
     */
    public function show($id)
    {
        $informasi = Informasi::find($id);

        if (!$informasi) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $informasi,
        ], 200);
    }

    /**
     * Menambah informasi baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
        ]);

        $informasi = Informasi::create($validated);

        if (!$informasi) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $informasi
        ], 200);
    }

    /**
     * Mengupdate informasi berdasarkan ID
     */
    public function update(Request $request, $id)
    {
        $informasi = Informasi::find($id);

        if (!$informasi) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
        ]);

        $updated = $informasi->update($validated);

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
            'data' => $informasi
        ], 200);
    }

    /**
     * Menghapus informasi berdasarkan ID
     */
    public function destroy($id)
    {
        $informasi = Informasi::find($id);

        if (!$informasi) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        $informasi->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Success',
        ], 200);
    }
}
