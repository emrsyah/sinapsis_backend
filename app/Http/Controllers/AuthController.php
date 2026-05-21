<?php

namespace App\Http\Controllers;

use App\Data\User\UpdateUserData;
use App\Data\User\UserData;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle(): \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle Google OAuth callback and issue a Sanctum token.
     */
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'image' => $googleUser->avatar,
                    'google_id' => $googleUser->id,
                ]
            );

            $deviceName = request()->userAgent() ?? 'Unknown Device';
            $token = $user->createToken($deviceName)->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => UserData::fromModel($user),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Google login failed: '.$e->getMessage()], 500);
        }
    }

    /**
     * Revoke the current access token (logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    /**
     * Return the authenticated user's profile.
     */
    public function me(Request $request): UserData
    {
        return UserData::fromModel($request->user());
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateUserData $data, Request $request): UserData
    {
        $user = $request->user();
        $user->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return UserData::fromModel($user->fresh());
    }

    /**
     * Update last opened note.
     */
    public function updateLastOpened(Request $request): JsonResponse
    {
        $request->validate(['note_id' => 'required|uuid|exists:notes,id']);
        $request->user()->update(['last_opened_note_id' => $request->note_id]);

        return response()->json(null, 204);
    }
}
