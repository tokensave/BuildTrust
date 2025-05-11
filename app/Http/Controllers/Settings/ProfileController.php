<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'company' => $request->user()->company,
        ]);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'username'     => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email'        => 'required|email|max:255|unique:users,email,' . $user->id,
            'company_name' => 'nullable|string|max:255',
            'inn'          => 'nullable|string|max:20',
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $user->clearMediaCollection('avatar');

            // сохраняем новый
            $user
                ->addMediaFromRequest('avatar')
                ->usingFileName(uniqid('', true) . '.' . $request->file('avatar')->extension())
                ->toMediaCollection('avatar');
        }

        // Обновляем остальные поля
        $user->fill([
            'username' => $validated['username'] ?? $user->username,
            'email'    => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Обновляем компанию, если нужно
        if ($user->isDirector() && $user->company) {
            $user->company->update([
                'name' => $validated['company_name'] ?? $user->company->name,
                'inn'  => $validated['inn']          ?? $user->company->inn,
            ]);
        }

        return to_route('profile.edit')->with('status', 'profile-updated');
    }


    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
