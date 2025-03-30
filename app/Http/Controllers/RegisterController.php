<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Mail\VerificationEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserReRegistrationNotification;
use App\Models\User; // Make sure to import the User model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Handle the registration of a new user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
{
    // Validate the incoming request
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255', // Remove the unique validation temporarily
        'password' => 'required|string|confirmed|min:8',
        'college' => 'required|string',
        'course' => 'required|string',
        'contact_number' => 'required_if:is_external,1|string|max:15',
        'registration_reason' => 'required_if:is_external,1|string|max:500',
        'is_external' => 'nullable|boolean', // Ensure is_external is handled as boolean
    ]);

    // Check if the user already exists with the provided email
    $existingUser = User::where('email', $validated['email'])->first();

    if ($existingUser) {
        if ($existingUser->status === 'rejected') {
            // Update the rejected user's details for re-registration
            $existingUser->first_name = $validated['first_name'];
            $existingUser->last_name = $validated['last_name'];
            $existingUser->password = Hash::make($validated['password']);
            $existingUser->college = $validated['college'];
            $existingUser->course = $validated['course'];
            $existingUser->role = 'user';  // Ensure the role remains 'user'
            $existingUser->is_external = $validated['is_external'] ?? false;
            $existingUser->contact_number = $validated['contact_number'] ?? null;
            $existingUser->registration_reason = $validated['registration_reason'] ?? null;
            $existingUser->status = 'pending';  // Set the status to 'pending' for re-approval
            $existingUser->account_remarks = null;  // Clear the previous rejection remarks
            $existingUser->save();  // Save the updated record

            // Optionally, send a re-registration confirmation email
            // Mail::to($existingUser->email)->send(new UserReRegistrationNotification($existingUser));

            return redirect()->route('login')->with('status', 'Re-registration successful. Please wait for admin approval.');
        }

        // If the user has any other status (approved or pending), block re-registration
        return redirect()->route('login')->with('status', 'Email is already registered.');
    }

    // If no existing user, create a new user record
    $user = User::create([
        'first_name' => $validated['first_name'],
        'last_name' => $validated['last_name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'college' => $validated['college'],
        'course' => $validated['course'],
        'role' => 'user',  // default role as 'user'
        'is_approved' => false, // explicitly set to false for admin approval
        'is_external' => $validated['is_external'] ?? false,
        'contact_number' => $validated['contact_number'] ?? null, // Only if external
        'registration_reason' => $validated['registration_reason'] ?? null, // Only if external
    ]);

    // Generate a random email verification token
    $user->email_verification_token = Str::random(60);
    $user->save();  // Save the token to the database

    // Send the email with the verification token
    Mail::to($user->email)->send(new VerificationEmail($user));

    return redirect()->route('login')->with('status', 'Registration successful. Please verify your email.');
}

}
