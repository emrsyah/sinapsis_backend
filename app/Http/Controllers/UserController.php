<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Validasi secara parsial, hanya field yang dikirim yang akan diupdate
        $validated = $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'image' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Berrhasil mengupdate profil',
            'data'    => $user,
        ], 200);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data user berhasil diambil',
            'data' => $user,
        ], 200);
    }
}
