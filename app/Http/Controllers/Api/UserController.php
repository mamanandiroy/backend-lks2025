<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Menampilkan semua data user dengan fitur pencarian dan paginasi.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 5); // jumlah data per halaman
        $offset = (int) $request->get('offset', 0);
        $search = $request->get('search', '');

        // Mulai query dasar
        $query = User::query();

        // Filter berdasarkan pencarian nama (jika ada)
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Hitung total data sebelum dibatasi limit
        $total = $query->count();
        // Jika limit = 'all', ambil semua data
        if ($limit === 'all') {
            $user = $query->orderBy('id', 'desc')->get();
        } else {
            $user = $query->orderBy('id', 'desc')
                    ->offset($offset)
                    ->limit((int)$limit)
                    ->get();
        }

        if ($user->isEmpty()) {
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
            'data' => $user,
        ], 200);
    }

    /**
     * Register user baru
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        $user = User::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role ?? 'user',
            'status'   => 0,
        ]);

        if (!$user) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $user
        ], 200);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'errors' => $validator->errors()
            ], 200);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed'
            ], 200);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200);
    }

    /**
     * Logout user (hapus token aktif)
     */
    public function logout(Request $request)
    {
        try {
            // Hapus token login yang sedang aktif
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'code' => 200,
                'message' => 'Logout success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 201,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * Detail user
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $user,
        ], 200);
    }

    /**
     * Menambah user baru (admin)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role'     => 'nullable|string',
            'status'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        $user = User::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role ?? 'user',
            'status'   => $request->status ?? 0
        ]);

        if (!$user) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'data' => null
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $user
        ], 200);
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed'
            ], 200);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'nullable|string|max:255',
            'phone'    => 'nullable|string|max:20',
            'email'    => 'nullable|string|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role'     => 'nullable|string',
            'status'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed',
                'errors' => $validator->errors()
            ], 200);
        }

        $data = $request->only(['name', 'phone', 'email', 'role', 'status']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $user
        ], 200);
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'code' => 201,
                'message' => 'Failed'
            ], 200);
        }

        $user->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Success'
        ], 200);
    }
}
