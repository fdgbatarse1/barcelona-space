<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\LogsToSentry;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    use LogsToSentry;

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $context = ['user_id' => $request->user()->id];
        $this->addBreadcrumb('auth.password_changed', 'User changed password', $context);
        $this->logAction('info', 'User changed password', $context);

        return back()->with('status', 'password-updated');
    }
}
