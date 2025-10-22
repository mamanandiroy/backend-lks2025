<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\SoalPertanyaan;
use App\Models\SesiSoal;
use Illuminate\Http\Request;

class SesiSoalController extends Controller
{
    /**
     * Menampilkan daftar Sesi Soal dengan fitur pencarian dan paginasi.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 5); // jumlah data per halaman
        $offset = (int) $request->get('offset', 0);
        $search = $request->get('search', '');

        // Mulai query dasar
        $query = SesiSoal::with(['user','soal.materi']); // relasi ke tabel soal, materi dan user

        // Filter berdasarkan pencarian nama user (jika ada)
        if (!empty($search)) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // Hitung total data sebelum paginasi
        $total = $query->count();

        // Jika limit = 'all', ambil semua data tanpa batasan
        if ($limit === 'all') {
            $sesisoal = $query->orderByDesc('id')->get();
        } else {
            $sesisoal = $query->orderByDesc('id')
                ->offset($offset)
                ->limit((int) $limit)
                ->get();
        }

        if ($sesisoal->isEmpty()) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'total_data' => $total,
            'data' => $sesisoal,
        ], 200);
    }

    /**
     * Menampilkan daftar Sesi Soal sesuai user yang login.
     */

    public function tampilsesi(Request $request)
    {
        // Ambil user dari token
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ambil semua soal beserta materi
        $semuaSoal = Soal::with('materi')
            ->when($request->has('search') && !empty($request->search), function ($query) use ($request) {
                $query->whereHas('materi', function ($q) use ($request) {
                    $q->where('judul', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        // Ambil semua sesi soal user ini
        $sesiSoalUser = SesiSoal::where('user_id', $user->id)->get()->keyBy('soal_id');

        // Gabungkan data soal dengan sesi user
        $dataGabung = $semuaSoal->map(function ($soal) use ($sesiSoalUser) {
            $sesi = $sesiSoalUser->get($soal->id);

            return [
                'id' => $sesi->id ?? null,
                'soal_id' => $soal->id,
                'materi_id' => $soal->materi->id ?? null,
                'judul' => $soal->materi->judul ?? '-',
                'skor' => $sesi->skor ?? null,
                'created_at' => $sesi->created_at ?? null,
                'status' => $sesi ? 'Sudah Dikerjakan' : 'Belum Dikerjakan',
            ];
        });

        // Total data sebelum paginasi
        $total = $dataGabung->count();

        // Terapkan paginasi manual (offset & limit)
        $offset = (int) ($request->offset ?? 0);
        $limit = $request->limit ?? 10;

        if ($limit !== 'all') {
            $dataGabung = $dataGabung->slice($offset, $limit)->values();
        }

        // Response JSON
        return response()->json([
            'success' => true,
            'total_data' => $total,
            'data' => $dataGabung,
        ]);
    }

    /**
     * Menampilkan detail Sesi Soal berdasarkan ID
     */
    public function show($id)
    {
        $sesisoal = SesiSoal::with(['user','soal.soalpertanyaan'])->find($id);

        if (!$sesisoal) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $sesisoal,
        ], 200);
    }

    /**
     * Submit Jawaban
     */

    public function submitJawaban(Request $request)
    {
        $validated = $request->validate([
            'materi_id' => 'required|exists:materis,id',
            'jawaban' => 'required|array',
            'waktu_habis' => 'required|boolean',
        ]);

        $user = $request->user();
        $materiId = $validated['materi_id'];
        $jawabanUser = $validated['jawaban']; // array: [id_pertanyaan => "A", "B", ...]

        // Ambil semua soal dalam materi ini
        $soalIds = Soal::where('materi_id', $materiId)->pluck('id');
        $pertanyaans = SoalPertanyaan::whereIn('soal_id', $soalIds)->get();

        $total = $pertanyaans->count();
        $benar = 0;

        // 🔍 Periksa setiap jawaban user
        foreach ($pertanyaans as $p) {
            if (isset($jawabanUser[$p->id])) {
                $userAnswer = strtoupper(trim($jawabanUser[$p->id])); // misal "A"
                $correctAnswer = strtoupper(trim($p->jawaban));       // misal "A"

                if ($userAnswer === $correctAnswer) {
                    $benar++;
                }
            }
        }

        // 🎯 Hitung skor (10 poin per jawaban benar)
        $skor = $benar * 10;

        // Jika total soal bukan 10, tetap skala 100 maksimum
        if ($total > 10) {
            $skor = round(($benar / $total) * 100, 2);
        }

        // 🔁 Simpan atau update sesi user
        $sesi = SesiSoal::where('user_id', $user->id)
            ->whereIn('soal_id', $soalIds)
            ->first();

        if ($sesi) {
            // Update skor (jika mengulang)
            $sesi->update([
                'skor' => $skor,
            ]);
            $status = 'update';
        } else {
            // Simpan sesi baru
            $sesi = SesiSoal::create([
                'soal_id' => $soalIds->first(),
                'user_id' => $user->id,
                'skor' => $skor,
            ]);
            $status = 'baru';
        }

        // ✅ Kirim respon JSON ke frontend
        return response()->json([
            'success' => true,
            'message' => "Jawaban berhasil disimpan ($status)",
            'data' => [
                'skor' => $skor,
                'benar' => $benar,
                'total' => $total,
            ],
        ]);
    }

    public function cekSesiSoal(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['code' => 401, 'message' => 'User tidak terautentikasi']);
        }

        try {
            // Ambil semua sesi soal milik user login + relasi soal & materi
            $sesi = SesiSoal::where('user_id', $user->id)
                ->with(['soal.materi'])
                ->orderBy('id', 'desc')
                ->get();

            // Hitung jumlah sesi dan rata-rata skor
            $jumlahSesi = $sesi->count();
            $rataRataSkor = $sesi->avg('skor');

            if ($jumlahSesi > 0) {
                return response()->json([
                    'code' => 200,
                    'message' => 'Data sesi ditemukan',
                    'data' => [
                        'jumlah_sesi' => $jumlahSesi,
                        'rata_rata_skor' => round($rataRataSkor, 2),
                        'daftar_sesi' => $sesi,
                    ]
                ]);
            } else {
                return response()->json([
                    'code' => 201,
                    'message' => 'Belum ada sesi soal',
                    'data' => null
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

}
