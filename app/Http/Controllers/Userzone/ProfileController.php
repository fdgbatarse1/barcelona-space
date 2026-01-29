<?php

namespace App\Http\Controllers\Userzone;

use App\Http\Controllers\Concerns\LogsToSentry;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use LogsToSentry;

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('userzone.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $emailChanged = $request->user()->isDirty('email');

        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        $context = ['user_id' => $request->user()->id, 'email_changed' => $emailChanged];
        $this->addBreadcrumb('profile.updated', 'Profile updated', $context);
        $this->logAction('info', 'Profile updated', $context);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $context = ['user_id' => $user->id];
        $this->addBreadcrumb('profile.deleted', 'Account deleted', $context);
        $this->logAction('warning', 'Account deleted', $context);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
