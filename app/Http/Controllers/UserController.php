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
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi secara parsial, hanya field yang dikirim yang akan diupdate
        $validated = $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->user_id.',user_id',
            'image' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Berrhasil mengupdate profil',
            'data'    => $user,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    public function profile(): JsonResponse
    {
        $user = Auth::user();
        return response()->json([
            'data' => $user,
        ]);
    }

    public function verifiedEmail()
    {
        $user = Auth::user();
        $user->email_verified_at = now();
        $user->save();
        return redirect()->route('profile');
    }
}
