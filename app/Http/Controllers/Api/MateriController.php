<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    /**
 * Menampilkan daftar Materi dengan fitur pencarian dan paginasi.
 */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 5);
        $offset = (int) $request->get('offset', 0);
        $search = $request->get('search', '');

        // Mulai query dasar
        $query = Materi::query();

        // Filter berdasarkan pencarian judul (jika ada)
        if (!empty($search)) {
            $query->where('judul', 'like', '%' . $search . '%');
        }

        // Hitung total data sebelum dibatasi limit
        $total = $query->count();

        // Jika limit = 'all', ambil semua data
        if ($limit === 'all') {
            $materi = $query->orderBy('id', 'desc')->get();
        } else {
            $materi = $query->orderBy('id', 'desc')
                        ->offset($offset)
                        ->limit((int)$limit)
                        ->get();
        }

        // Cek apakah data ada
        if ($materi->isEmpty()) {
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
            'data' => $materi,
        ], 200);
    }

    /**
     * Menampilkan detail Materi berdasarkan ID
     */
    public function show($id)
    {
        $materi = Materi::find($id);

        if (!$materi) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $materi,
        ], 200);
    }

    /**
     * Menambah Materi baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
        ]);

        $materi = Materi::create($validated);

        if (!$materi) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $materi,
        ], 200);
    }

    /**
     * Mengupdate Materi berdasarkan ID
     */
    public function update(Request $request, $id)
    {
        $materi = Materi::find($id);

        if (!$materi) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
        ]);

        $updated = $materi->update($validated);

        if (!$updated) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $materi,
        ], 200);
    }

    /**
     * Menghapus Materi berdasarkan ID
     */
    public function destroy($id)
    {
        $materi = Materi::find($id);

        if (!$materi) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        $materi->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Success',
        ], 200);
    }
}
