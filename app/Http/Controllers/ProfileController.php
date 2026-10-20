<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        $layout = match ($user->role) {
            'owner' => 'layouts.owner',
            'cashier' => 'layouts.cashier',
            default => 'layouts.app',
        };

        // Format phone without prefix +62/62/0 for clean input display
        $phone = $user->phone ?? '';
        if (str_starts_with($phone, '+62')) {
            $phone = substr($phone, 3);
        } elseif (str_starts_with($phone, '62')) {
            $phone = substr($phone, 2);
        } elseif (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        return view('profile.edit', compact('user', 'layout', 'phone'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', // Maks 10MB
        ]);

        // Clean phone number (simpan format +628...)
        if (! empty($validated['phone'])) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = substr($cleanPhone, 1);
            } elseif (str_starts_with($cleanPhone, '62')) {
                $cleanPhone = substr($cleanPhone, 2);
            }
            $validated['phone'] = '+62'.$cleanPhone;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Informasi akun profil berhasil diperbarui!');
    }

    public function destroyAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return redirect()->route('profile.edit')->with('success', 'Foto avatar berhasil dihapus.');
    }
}
