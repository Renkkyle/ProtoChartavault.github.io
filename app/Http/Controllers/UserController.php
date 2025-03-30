<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Mail\VerificationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Mail\UserReRegistrationNotification;

class UserController extends BaseController
{
    // Show Dashboar
    public function showProfile()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('message', 'Redirecting to Admin Dashboard');
        } else {
            return redirect()->route('user.dashboard')->with('message', 'Redirecting to User Dashboard');
        }
    }

    // Update password
    public function updatePassword(Request $request)
    {


        // Validate the input fields
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:1|confirmed',
        ]);
    
        $user = Auth::user();

        // Check if the current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('status', 'Password changed successfully.');
    }

    public function messages()
    {
        $user = auth()->user(); // Fetch the authenticated user
        return view('UserMessages', compact('user')); // Pass the user variable to the view
    }

// Status Account

    public function showPending()
    {
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();


        // $pendingUsers = User::where('status', 'pending')->get();
            $pendingUsers = User::where('status', 'pending')
            ->where('is_verified', true) // Only fetch verified users
            ->paginate(15);
        return view('UserManagement', [
            'users' => $pendingUsers,
            'status' => 'pending',
            'user' => Auth::user(), // Pass the authenticated user
            'pendingCount' => $pendingCount,
            'pendingUserCount' => $pendingUserCount,
            'pendingTicketCount' => $pendingTicketCount,
            'pendingDownloadRequestCount' => $pendingDownloadRequestCount
        ]);
    }

    public function showApproved()
    {
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();


        $approvedUsers = User::where('status', 'approved')->paginate(15);
        return view('UserManagement', [
            'users' => $approvedUsers,
            'status' => 'approved',
            'user' => Auth::user(), // Pass the authenticated user
            'pendingCount' => $pendingCount,
            'pendingUserCount' => $pendingUserCount,
            'pendingTicketCount' => $pendingTicketCount,
            'pendingDownloadRequestCount' => $pendingDownloadRequestCount
        ]);
    }
    

    public function showRejected()
    {
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();


        $rejectedUsers = User::where('status', 'rejected')->paginate(10);
        return view('UserManagement', [
            'users' => $rejectedUsers,
            'status' => 'rejected',
            'user' => Auth::user(), // Pass the authenticated user
            'pendingCount' => $pendingCount,
            'pendingUserCount' => $pendingUserCount,
            'pendingTicketCount' => $pendingTicketCount,
            'pendingDownloadRequestCount' => $pendingDownloadRequestCount
        ]);
    }

    
    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'approved'; // Update the status
        $user->is_approved = 1; // Optionally update a separate column for approval
        $user->save();

        Mail::to($user->email)->send(new UserReRegistrationNotification($user));
        
        return redirect()->back()->with('success', 'User approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'account_remarks' => 'required|string|max:255',  // Validate the remarks field
        ]);
    
        // Find the user by ID
        $user = User::findOrFail($id);
        
        // Update the user's status and add the rejection remarks
        $user->status = 'rejected';
        $user->is_approved = 0;  // Optionally update a column for rejection
        $user->account_remarks = $request->account_remarks;  // Save the rejection remarks
        $user->save();
    
        // Optionally send an email (if needed)
        Mail::to($user->email)->send(new UserReRegistrationNotification($user));
    
        return redirect()->back()->with('success', 'User rejected successfully.');
    }

    public function edit($id)
    {
        
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $user = User::findOrFail($id);
        Log::info('User registration_reason:', ['registration_reason' => $user->registration_reason]);

        return view('admin.edit_user', compact('user'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,user',
            
        ]);

        $user = User::findOrFail($id);
        $user->update($validated);

        return redirect()->route('admin.userManagement')->with('status', 'User updated successfully.');
    }

    // Profile 

    public function showUserProfile()
    {
        $user = Auth::user();
        return view('userProfile', compact('user')); // Adjust the view name as necessary
    }

    public function updateUserProfile(Request $request)
    {
        $user = Auth::user();
        // Validate and update the user's profile data
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->save();

        return redirect()->route('userProfile')->with('message', 'Profile updated successfully!');
    }

    public function showAdminProfile()
    {
        $user = Auth::user();
        return view('adminProfile', compact('user')); // Adjust the view name as necessary
    }

    public function updateAdminProfile(Request $request)
    {
        $user = Auth::user();
        // Validate and update the admin's profile data
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->save();

        return redirect()->route('adminProfile')->with('message', 'Profile updated successfully!');
    }

        public function sendVerificationEmail(User $user)
    {
        // You might have a verification token stored on the User model
        $user->email_verification_token = Str::random(60); // or any other method to generate token
        $user->save();

        // Send the email
        Mail::to($user->email)->send(new VerificationEmail($user));
    }
        public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors('Invalid token.');
        }

        // Mark the user as verified
        $user->is_verified = true; // Or however your model handles this
        $user->email_verification_token = null; // Remove the verification token
        $user->save();

        return redirect()->route('login')->with('verify_success', 'Email verified successfully!, Please wait for the admin to approve your account');
    }

    public function destroy($id)
{
    $user = User::findOrFail($id);
    // Authorization
    
    if (auth()->user()->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    try {
        $user->delete();
        return response()->json(['message' => 'User removed successfully.'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to remove user. Please try again later.'], 500);
    }
}
}
