<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display users list (Admin only)
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::orderBy('id', 'desc')->get();

        return view('users.index', compact('users'));
    }

    /**
     * Store new user (Admin only)
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users'],
            'full_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:4'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'sms_enabled' => ['nullable', 'boolean'],
        ], [
            'username.unique' => 'მომხმარებელი ამ username-ით უკვე არსებობს!',
        ]);

        User::create([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'sms_enabled' => $request->boolean('sms_enabled', true),
        ]);

        return back()->with('success', 'მომხმარებელი დაემატა! ✅');
    }

    /**
     * Update user (Admin only)
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'full_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'sms_enabled' => ['nullable', 'boolean'],
        ], [
            'username.unique' => 'ეს username უკვე დაკავებულია!',
        ]);

        $user->update([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'sms_enabled' => $request->boolean('sms_enabled'),
        ]);

        return back()->with('success', 'მონაცემები განახლდა! ✅');
    }

    /**
     * Delete user (Admin only)
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        // Check if trying to delete self
        if ($user->id === Auth::id()) {
            return back()->with('error', 'საკუთარი თავის წაშლა შეუძლებელია!');
        }

        // Check if user has cars
        if ($user->cars()->exists()) {
            return back()->with('error', 'ვერ წაიშალა! ჯერ წაშალეთ მანქანები.');
        }

        $user->delete();

        return back()->with('warning', 'მომხმარებელი წაიშალა!');
    }

    /**
     * Toggle SMS status (Admin only)
     */
    public function toggleSms(User $user): RedirectResponse
    {
        $this->authorize('toggleSms', $user);

        $user->update(['sms_enabled' => !$user->sms_enabled]);

        return back();
    }

    /**
     * Reset user password (Admin only)
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $request->validate([
            'new_password' => ['required', 'string', 'min:4'],
        ]);

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return back()->with('success', 'პაროლი წარმატებით შეიცვალა!');
    }

    /**
     * Show change password form (for logged-in user)
     */
    public function showChangePasswordForm(): View
    {
        return view('users.change-password');
    }

    /**
     * Change own password
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:4', 'confirmed'],
        ], [
            'current_password.current_password' => 'მიმდინარე პაროლი არასწორია',
            'new_password.confirmed' => 'პაროლები არ ემთხვევა',
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return back()->with('success', 'პაროლი წარმატებით შეიცვალა!');
    }
}
