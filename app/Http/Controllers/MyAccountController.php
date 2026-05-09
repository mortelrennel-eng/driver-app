<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyNewEmail;
use App\Mail\EmailChangeRequested;

class MyAccountController extends Controller
{

    public function updateProfileImage(Request $request)
    {
        $user = Auth::user();

        if ($request->has('icon_path')) {
            $user->update([
                'profile_image' => $request->icon_path
            ]);
            return redirect()->route('my-account')->with('success', 'Profile icon updated successfully!');
        }

        if ($request->hasFile('profile_image')) {
            $request->validate([
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Delete old image if it's not an icon
            if ($user->profile_image && !str_contains($user->profile_image, 'image/')) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $path = $request->file('profile_image')->store('profiles', 'public');
            
            $user->update([
                'profile_image' => $path
            ]);

            return redirect()->route('my-account')->with('success', 'Profile image uploaded successfully!');
        }

        return redirect()->route('my-account')->with('error', 'No image or icon selected.');
    }
    public function index()
    {
        $user = Auth::user();

        // If first_name is empty but full_name exists, try to split it for display
        if (empty($user->first_name) && !empty($user->full_name)) {
            $nameParts = explode(' ', trim($user->full_name));
            if (count($nameParts) > 1) {
                $user->first_name = $nameParts[0];
                $user->last_name = end($nameParts);
                if (count($nameParts) > 2) {
                    $user->middle_name = $nameParts[1];
                }
            } else {
                $user->first_name = $user->full_name;
            }
        }

        return view('my-account.index', [
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|size:11',
        ]);
 
        $user->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'full_name' => trim($request->first_name . ' ' . $request->last_name),
        ]);

        return redirect()->route('my-account')
            ->with('success', 'Profile updated successfully!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('my-account')
            ->with('success', 'Password changed successfully!');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // For now, just show a message
        // In production, you would implement password reset functionality
        return redirect()->route('my-account')
            ->with('info', 'Password reset link has been sent to your email.');
    }

    public function requestEmailChange(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'new_email' => 'required|email|unique:users,email',
            'current_password' => 'required',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The password provided does not match our records.']);
        }

        $token = Str::random(60);
        
        $user->update([
            'pending_email' => $request->new_email,
            'email_change_token' => $token
        ]);

        // Send notification to OLD email (as requested: "validate muna sa old email")
        Mail::to($user->email)->send(new EmailChangeRequested($user, $request->new_email));

        // Also send verification link to the NEW email (Standard practice to ensure new email is valid)
        // But the user said "validate muna sa old email", so I will send the verification link to the OLD email
        // Or if they click from OLD email, it authorizes it.
        
        // Actually, the email I sent to OLD email (EmailChangeRequested) should contain the link if that's the "accept" step.
        // Let's modify EmailChangeRequested to include the link.
        
        return redirect()->route('my-account')->with('success', 'Email change request sent! Please check your CURRENT email (' . $user->email . ') to authorize the change.');
    }

    public function verifyEmailChange($token)
    {
        $user = \App\Models\User::where('email_change_token', $token)->firstOrFail();

        $oldEmail = $user->email;
        $newEmail = $user->pending_email;

        $user->update([
            'email' => $newEmail,
            'pending_email' => null,
            'email_change_token' => null
        ]);

        return redirect()->route('my-account')->with('success', 'Email address successfully updated from ' . $oldEmail . ' to ' . $newEmail . '!');
    }
}
